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
			$currencies['IRHR'] = __( 'Iranian hezar rial', 'woo-idpay-gateway' );
			$currencies['IRHT'] = __( 'Iranian hezar toman', 'woo-idpay-gateway' );

			return $currencies;
		}

		// Allows the gateway to define some currency symbols for the defined currency coeds.
		add_filter( 'woocommerce_currency_symbol', 'wc_idpay_currency_symbol', 10, 2 );

		function wc_idpay_currency_symbol( $currency_symbol, $currency ) {
			switch ( $currency ) {

				case 'IRHR':
					$currency_symbol = __( 'IRHR', 'woo-idpay-gateway' );
					break;

				case 'IRHT':
					$currency_symbol = __( 'IRHT', 'woo-idpay-gateway' );
					break;
			}

			return $currency_symbol;
		}

	}
}
