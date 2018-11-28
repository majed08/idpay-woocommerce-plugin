<?php
/**
 * Plugin Name: IDPay payment gateway for Woocommerce
 * Author: IDPay
 * Description: Secure <a href="https://idpay.ir">IDPay</a> payment gateway for Woocommerce.
 * Version: 1.0.3
 * Author URI: https://idpay.ir
 * Author Email: info@idpay.ir
 * Text Domain: idpay-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( 'includes/wc-geteway-idpay-helpers.php' );
require_once( 'includes/wc-gateway-idpay-init.php' );
