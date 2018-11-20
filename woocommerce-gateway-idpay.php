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

add_action( 'plugins_loaded', 'woocommerce_gateway_idpay_init' );

function woocommerce_gateway_idpay_init() {

	if ( class_exists( 'WC_Payment_Gateway' ) ) {
		require_once( 'includes/class-wc-gateway-idpay.php' );
		add_filter( 'woocommerce_payment_gateways', 'woocommerce_add_idpay_gateway' );

		function woocommerce_add_idpay_gateway( $methods ) {
			$methods[] = 'WC_IDPay';

			return $methods;
		}

		add_filter( 'woocommerce_currencies', 'idpay_IR_currency' );

		function idpay_IR_currency( $currencies ) {
			$currencies['IRR']  = __( 'ریال', 'woocommerce' );
			$currencies['IRT']  = __( 'تومان', 'woocommerce' );
			$currencies['IRHR'] = __( 'هزار ریال', 'woocommerce' );
			$currencies['IRHT'] = __( 'هزار تومان', 'woocommerce' );

			return $currencies;
		}

		add_filter( 'woocommerce_currency_symbol', 'idpay_IR_currency_symbol', 10, 2 );

		function idpay_IR_currency_symbol( $currency_symbol, $currency ) {
			switch ( $currency ) {
				case 'IRR':
					$currency_symbol = 'ریال';
					break;

				case 'IRT':
					$currency_symbol = 'تومان';
					break;

				case 'IRHR':
					$currency_symbol = 'هزار ریال';
					break;

				case 'IRHT':
					$currency_symbol = 'هزار تومان';
					break;
			}

			return $currency_symbol;
		}

	}
}

function idpay_wc_get_amount( $amount, $currency ) {
	switch ( strtolower( $currency ) ) {
		case strtolower( 'IRR' ):
		case strtolower( 'RIAL' ):
			return $amount;

		case strtolower( 'تومان ایران' ):
		case strtolower( 'تومان' ):
		case strtolower( 'IRT' ):
		case strtolower( 'Iranian_TOMAN' ):
		case strtolower( 'Iran_TOMAN' ):
		case strtolower( 'Iranian-TOMAN' ):
		case strtolower( 'Iran-TOMAN' ):
		case strtolower( 'TOMAN' ):
		case strtolower( 'Iran TOMAN' ):
		case strtolower( 'Iranian TOMAN' ):
			return $amount * 10;

		case strtolower( 'IRHR' ):
			return $amount * 1000;

		case strtolower( 'IRHT' ):
			return $amount * 10000;

		default:
			return 0;
	}
}
