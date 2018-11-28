<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add a function as a callback when hook 'plugins_loaded' is fired.
 *
 * Registers the 'wc_gateway_idpay_init' function to the
 * internal hook of Wordpress: 'plugins_loaded'.
 *
 * @see wc_gateway_idpay_init()
 */
add_action( 'plugins_loaded', 'wc_gateway_idpay_init' );

/**
 * Initialize the IDPAY gateway.
 *
 * When the internal hook 'plugins_loaded' is fired, this function would be
 * executed and after that, a Woocommerce hook (woocommerce_payment_gateways)
 * which defines a new gateway, would be triggered.
 *
 * Therefore whenever all plugins are loaded, the IDPAY gateway would be
 * initialized.
 *
 * Also another Woocommerce hooks would be fired in this process:
 *  - woocommerce_currencies
 *  - woocommerce_currency_symbol
 *
 * The two above hooks allows the gateway to define some currencies and their
 * related symbols.
 */
function wc_gateway_idpay_init() {

	if ( class_exists( 'WC_Payment_Gateway' ) ) {
		require_once( 'class-wc-gateway-idpay.php' );
		add_filter( 'woocommerce_payment_gateways', 'wc_add_idpay_gateway' );

		function wc_add_idpay_gateway( $methods ) {
			// Registers class WC_IDPAY as a payment method.
			$methods[] = 'WC_IDPay';

			return $methods;
		}

		// Allows the gateway to define some currencies.
		add_filter( 'woocommerce_currencies', 'wc_idpay_currencies' );

		function wc_idpay_currencies( $currencies ) {
			$currencies['IRR']  = __( 'ریال', 'woocommerce' );
			$currencies['IRT']  = __( 'تومان', 'woocommerce' );
			$currencies['IRHR'] = __( 'هزار ریال', 'woocommerce' );
			$currencies['IRHT'] = __( 'هزار تومان', 'woocommerce' );

			return $currencies;
		}

		// Allows the gateway to define some currency symbols for the defined currency coeds.
		add_filter( 'woocommerce_currency_symbol', 'wc_idpay_currency_symbol', 10, 2 );

		function wc_idpay_currency_symbol( $currency_symbol, $currency ) {
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
