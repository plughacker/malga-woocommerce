<?php
class Malga_Charges_Adapter {
    public $gateway, $payload;

	public function __construct($api, $order, $post) {
        $this->gateway = $api->gateway;   
        
        if(!isset($_SERVER['HTTP_USER_AGENT'])){
            $_SERVER['HTTP_USER_AGENT'] = 'null';
        }

		$this->payload = array(
			"merchantId" => $this->gateway->get_merchantId(),
			"amount" => $api->money_format( $order->get_total() ),
			"statementDescriptor" => $this->gateway->statement_descriptor,
			"capture" => true,
			"orderId" => $order->get_order_number(),
			"paymentMethod" => ["paymentType"=> sanitize_text_field($post['paymentType'])],
            "appInfo" => [
                "platform" => [
                   "integrator" => "malga",
                   "name" => "woocommerce",
                   "version" => "1.0"
                ],
                "device" => [
                   "name" => "browser",
                   "version" => $_SERVER['HTTP_USER_AGENT']
                ],
                "system" => [
                   "name" => "woocommerce",
                   "version" => "1.0"
                ]
            ]
		);    
    }

    private function get_document( $post ) {
        if (isset($post['billing_persontype']) && !empty($post['billing_persontype'])){
            $document_type = ($post['billing_persontype'] == '1')? 'cpf' : 'cnpj';
            $document_number = ($post['billing_persontype'] == '1')? $post['billing_cpf'] : $post['billing_cnpj'];
        } else if (isset($post['billing_cpf']) && !empty($post['billing_cpf'])){
            $document_type = 'cpf';
            $document_number = $post['billing_cpf'];
        } else if (isset($post['billing_cnpj']) && !empty($post['billing_cnpj'])){
            $document_type = 'cnpj';
            $document_number = $post['billing_cnpj'];
        }

        $document_number = str_replace(array('.',',','-','/'), '', $document_number);

        return array($document_type, sanitize_text_field($document_number));
    }

    public function set_fraudanalysis( $post, $order ) {
        list($document_type, $document_number) = $this->get_document($post);

        $district = sanitize_text_field($post['billing_neighborhood']);
        if(empty($district)){$district = sanitize_text_field($post['billing_address_2']);};
        if(empty($district)){$district = sanitize_text_field($post['billing_address_1']);};

        if(empty($document_type))
            $document_type = 'NoDocument'; 
        else
            $document_type = strtoupper($document_type); 

        if($this->payload['paymentSource']['sourceType'] == "card"){
            $this->payload['fraudAnalysis'] = [
                "customer"=> [
                    "name"=> sanitize_text_field($post['billing_first_name'] . ' ' . $post['billing_last_name']),
                    "identity"=> $document_number,
                    "identityType"=> $document_type,
                    "email"=> sanitize_email($post['billing_email']),
                    "phone"=> sanitize_text_field($post['billing_phone']),
                    "billingAddress"=> [
                        'street' => sanitize_text_field($post['billing_address_1']),
                        'number' => sanitize_text_field($post['billing_number']),
                        'zipCode' => sanitize_text_field($post['billing_postcode']),
                        'city' => sanitize_text_field($post['billing_city']),
                        'state' => sanitize_text_field($post['billing_state']),
                        'country' => sanitize_text_field($post['billing_country']),
                        "complement"=> sanitize_text_field($post['billing_address_2']), 
                        'district' => $district,
                    ]
                ],
                "cart" => [
                    "items" => []
                ]     
            ];

            foreach ( $order->get_items() as $item_id => $item ) { 
                $this->payload['fraudAnalysis']['cart']['items'][] = [
                    'name' => sanitize_text_field($item->get_name()),
                    'quantity' => $item->get_quantity(),
                    'sku' => sanitize_text_field($item->get_id()),
                    'unitPrice' => intval($item->get_total()),
                    'risk' => "Low"
                ];                
            }
        }
    }

    public function set_payment_flow( $metadata ) {
        $this->payload['paymentFlow']['metadata'] = $metadata;
    }

    public function to_credit( $post ) {
        if(!isset($post['malgapayments_card_installments'])) $post['malgapayments_card_installments'] = "1";

		$post['malgapayments_card_expiry'] = str_replace(array(' '), '', sanitize_text_field($post['malgapayments_card_expiry']));		
		$post['malgapayments_card_number'] = str_replace(array(' '), '', sanitize_text_field($post['malgapayments_card_number']));  

        $this->payload['paymentSource'] = array(
            "sourceType" => "card",
            "card"=> array(
                "cardNumber"=> sanitize_text_field($post['malgapayments_card_number']),
                "cardCvv"=> sanitize_text_field($post['malgapayments_card_cvv']),
                "cardExpirationDate"=> sanitize_text_field($post['malgapayments_card_expiry']),
                "cardHolderName"=> sanitize_text_field($post['malgapayments_card_holder_name'])
            )
        );

        $this->payload['paymentMethod']['installments'] = intval($post['malgapayments_card_installments']);
        $this->payload["currency"] = $this->gateway->currency;
    }

    public function to_pix( $post ) {
        list($document_type, $document_number) = $this->get_document($post);

        $this->payload['paymentSource'] = array(
            "sourceType" => "customer",
            "customer"=> array(
                "name"=> sanitize_text_field($post['billing_first_name'] . ' ' . $post['billing_last_name']),
                "phoneNumber"=> sanitize_text_field($post['billing_phone']),
                "email"=> sanitize_email($post['billing_email']),
				"address"=> array(
					"street"=> sanitize_text_field($post['billing_address_1']), 
					"streetNumber"=> sanitize_text_field($post['billing_number']), 
					"zipCode"=> sanitize_text_field($post['billing_postcode']), 
					"country"=> sanitize_text_field($post['billing_country']), 
					"state"=> sanitize_text_field($post['billing_state']), 
					"district"=> sanitize_text_field($post['billing_neighborhood']), 
					"city"=> sanitize_text_field($post['billing_city'])
				),				
                "document"=> array(
                    "number"=> $document_number,
                    "type"=> $document_type
                )
            )
        );

        $this->payload['paymentMethod']['expiresIn'] = 3600;
    }      


    public function to_boleto( $post ) {
        $boleto_expires = sanitize_text_field($this->gateway->get_option( 'boleto_expires', 5 ));
        $boleto_instructions = $this->gateway->get_option( 'boleto_instructions', 'Instruções para pagamento do boleto' );
        $boleto_instructions = sanitize_text_field($boleto_instructions);
        $interest_days = intval(sanitize_text_field($this->gateway->get_option( 'interest_days', 5 )));
        $interest_value = intval(sanitize_text_field($this->gateway->get_option( 'interest_value', 5 )));
        $fine_value = intval(sanitize_text_field($this->gateway->get_option( 'fine_value', 5 )));
        $fine_days = intval(sanitize_text_field($this->gateway->get_option( 'fine_days', 5 )));

        list($document_type, $document_number) = $this->get_document($post);

        $this->payload['paymentSource'] = array(
            "sourceType" => "customer",
            "customer"=> array(
                "name"=> sanitize_text_field($post['billing_first_name'] . ' ' . $post['billing_last_name']),
                "phoneNumber"=> sanitize_text_field($post['billing_phone']),
                "email"=> sanitize_email($post['billing_email']),
				"address"=> array(
					"street"=> sanitize_text_field($post['billing_address_1']), 
					"streetNumber"=> sanitize_text_field($post['billing_number']), 
					"zipCode"=> sanitize_text_field($post['billing_postcode']), 
					"country"=> sanitize_text_field($post['billing_country']), 
					"state"=> sanitize_text_field($post['billing_state']), 
					"district"=> sanitize_text_field($post['billing_neighborhood']), 
					"city"=> sanitize_text_field($post['billing_city'])
				),
                "document"=> array(
                    "number"=> $document_number,
                    "type"=> $document_type
                )
            )
        );

        $this->payload['paymentMethod']['expiresDate'] = date('Y-m-d', strtotime("+$boleto_expires days"));
        $this->payload['paymentMethod']['instructions'] = $boleto_instructions;
        $this->payload['paymentMethod']['interest'] = array(
            "days"=> $interest_days,
            $this->gateway->get_option( 'interest_type', 'amount' )=> $interest_value
        );
        $this->payload['paymentMethod']['fine'] = array(
            "days"=> $fine_days,
            $this->gateway->get_option( 'fine_type', 'amount' )=> $fine_value
        );
    }      
    
    public function hide_sensitive(){
        $sanitized = $this->payload;

        if($sanitized['paymentMethod']['paymentType'] == 'credit'){
            $sanitized['paymentSource']['paymentType'] = array(
                "sourceType" => "card"
            );
        }

        return $sanitized;
    }
}
