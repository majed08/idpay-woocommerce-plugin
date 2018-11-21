<?php
/**
 * Plugin Name: WooCommerce IDPay Gateway
 * Author: IDPay
 * Description: درگاه پرداخت امن <a href="https://idpay.ir">آیدی پی</a> برای فروشگاه ساز ووکامرس
 * Version: 1.3
 * Author URI: https://idpay.ir
 * Author Email: info@idpay.ir
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( 'includes/wc-geteway-idpay-helpers.php' );
require_once( 'includes/wc-gateway-idpay-init.php' );
