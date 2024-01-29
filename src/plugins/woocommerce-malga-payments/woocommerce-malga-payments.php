<?php
/**
 * Plugin Name: Malga
 * Plugin URI: https://www.malga.io/wocommerce
 * Description: Take credit card payments on your store using Malga.
 * Author: Malga Team
 * Author URI: https://www.malga.io/
 * Version: 1.0.0
 * Requires at least: 5.6
 * Tested up to: 6.4
 * WC requires at least: 3.3
 * WC tested up to: 8.5.1
 * Text Domain: malga-payments-gateway
 * Domain Path: /languages/
 */

defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'MALGAPAYMENTS_VERSION', '1.0.0' );
define( 'MALGAPAYMENTS_PLUGIN_FILE', __FILE__ );
require_once dirname( __FILE__ ) . '/includes/constants/payments-types.php';

class Malga_Payments {
	public static function init() {				
		// Checks with WooCommerce is installed.
		if ( class_exists( 'WC_Payment_Gateway' ) ) {
			require_once dirname( __FILE__ ) . '/sdk/malga-payments.php';
			require_once dirname( __FILE__ ) . '/includes/class-malga-api.php';
			require_once dirname( __FILE__ ) . '/includes/class-malga-gateway.php';
			require_once dirname( __FILE__ ) . '/includes/class-malga-charges-adapter.php';

			add_filter( 'woocommerce_payment_gateways', array( __CLASS__, 'add_gateway' ) );
		} else {
			add_action( 'admin_notices', array( __CLASS__, 'woocommerce_missing_notice' ) );
		}	
	}

	public static function woocommerce_missing_notice() {
		include dirname( __FILE__ ) . '/templates/notice/missing-woocommerce.php';
	}

	public static function add_gateway( $methods ) {
		$methods[] = 'Malga_Gateway';

		return $methods;
	}	
	
	public static function get_templates_path() {
		return plugin_dir_path( MALGAPAYMENTS_PLUGIN_FILE ) . 'templates/';
	}	

	public static function load_plugin_textdomain() {
		load_plugin_textdomain( 'malga-payments-gateway', false, basename( dirname( __FILE__ ) ) . '/languages/' );
	}	
}   

add_action( 'plugins_loaded', array( 'Malga_Payments', 'init' ) );
add_action( 'plugins_loaded', array( 'Malga_Payments', 'load_plugin_textdomain' ) );