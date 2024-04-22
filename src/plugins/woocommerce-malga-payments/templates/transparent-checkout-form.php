<?php
defined( 'ABSPATH' ) || exit;
?>

<fieldset id="malgapayments-payment-form">
	<style>.malgapayments-method-form{display: none;}</style>

	<?php foreach(WC_MALGAPAYMENTS_PAYMENTS_TYPES as $key => $label){ ?>
		<?php if (in_array($key, $allowedTypes)){ ?>
			<label>
				<input type="radio" name="paymentType" value="<?php echo esc_attr($key); ?>">
				<?php 
					$allowedHTML = ['span' => ['style' => []]];

					wp_kses(sprintf(esc_html_e( '%s', 'malga-payments-gateway' ), $titleOfTypes[$key]), $allowedHTML);
				?>
			</label>
			<?php
			wc_get_template(
				"payment-types/$key.php", array(
					'currency' 			   => $currency,
					'cart_total'           => $cart_total,
					'minimum_installment'  => $minimum_installment,
					'maximum_installment'  => $maximum_installment,
				), 'woocommerce/malgapayments/', Malga_Payments::get_templates_path()
			);		
			?>
		<?php } ?>
	<?php } ?>	
</fieldset>
