<?php
/**
 * Plugin Name: IDPay payment gateway for Woocommerce
 * Author: IDPay
 * Description: <a href="https://idpay.ir">IDPay</a> secure payment gateway for Woocommerce.
 * Version: 2.2.4
 * Author URI: https://idpay.ir
 * Author Email: info@idpay.ir
 * Text Domain: woo-idpay-gateway
 * Domain Path: /languages/
 *
 * WC requires at least: 3.0
 * WC tested up to: 7.2
 */

if (! defined('ABSPATH')) {
    exit;
}

function woo_idpay_gateway_load()
{
    $realPath = basename(dirname(__FILE__)) . '/languages';
    load_plugin_textdomain('woo-idpay-gateway', false, $realPath);
}

add_action('init', 'woo_idpay_gateway_load');
require_once(plugin_dir_path(__FILE__) . 'includes/wc-gateway-idpay-init.php');
