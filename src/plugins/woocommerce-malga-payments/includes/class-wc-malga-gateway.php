<?php
defined( 'ABSPATH' ) || exit;

class WC_Malga_Gateway extends WC_Payment_Gateway {
	public function __construct() {
		$this->id                 = 'malgapayments';
		$this->icon               = apply_filters( 'woocommerce_malgapayments_icon', plugins_url( 'assets/images/poweredbymalga.png', plugin_dir_path( __FILE__ ) ) );
		$this->method_title       = __( 'Malga', 'malga-payments-gateway' );
		$this->method_description = __( 'Accept payments by credit card, bank debit or banking ticket using the Malga Payments.', 'malga-payments-gateway' );
		$this->order_button_text  = __( 'Pay', 'malga-payments-gateway' );

		$this->init_form_fields();

		$this->init_settings();   

		$this->title               = $this->get_option( 'title' );
		$this->description         = $this->get_option( 'description' );
		$this->clientId            = $this->get_option( 'clientId' );
		$this->tokenId             = $this->get_option( 'tokenId' );
		$this->merchantId          = $this->get_option( 'merchantId' );
		$this->sandbox_merchantId  = $this->get_option( 'sandbox_merchantId' );
		$this->statement_descriptor= $this->get_option( 'statement_descriptor', 'WC-' );
		$this->webhook_secret	   = $this->get_option( 'webhook_secret', 'uuid' );
		$this->currency	   		   = $this->get_option( 'currency', 'BRL' );
		$this->sandbox             = $this->get_option( 'sandbox', 'no' );    
		$this->debuger             = $this->get_option( 'debuger', 'no' ); 
		$this->fraudanalysis       = $this->get_option( 'fraudanalysis', 'no' ); 
		$this->minimum_installment = $this->get_option( 'minimum_installment', '5' );  
		$this->maximum_installment = $this->get_option( 'maximum_installment', '10' );  
		$this->allowedTypes        = $this->get_allowedTypes();
		$this->titleOfTypes        = [];
		$this->feeOfTypes        = [];
		foreach(WC_MALGAPAYMENTS_PAYMENTS_TYPES as $key => $label){
			$this->titleOfTypes[$key] = $this->get_option( "title_$key", $label ); 
			$this->feeOfTypes[$key] = $this->get_option( "fee_$key", '0' ); 
		}

		$this->sdk = new Malga_Payments_SDK( $this->clientId, $this->tokenId, ( 'yes' == $this->sandbox ) );	
		$this->api = new WC_MalgaPayments_API( $this );

		add_action( 'wp_enqueue_scripts', array( $this, 'checkout_scripts' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
		add_action( 'woocommerce_api_wc_malgapayments_gateway', array( $this, 'ipn_handler' ) );
    }
    
	public function init_form_fields() {
		$this->form_fields = array(
			'title' => array(
				'title'       => __( 'Title', 'malga-payments-gateway' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'malga-payments-gateway' ),
				'desc_tip'    => true,
				'default'     => __( 'MalgaPayments', 'malga-payments-gateway' ),
			),
			'description' => array(
				'title'       => __( 'Description', 'malga-payments-gateway' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'malga-payments-gateway' ),
				'default'     => __( 'Pay via Malga', 'malga-payments-gateway' ),
			),
			'minimum_installment' => array(
				'title'       => __( 'Minimum value of the installment', 'malga-payments-gateway' ),
				'type'        => 'number',
				'description' => __( 'Please enter your minimum value of the installment', 'malga-payments-gateway' ),
				'default'     => '5',
			),	
			'maximum_installment' => array(
				'title'       => __( 'Maximum number of installments', 'malga-payments-gateway' ),
				'type'        => 'number',
				'description' => __( 'Enter the maximum number of installments', 'malga-payments-gateway' ),
				'default'     => '10',
			),					
			'debuger' => array(
				'title'       => __( 'Debuger', 'malga-payments-gateway' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable debuger', 'malga-payments-gateway' ),
				'desc_tip'    => true,
				'default'     => 'no',
				'description' => __( 'Debuger can be used to test the payments.', 'malga-payments-gateway' ),
			),	
			'fraudanalysis' => array(
				'title'       => __( 'FraudAnalysis', 'malga-payments-gateway' ),
				'type'        => 'checkbox',
				'label'       => __( 'Use Fraudanalysis', 'malga-payments-gateway' ),
				'desc_tip'    => true,
				'default'     => 'no',
				'description' => __( 'fraudAnalysis can be used otimize payments.', 'malga-payments-gateway' ),
			),						
			'integration' => array(
				'title'       => __( 'Integration', 'malga-payments-gateway' ),
				'type'        => 'title',
				'description' => '',
			),
			'clientId' => array(
				'title'       => __( 'X-Client-Id', 'malga-payments-gateway' ),
				'type'        => 'text',
				'description' => __( 'Please enter your X-Client-Id. This is needed in order to take payment.', 'malga-payments-gateway' ),
				'desc_tip'    => true,
				'default'     => '',
            ),
			'tokenId' => array(
				'title'       => __( 'X-Api-Key', 'malga-payments-gateway' ),
				'type'        => 'text',
				'description' => __( 'Please enter your X-Api-Key. This is needed in order to take payment.', 'malga-payments-gateway' ),
				'desc_tip'    => true,
				'default'     => '',
            ),			
			'merchantId' => array(
				'title'       => __( 'MerchantId', 'malga-payments-gateway' ),
				'type'        => 'text',
				'description' => __( 'Please enter your MerchantId. This is needed in order to take payment.', 'malga-payments-gateway' ),
				'desc_tip'    => true,
				'default'     => '',
            ),						
			'sandbox' => array(
				'title'       => __( 'Sandbox', 'malga-payments-gateway' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable Malga Sandbox', 'malga-payments-gateway' ),
				'desc_tip'    => true,
				'default'     => 'no',
				'description' => __( 'Malga Sandbox can be used to test the payments.', 'malga-payments-gateway' ),
			),
			'merchantId' => array(
				'title'       => __( 'MerchantId', 'malga-payments-gateway' ),
				'type'        => 'text',
				'description' => __( 'Please enter your Malga email address. This is needed in order to take payment.', 'malga-payments-gateway' ),
				'desc_tip'    => true,
				'default'     => '',
            ),
			'sandbox_merchantId' => array(
				'title'       => __( 'Sandbox MerchantId', 'malga-payments-gateway' ),
				'type'        => 'text',
				'description' => __( 'Please enter your Malga sandbox merchantId', 'malga-payments-gateway' ),
				'default'     => '',
			),
			'behavior' => array(
				'title'       => __( 'Integration Behavior', 'malga-payments-gateway' ),
				'type'        => 'title',
				'description' => '',
			),
			'statement_descriptor' => array(
				'title'       => __( 'Statement descriptor', 'malga-payments-gateway' ),
				'type'        => 'text',
				'description' => __( 'Please enter a statement descriptor.', 'malga-payments-gateway' ),
				'default'     => 'WC-',
			),
			'webhook_secret' => array(
				'title'       => __( 'Webhook Secret', 'malga-payments-gateway' ),
				'type'        => 'text',
				'description' => sprintf(__( 'Please enter a Webhook Secret, use: %s', 'malga-payments-gateway' ), WC()->api_request_url( 'WC_MalgaPayments_Gateway' ) . '?secret=' . $this->get_option( 'webhook_secret', 'uuid' )),
				'default'     => 'uuid',
			),		
			'currency' => array(
				'title'       => __( 'Currency', 'malga-payments-gateway' ),
				'type'        => 'text',
				'description' => sprintf(__( 'Currency identifier for billing processing, ISO 4217 format', 'malga-payments-gateway' )),
				'default'     => 'BRL',
			),	
			'transparent_checkout' => array(
				'title'       => __( 'Transparent Checkout Options', 'malga-payments-gateway' ),
				'type'        => 'title',
				'description' => '',
			),			
		);
		
		foreach(WC_MALGAPAYMENTS_PAYMENTS_TYPES as $key => $label){
			$this->form_fields["allow_$key"] = array(
				'title'   => __( $label, 'malga-payments-gateway' ),
				'type'    => 'checkbox',
				'label'   => __( "Enable $label", 'malga-payments-gateway' ),
				'default' => 'yes',
			);
			$this->form_fields["title_$key"] = array(
				'type'    => 'text',
				'default' => __( $label, 'malga-payments-gateway' ),
				'description' => __( 'Please enter your title of payment type', 'malga-payments-gateway' ),
				'desc_tip'    => false,				
			);			
			$this->form_fields["fee_$key"] = array(
				'type'    => 'number',
				'default' => 0,
				'description' => __( 'Percent of discount', 'malga-payments-gateway' ),
				'desc_tip'    => false,				
			);			
		}	

		foreach(WC_MALGAPAYMENTS_PAYMENTS_TYPES as $key => $label){
			if(file_exists(dirname( __FILE__ ) . "/configs/$key.php")){	
				$this->form_fields[$key] = array(
					'title'       => __( "$label Options", 'malga-payments-gateway' ),
					'type'        => 'title',
					'description' => '',
				);						
				include dirname( __FILE__ ) . "/configs/$key.php";
			}
		}
	}    

	public function ipn_validate($data) {		
		if(!$data || $_GET['secret'] != $this->webhook_secret){
			wp_die( esc_html__( 'Malga Request Unauthorized', 'malga-payments-gateway' ), esc_html__( 'Malga Request Unauthorized', 'malga-payments-gateway' ), array( 'response' => 401 ) );
		}else{
			header( 'HTTP/1.1 200 OK' );
		}
	}

	public function ipn_handler() {	
		$data = json_decode(file_get_contents('php://input'), true);
		$this->ipn_validate($data);

		if($data['object'] == 'transaction'){
			$payment = $data['data'];
			$order = wc_get_order( $data['data']['orderId'] );
			if($order && $payment){
				$this->update_order_status( $order, $payment );
				$this->save_payment_meta_data( $order, $payment );					
			}
		}
		exit;
	}

	public function checkout_scripts() {
		if ( is_checkout() && $this->is_available() ) {
			if ( ! get_query_var( 'order-received' ) ) {
				wp_enqueue_style( 'malgapayments-checkout', plugins_url( 'assets/css/transparent-checkout.css', plugin_dir_path( __FILE__ ) ), array(), WC_MALGAPAYMENTS_VERSION );
				wp_enqueue_script( 'malgapayments-checkout', plugins_url( 'assets/js/transparent-checkout.js', plugin_dir_path( __FILE__ ) ), array( 'jquery' ), WC_MALGAPAYMENTS_VERSION, true );
			}
		}
	}

	public function admin_options() {
		include WC_Malga_Payments::get_templates_path() . 'admin-page.php';
	}

	public function is_available() {
		$available = 	('yes' === $this->get_option( 'enabled' ) && 
					 	'' !== $this->get_clientId() && 
						'' !== $this->get_tokenId() && 
						(NULL !== $this->get_merchantId() || empty($this->get_merchantId())));

		if (in_array(WC_MALGAPAYMENTS_BR_TYPES, $this->allowedTypes) && ! class_exists( 'Extra_Checkout_Fields_For_Brazil' ) ) {
			$available = false;
		}

		return $available;
	}	

	public function payment_fields() {
		wp_enqueue_script( 'wc-credit-card-form' );

		$description = $this->get_description();
		if ( $description ) {
			echo wp_kses_post( $description );
		}

		$cart_total = $this->get_order_total();

		wc_get_template(
			'transparent-checkout-form.php', array(
				'cart_total'         => $cart_total,
				'minimum_installment'=> $this->minimum_installment,
				'maximum_installment'=> $this->maximum_installment,
				'allowedTypes'       => $this->allowedTypes,
				'titleOfTypes'       => $this->titleOfTypes,
			), 'woocommerce/malgapayments/', WC_MALGA_Payments::get_templates_path()
		);
	}	

	public function get_allowedTypes($onlyBR = false) {
		$allowedTypes = array();
		foreach(WC_MALGAPAYMENTS_PAYMENTS_TYPES as $key => $label){
			if($this->get_option( "allow_$key", 'yes' ) == 'yes'){
				if($onlyBR){
					if(in_array($key, array_keys(WC_MALGAPAYMENTS_BR_TYPES))){						
						$allowedTypes[] = $key;
					}
				}else{
					$allowedTypes[] = $key;
				}
			}
		}
		return $allowedTypes;
	}
	
	public function get_clientId() {
		return $this->clientId;
	}

	public function get_tokenId() {
		return $this->tokenId;
	}	

	public function get_merchantId() {
		return 'yes' === $this->sandbox ? $this->sandbox_merchantId : $this->merchantId;
	}		
	
	public function update_order_status( $order, $payment ) {
		switch ( $payment['status'] ) {
			case 'authorized':
				$order->update_status( 'processing', __( 'Malga: Payment approved.', 'malga-payments-gateway' ) );
				$order->add_order_note( __( 'Malga: Payment approved.', 'malga-payments-gateway' ) );
				wc_reduce_stock_levels( $order->get_order_number() );				
				break;
			case 'pre_authorized':
				$order->update_status( 'on-hold', __( 'Malga: Payment is pre-authorized', 'malga-payments-gateway' ) );				
				break;
			case 'pending':				
				$order->update_status( 'pending"', __( 'Malga: Payment is pending', 'malga-payments-gateway' ) );	
				break;
			case 'failed':				
				$order->update_status( 'failed', __( 'Malga: Payment is failed', 'malga-payments-gateway' ) );	
				break;
			case 'canceled':				
				$order->update_status( 'failed', __( 'Malga: Payment is canceled', 'malga-payments-gateway' ) );	
				break;
			case 'voided':
				$order->update_status( 'refunded', __( 'Malga: Payment refunded', 'malga-payments-gateway' ) );
				wc_increase_stock_levels( $order->get_order_number() );
				break;	
			case 'charged_back':
				$order->update_status( 'refunded', __( 'Malga: Payment came into dispute.', 'malga-payments-gateway' ) );							
				break;																			
			default:
				break;				
		}
	}

	protected function save_payment_meta_data( $order, $payment ) {
		$meta_data    = array(
			'paymentStatus' => $payment['status']
		);

		if(isset($payment['paymentMethod'])){
			$meta_data['paymentType'] = $payment['paymentMethod']['paymentType'];
			$meta_data['paymentData'] = $payment;
		}

		$meta_data['_malga_data_' . time()] = $payment;		

		foreach ( $meta_data as $key => $value ) {
			$order->update_meta_data( $key, $value );
		}

		$order->save();		
	}

	public function set_fees($order) {
		$fees = $order->get_fees();
		if(!empty($fees)){
			foreach($this->feeOfTypes as $key => $value){
				foreach ($fees as $key => $fee) {
					if($fees[$key]->name === __( $key . " discount" )) {
						unset($fees[$key]);
					}
				}
			}
			$order->set_fees($fees);
		}

		$fee = $this->feeOfTypes[$_POST['paymentType']];
		if( is_numeric($fee) && $fee != '0' ){
			$subtotal = $order->get_subtotal();
			$percentage = intval($fee);
			$percentage = $percentage > 100 ? -100 : -$percentage;
			$discount   = $percentage * $subtotal / 100;			

			$fee = new WC_Order_Item_Fee();
			$fee->set_name( $_POST['paymentType'] . " discount" );
			$fee->set_amount($discount);
			$fee->set_total($discount);			
			$fee->set_tax_class('');
			$fee->set_tax_status('none');

			$order->add_item($fee);
			$order->calculate_totals();
			$order->save();		
		}			
	}

	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );	
		$this->set_fees( $order ); 

		$response = $this->api->payment_request( $order, $_POST );	
		if ( !empty($response['data']) ) {
			$this->update_order_status( $order, $response['data'] );
			$this->save_payment_meta_data( $order, $response['data'] );
			
			if( $response['data']['status']  == 'authorized' || $response['data']['status']  == 'pending') {
				WC()->cart->empty_cart();

				if($response['data']['status']  == 'authorized'){ 
					$redirect = $order->get_checkout_order_received_url();
				} else { 
					$redirect = $order->get_checkout_payment_url( true );
				}

				return array(
					'result'   => 'success',
					'redirect' => $redirect
				);	
			}else{	
				foreach ( $response['error'] as $error ) {
					wc_add_notice( __($error, 'malga-payments-gateway' ), 'error' );
				}

				return array(
					'result'   => 'fail',
					'redirect' => ''
				);						
			}				
		}else{
			if(!isset($response['error']) || empty($response['error'])){
				$errors = array( 
					__( 'Internal error :(', 'malga-payments-gateway' ) 
				);
			}
			
			foreach ( $response['error'] as $error ) {
				wc_add_notice( __($error, 'malga-payments-gateway' ), 'error' );
			}
	
			return array(
				'result'   => 'fail',
				'redirect' => '',
			);
		}
	}

	public function receipt_page( $order_id ) {
		$order = wc_get_order( $order_id );

		$paymentType   = $order->get_meta( 'paymentType' );
		$paymentStatus = $order->get_meta( 'paymentStatus' );
		$paymentData   = $order->get_meta( 'paymentData' );

		wc_get_template(
			"receipt/$paymentType.php", array(
				'payment_data'         => $paymentData,
			), 'woocommerce/malgapayments/', WC_Malga_Payments::get_templates_path()
		);
	}
}