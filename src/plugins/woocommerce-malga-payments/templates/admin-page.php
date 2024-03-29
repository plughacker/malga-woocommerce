<h3><?php echo esc_html( $this->method_title ); ?></h3>

<?php
    if ( '' === $this->get_clientId() ) {
        include WC_Malga_Payments::get_templates_path() . 'notice/missing-clientId.php';
    }

    if ( '' === $this->get_tokenId() ) {
        include WC_Malga_Payments::get_templates_path() . 'notice/missing-token.php';
    }

    if ( NULL === $this->get_merchantId() || empty($this->get_merchantId()) ) {      
        include WC_Malga_Payments::get_templates_path() . 'notice/missing-merchant.php';
    }

    if ( !empty($this->get_allowedTypes(true)) && ! class_exists( 'Extra_Checkout_Fields_For_Brazil' ) ) {
        include WC_Malga_Payments::get_templates_path() . 'notice/missing-ecfb.php';
    }    
?>

<?php echo wp_kses_post( $this->method_description ); ?>

<table class="form-table">
	<?php $this->generate_settings_html(); ?>
</table>

<!-- 
    plugin_locale: <?php echo apply_filters( 'plugin_locale', determine_locale(), 'malga-payments-gateway' ); ?>
-->