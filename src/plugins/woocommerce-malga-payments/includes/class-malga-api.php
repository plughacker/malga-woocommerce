<?php
defined( 'ABSPATH' ) || exit;

class Malga_API {
	public $gateway;

	public function __construct( $gateway = null ) {
		$this->gateway = $gateway;		
	}  

	public function money_format( $value ) {
		return intval(str_replace(array(' ', ',', '.'), '', $value));
	}

    public function payment_request( $order ) {
		$payment_method = isset( $_POST['paymentType'] ) ? $_POST['paymentType'] : '';

		if ( ! in_array( $payment_method, $this->gateway->allowedTypes ) ) {
			return array(
				'url'   => '',
				'data'  => '',
				'error' => array( '<strong>' . __( 'Malga', 'malga-payments-gateway' ) . '</strong>: ' .  __( 'Please, select a payment method.', 'malga-payments-gateway' ) ),
			);
		}	

		$adapter = new Malga_Charges_Adapter( $this, $order);

		call_user_func_array(array($adapter, 'to_' . $payment_method), array());

		if( 'yes' == $this->gateway->fraudanalysis ){
			$adapter->set_fraudanalysis($order);
		}

        $payment_flow = apply_filters( 'malga_payment_flow', false, $order );
		if( $payment_flow ){
			$adapter->set_payment_flow($payment_flow);
		}

		$adapter->payload = apply_filters('malga_payload', $adapter->payload);

		$return = $this->gateway->sdk->post_charge($adapter->payload);

		if( 'yes' == $this->gateway->debuger ){
			$request = json_encode($adapter->hide_sensitive($adapter->payload), JSON_UNESCAPED_SLASHES);
			
			$order->add_order_note( 'Request: '.$request, 'malga-payments-gateway' );
			$order->add_order_note( 'Return: '.json_encode($return), 'malga-payments-gateway' );
		}

		if($return['status'] == 'failed'){
			$errors = array();
			if(isset($return['transactionRequests'][0]['providerError'])){
				$error = $return['transactionRequests'][0]['providerError']['networkDeniedMessage'];
				$errors[] = sprintf(esc_html__('%s', 'malga-payments-gateway' ), esc_html($error));
			}

			return array(
				'url'   => '',
				'data'  => '',
				'error' => $errors,
			);
		}

		if (isset($return['error'])) {
			$errors = array();
			if(isset($return['error']['message'])){				
				$errors[] = sprintf(esc_html__('%s', 'malga-payments-gateway' ), esc_html($return['error']['message']));
			}

			if(isset($return['error']['details'])){
				foreach($return['error']['details'] as $error){
					$errors[] = sprintf(esc_html__('%s', 'malga-payments-gateway' ), esc_html($error));
				}
			}

			return array(
				'url'   => '',
				'data'  => '',
				'error' => $errors,
			);			
		}

		return array(
			'url'   => '',
			'data'  => $return
		);		
    }
}
