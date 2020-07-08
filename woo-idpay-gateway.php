<?php
/**
 * Plugin Name: IDPay payment gateway for Woocommerce
 * Author: IDPay
 * Description: <a href="https://idpay.ir">IDPay</a> secure payment gateway for Woocommerce.
 * Version: 2.1.0
 * Author URI: https://idpay.ir
 * Author Email: info@idpay.ir
 * Text Domain: woo-idpay-gateway
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function woo_idpay_gateway_load_textdomain() {
	load_plugin_textdomain( 'woo-idpay-gateway', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

add_action( 'init', 'woo_idpay_gateway_load_textdomain' );

require_once( plugin_dir_path( __FILE__ ) . 'includes/wc-gateway-idpay-helpers.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/wc-gateway-idpay-init.php' );
