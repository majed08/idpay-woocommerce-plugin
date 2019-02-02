<?php

/**
 * WC_IDPay Class.
 */
class WC_IDPay extends WC_Payment_Gateway {

	/**
	 * The API Key
	 *
	 * @var string
	 */
	protected $api_key;

	/**
	 * The sandbox mode.
	 *
	 * Indicates weather the gateway is in the test or the live mode.
	 *
	 * @var string
	 */
	protected $sandbox;

	/**
	 * The payment success message.
	 *
	 * @var string
	 */
	protected $success_massage;

	/**
	 * The payment failure message.
	 *
	 * @var string
	 */
	protected $failed_massage;


	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                 = 'WC_IDPay';
		$this->method_title       = __( 'IDPay', 'woo-idpay-gateway' );
		$this->method_description = __( 'Redirects customers to IDPay to process their payments.', 'woo-idpay-gateway' );
		$this->has_fields         = FALSE;
		$this->icon               = apply_filters( 'WC_IDPay_logo', dirname( WP_PLUGIN_URL . '/' . plugin_basename( dirname( __FILE__ ) ) ) . '/assets/images/logo.png' );

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Get setting values.
		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );

		$this->api_key = $this->get_option( 'api_key' );
		$this->sandbox = $this->get_option( 'sandbox' );

		$this->payment_endpoint = $this->get_option( 'payment_endpoint' );
		$this->verify_endpoint  = $this->get_option( 'verify_endpoint' );

		$this->success_massage = $this->get_option( 'success_massage' );
		$this->failed_massage  = $this->get_option( 'failed_massage' );

		if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
				$this,
				'process_admin_options',
			) );
		} else {
			add_action( 'woocommerce_update_options_payment_gateways', array(
				$this,
				'process_admin_options',
			) );
		}

		add_action( 'woocommerce_receipt_' . $this->id, array(
			$this,
			'idpay_checkout_receipt_page',
		) );
		add_action( 'woocommerce_api_' . strtolower( get_class( $this ) ), array(
			$this,
			'idpay_checkout_return_handler',
		) );
	}

	/**
	 * Admin options for the gateway.
	 */
	public function admin_options() {
		parent::admin_options();
	}

	/**
	 * Processes and saves the gateway options in the admin page.
	 *
	 * @return bool|void
	 */
	public function process_admin_options() {
		parent::process_admin_options();
	}

	/**
	 * Initiate some form fields for the gateway settings.
	 */
	public function init_form_fields() {
		// Populates the inherited property $form_fields.
		$this->form_fields = apply_filters( 'WC_IDPay_Config', array(
			'enabled'           => array(
				'title'       => __( 'Enable/Disable', 'woo-idpay-gateway' ),
				'type'        => 'checkbox',
				'label'       => 'Enable IDPay gateway',
				'description' => '',
				'default'     => 'yes',
			),
			'title'             => array(
				'title'       => __( 'Title', 'woo-idpay-gateway' ),
				'type'        => 'text',
				'description' => __( 'This gateway title will be shown when a customer is going to to checkout.', 'woo-idpay-gateway' ),
				'default'     => __( 'IDPay payment gateway', 'woo-idpay-gateway' ),
			),
			'description'       => array(
				'title'       => __( 'Description', 'woo-idpay-gateway' ),
				'type'        => 'textarea',
				'description' => __( 'This gateway description will be shown when a customer is going to to checkout.', 'woo-idpay-gateway' ),
				'default'     => __( 'Redirects customers to IDPay to process their payments.', 'woo-idpay-gateway' ),
			),
			'webservice_config' => array(
				'title'       => __( 'Webservice Configuration', 'woo-idpay-gateway' ),
				'type'        => 'title',
				'description' => '',
			),
			'api_key'           => array(
				'title'       => __( 'API Key', 'woo-idpay-gateway' ),
				'type'        => 'text',
				'description' => __( 'You can create an API Key by going to https://idpay.ir/dashboard/web-services', 'woo-idpay-gateway' ),
				'default'     => '',
			),
			'sandbox'           => array(
				'title'       => __( 'Sandbox', 'woo-idpay-gateway' ),
				'label'       => __( 'Enable sandbox mode', 'woo-idpay-gateway' ),
				'description' => __( 'If you check this option, the gateway works in test (sandbox) mode.', 'woo-idpay-gateway' ),
				'type'        => 'checkbox',
				'default'     => 'no',
			),
			'payment_endpoint'  => array(
				'title'       => __( 'Payment endpoint', 'woo-idpay-gateway' ),
				'type'        => 'text',
				'description' => __( '', 'woo-idpay-gateway' ),
				'default'     => 'https://test.idpay.ir/v1.1/payment',
			),
			'verify_endpoint'   => array(
				'title'       => __( 'Verify endpoint', 'woo-idpay-gateway' ),
				'type'        => 'text',
				'description' => __( '', 'woo-idpay-gateway' ),
				'default'     => 'https://test.idpay.ir/v1.1/payment/verify',
			),
			'message_confing'   => array(
				'title'       => __( 'Payment message configuration', 'woo-idpay-gateway' ),
				'type'        => 'title',
				'description' => __( 'Configure the messages which are displayed when a customer is brought back to the site from the gateway.', 'woo-idpay-gateway' ),
			),
			'success_massage'   => array(
				'title'       => __( 'Success message', 'woo-idpay-gateway' ),
				'type'        => 'textarea',
				'description' => __( 'Enter the message you want to display to the customer after a successful payment. You can also choose these placeholders {track_id}, {order_id} for showing the order id and the tracking id respectively.', 'woo-idpay-gateway' ),
				'default'     => __( 'Your payment has been successfully completed. Track id: {track_id}', 'woo-idpay-gateway' ),
			),
			'failed_massage'    => array(
				'title'       => __( 'Failure message', 'woo-idpay-gateway' ),
				'type'        => 'textarea',
				'description' => __( 'Enter the message you want to display to the customer after a failure occurred in a payment. You can also choose these placeholders {track_id}, {order_id} for showing the order id and the tracking id respectively.', 'woo-idpay-gateway' ),
				'default'     => __( 'Your payment has failed. Please try again or contact the site administrator in case of a problem.', 'woo-idpay-gateway' ),
			),
		) );
	}

	/**
	 * Process the payment and return the result.
	 *
	 * see process_order_payment() in the Woocommerce APIs
	 *
	 * @param int $order_id
	 *
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = new WC_Order( $order_id );

		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url( TRUE ),
		);
	}

	/**
	 * Add IDPay Checkout items to receipt page.
	 */
	public function idpay_checkout_receipt_page( $order_id ) {
		global $woocommerce;

		$order    = new WC_Order( $order_id );
		$currency = $order->get_order_currency();
		$currency = apply_filters( 'WC_IDPay_Currency', $currency, $order_id );

		$api_key = $this->api_key;
		$sandbox = $this->sandbox == 'no' ? 'false' : 'true';

		$amount   = wc_idpay_get_amount( intval( $order->get_total() ), $currency );
		$desc     = __( 'Oder number #', 'woo-idpay-gateway' ) . $order->get_order_number();
		$callback = add_query_arg( 'wc_order', $order_id, WC()->api_request_url( 'wc_idpay' ) );

		if ( empty( $amount ) ) {
			$notice = __( 'selected currency is not supported', 'woo-idpay-gateway' );
			wc_add_notice( $notice, 'error' );

			return FALSE;
		}

		$data = array(
			'order_id' => $order_id,
			'amount'   => $amount,
			'phone'    => '',
			'desc'     => $desc,
			'callback' => $callback,
		);

		$headers = array(
			'Content-Type' => 'application/json',
			'X-API-KEY'    => $api_key,
			'X-SANDBOX'    => $sandbox,
		);

		$args = array(
			'body'    => json_encode( $data ),
			'headers' => $headers,
		);


		$response    = wp_safe_remote_post( $this->payment_endpoint, $args );
		$http_status = wp_remote_retrieve_response_code( $response );
		$result      = wp_remote_retrieve_body( $response );
		$result      = json_decode( $result );

		if ( $http_status != 201 || empty( $result ) || empty( $result->id ) || empty( $result->link ) ) {
			$note = '';
			$note .= __( 'An error occurred while creating the transaction.', 'woo-idpay-gateway' );
			$note .= '<br/>';
			$note .= sprintf( __( 'error status: %s', 'woo-idpay-gateway' ), $http_status );
			$order->add_order_note( $note );

			if ( ! empty( $result->error_code ) && ! empty( $result->error_message ) ) {
				$note = '';
				$note .= sprintf( __( 'error code: %s', 'woo-idpay-gateway' ), $result->error_code );
				$note .= '<br/>';
				$note .= sprintf( __( 'error message: %s', 'woo-idpay-gateway' ), $result->error_message );
				$order->add_order_note( $note );

				$notice = $result->error_message;
				wc_add_notice( $notice, 'error' );
			}

			return FALSE;
		}

		// Save ID of this transaction
		update_post_meta( $order_id, '_transaction_id', $result->id );

		$note = sprintf( __( 'transaction id: %s', 'woo-idpay-gateway' ), $result->id );
		$order->add_order_note( $note );
		update_post_meta( $order_id, 'idpay_id', $result->id );

		wp_redirect( $result->link );
	}

	/**
	 * Handles the return from processing the payment.
	 */
	public function idpay_checkout_return_handler() {
		global $woocommerce;

		if ( empty( $_POST['id'] ) || empty( $_POST['order_id'] ) ) {
			return FALSE;
		}

		$order_id = $_POST['order_id'];

		if ( empty( $order_id ) ) {
			$this->idpay_display_invalid_order_message();
			wp_redirect( $woocommerce->cart->get_checkout_url() );
			exit;
		}

		$order = wc_get_order( $order_id );

		if ( empty( $order ) ) {
			$this->idpay_display_invalid_order_message();
			wp_redirect( $woocommerce->cart->get_checkout_url() );
			exit;
		}

		if ( $this->double_spending_occurred( $order_id, $_POST['id'] ) ) {
			$this->idpay_display_invalid_order_message();
			wp_redirect( $woocommerce->cart->get_checkout_url() );
			exit;
		}

		if ( $order->status == 'completed' || $order->status == 'processing' ) {
			$this->idpay_display_success_message( $order_id );
			wp_redirect( add_query_arg( 'wc_status', 'success', $this->get_return_url( $order ) ) );
			exit;
		}

		$api_key = $this->api_key;
		$sandbox = $this->sandbox == 'no' ? 'false' : 'true';

		$data = array(
			'id'       => get_post_meta( $order_id, '_transaction_id', TRUE ),
			'order_id' => $order_id,
		);

		$headers = array(
			'Content-Type' => 'application/json',
			'X-API-KEY'    => $api_key,
			'X-SANDBOX'    => $sandbox,
		);

		$args = array(
			'body'    => json_encode( $data ),
			'headers' => $headers,
		);

		$response    = wp_safe_remote_post( $this->verify_endpoint, $args );
		$http_status = wp_remote_retrieve_response_code( $response );
		$result      = wp_remote_retrieve_body( $response );
		$result      = json_decode( $result );

		if ( $http_status != 200 ) {
			$note = '';
			$note .= __( 'An error occurred while verifying the transaction.', 'woo-idpay-gateway' );
			$note .= '<br/>';
			$note .= sprintf( __( 'error status: %s', 'woo-idpay-gateway' ), $http_status );
			$order->add_order_note( $note );

			if ( ! empty( $result->error_code ) && ! empty( $result->error_message ) ) {
				$note = '';
				$note .= sprintf( __( 'error code: %s', 'woo-idpay-gateway' ), $result->error_code );
				$note .= '<br/>';
				$note .= sprintf( __( 'error message: %s', 'woo-idpay-gateway' ), $result->error_message );
				$order->add_order_note( $note );

				$notice = $result->error_message;
				wc_add_notice( $notice, 'error' );
			}

			wp_redirect( $woocommerce->cart->get_checkout_url() );
			exit;
		} else {
			$verify_status   = empty( $result->status ) ? NULL : $result->status;
			$verify_track_id = empty( $result->track_id ) ? NULL : $result->track_id;
			$verify_id       = empty( $result->id ) ? NULL : $result->id;
			$verify_order_id = empty( $result->order_id ) ? NULL : $result->order_id;
			$verify_amount   = empty( $result->amount ) ? NULL : $result->amount;
			$verify_card_no  = empty( $result->card_no ) ? NULL : $result->card_no;
			$verify_date     = empty( $result->date ) ? NULL : $result->date;

			$status = ( $verify_status >= 100 ) ? 'processing' : 'failed';

			$note = sprintf( __( 'IDPay tracking id: %s', 'woo-idpay-gateway' ), $verify_track_id );
			$order->add_order_note( $note );
			update_post_meta( $order_id, 'idpay_track_id', $verify_track_id );

			$note = sprintf( __( 'Transaction payment status: %s', 'woo-idpay-gateway' ), $verify_status );
			$order->add_order_note( $note );
			update_post_meta( $order_id, 'idpay_status', $verify_status );

			$note = sprintf( __( 'Payer card number: %s', 'woo-idpay-gateway' ), $verify_card_no );
			$order->add_order_note( $note );
			update_post_meta( $order_id, 'idpay_card_no', $verify_card_no );

			$currency = $order->get_currency();
			$currency = apply_filters( 'WC_IDPay_Currency', $currency, $order_id );
			$amount   = wc_idpay_get_amount( intval( $order->get_total() ), $currency );

			if ( empty( $verify_status ) || empty( $verify_track_id ) || empty( $verify_amount ) || $verify_amount != $amount ) {
				$note = __( 'Error in transaction status or inconsistency with payment gateway information', 'woo-idpay-gateway' );
				$order->add_order_note( $note );
				$status = 'failed';
			}

			if ( $status == 'failed' ) {
				$order->update_status( $status );
				$this->idpay_display_failed_message( $order_id );

				wp_redirect( $woocommerce->cart->get_checkout_url() );
				exit;
			} elseif ( $status == 'processing' ) {
				$order->payment_complete( $verify_id );
				$order->update_status( $status );
				$woocommerce->cart->empty_cart();
				$this->idpay_display_success_message( $order_id );

				wp_redirect( add_query_arg( 'wc_status', 'success', $this->get_return_url( $order ) ) );
				exit;
			}
		}
	}

	/**
	 * Shows an invalid order message.
	 *
	 * @see idpay_checkout_return_handler().
	 */
	private function idpay_display_invalid_order_message() {
		$notice = '';
		$notice .= __( 'There is no order number referenced.', 'woo-idpay-gateway' );
		$notice .= '<br/>';
		$notice .= __( 'Please try again or contact the site administrator in case of a problem.', 'woo-idpay-gateway' );
		wc_add_notice( $notice, 'error' );
	}

	/**
	 * Shows a success message
	 *
	 * This message is configured at the admin page of the gateway.
	 *
	 * @see idpay_checkout_return_handler()
	 *
	 * @param $order_id
	 */
	private function idpay_display_success_message( $order_id ) {
		$track_id = get_post_meta( $order_id, 'idpay_track_id', TRUE );

		$notice = wpautop( wptexturize( $this->success_massage ) );
		$notice = str_replace( "{track_id}", $track_id, $notice );
		$notice = str_replace( "{order_id}", $order_id, $notice );
		wc_add_notice( $notice, 'success' );
	}

	/**
	 * Shows a failure message for the unsuccessful payments.
	 *
	 * This message is configured at the admin page of the gateway.
	 *
	 * @see idpay_checkout_return_handler()
	 *
	 * @param $order_id
	 */
	private function idpay_display_failed_message( $order_id ) {
		$track_id = get_post_meta( $order_id, 'idpay_track_id', TRUE );

		$notice = wpautop( wptexturize( $this->failed_massage ) );
		$notice = str_replace( "{track_id}", $track_id, $notice );
		$notice = str_replace( "{order_id}", $order_id, $notice );
		wc_add_notice( $notice, 'error' );
	}

	/**
	 * Checks if double-spending is occurred.
	 *
	 * @param $order_id
	 * @param $remote_id
	 *
	 * @return bool
	 */
	private function double_spending_occurred( $order_id, $remote_id ) {
		if ( get_post_meta( $order_id, '_transaction_id', TRUE ) != $remote_id ) {
			return TRUE;
		}

		return FALSE;
	}
}