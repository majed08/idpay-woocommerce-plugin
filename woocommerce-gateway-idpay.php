<?php
/**
 * Plugin Name: WooCommerce IDPay Gateway
 * Author: IDPay
 * Description: درگاه پرداخت امن <a href="https://idpay.ir">آیدی پی</a> برای فروشگاه ساز ووکامرس
 * Version: 1.2
 * Author URI: https://idpay.ir
 * Author Email: info@idpay.ir
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('plugins_loaded', 'woocommerce_gateway_idpay_init');

function woocommerce_gateway_idpay_init()
{

    if (class_exists('WC_Payment_Gateway') && !class_exists('WC_IDPay') && !function_exists('woocommerce_add_idpay_gateway')) {

        add_filter('woocommerce_payment_gateways', 'woocommerce_add_idpay_gateway');

        function woocommerce_add_idpay_gateway($methods)
        {
            $methods[] = 'WC_IDPay';
            return $methods;
        }

        add_filter('woocommerce_currencies', 'idpay_IR_currency');

        function idpay_IR_currency($currencies)
        {
            $currencies['IRR'] = __('ریال', 'woocommerce');
            $currencies['IRT'] = __('تومان', 'woocommerce');
            $currencies['IRHR'] = __('هزار ریال', 'woocommerce');
            $currencies['IRHT'] = __('هزار تومان', 'woocommerce');

            return $currencies;
        }

        add_filter('woocommerce_currency_symbol', 'idpay_IR_currency_symbol', 10, 2);

        function idpay_IR_currency_symbol($currency_symbol, $currency)
        {
            switch ($currency) {
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

        class WC_IDPay extends WC_Payment_Gateway
        {
            public function __construct()
            {
                $this->id = 'WC_IDPay';
                $this->method_title = 'درگاه پرداخت آیدی پی';
                $this->method_description = 'پرداخت توسط درگاه پرداخت آیدی پی';
                $this->has_fields = false;
                $this->icon = apply_filters('WC_IDPay_logo', WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)) . '/assets/images/logo.png');

                // Load the form fields.
                $this->init_form_fields();

                // Load the settings.
                $this->init_settings();

                // Get setting values.
                $this->title = $this->get_option('title');
                $this->description = $this->get_option('description');

                $this->api_key = $this->get_option('api_key');
                $this->sandbox = $this->get_option('sandbox');

                $this->success_massage = $this->get_option('success_massage');
                $this->failed_massage = $this->get_option('failed_massage');

                // Hooks.
                if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
                    add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
                }
                else {
                    add_action('woocommerce_update_options_payment_gateways', array($this, 'process_admin_options'));
                }

                add_action('woocommerce_receipt_' . $this->id, array($this, 'idpay_checkout_receipt_page'));
                add_action('woocommerce_api_' . strtolower(get_class($this)), array($this, 'idpay_checkout_return_handler'));
            }

            public function admin_options()
            {
                parent::admin_options();
            }

            public function init_form_fields()
            {
                $this->form_fields = apply_filters('WC_IDPay_Config', array(
                    'enabled' => array(
                        'title' => 'فعال/غیرفعال',
                        'type' => 'checkbox',
                        'label' => 'فعال سازی درگاه پرداخت آیدی پی',
                        'description' => '',
                        'default' => 'yes',
                    ),
                    'title' => array(
                        'title' => 'عنوان',
                        'type' => 'text',
                        'description' => '',
                        'default' => 'درگاه پرداخت آیدی پی',
                    ),
                    'description' => array(
                        'title' => 'توضیحات',
                        'type' => 'textarea',
                        'description' => '',
                        'default' => 'پرداخت توسط درگاه پرداخت آیدی پی',
                    ),
                    'webservice_config' => array(
                        'title' => 'تنظیمات وب سرویس',
                        'type' => 'title',
                        'description' => '',
                    ),
                    'api_key' => array(
                        'title' => 'API Key',
                        'type' => 'text',
                        'description' => '',
                        'default' => '',
                    ),
                    'sandbox' => array(
                        'title' => 'آزمایشگاه',
                        'type' => 'checkbox',
                        'default' => 'no'
                    ),
                    'message_confing' => array(
                        'title' => 'تنظیمات پیام ها',
                        'type' => 'title',
                        'description' => '',
                    ),
                    'success_massage' => array(
                        'title' => 'پیام پرداخت موفق',
                        'type' => 'textarea',
                        'description' => 'متن پیامی که می خواهید بعد از پرداخت موفق به کاربر نمایش دهید را وارد کنید. همچنین می توانید از شورت کدهای {order_id} برای نمایش شماره سفارش و {track_id} برای نمایش کد رهگیری آیدی پی استفاده نمایید.',
                        'default' => 'پرداخت شما با موفقیت انجام شد. کد رهگیری: {track_id}',
                    ),
                    'failed_massage' => array(
                        'title' => 'پیام پرداخت ناموفق',
                        'type' => 'textarea',
                        'description' => 'متن پیامی که می خواهید بعد از پرداخت ناموفق به کاربر نمایش دهید را وارد کنید. همچنین می توانید از شورت کدهای {order_id} برای نمایش شماره سفارش و {track_id} برای نمایش کد رهگیری آیدی پی استفاده نمایید.',
                        'default' => 'پرداخت شما ناموفق بوده است. لطفا مجددا تلاش نمایید یا در صورت بروز اشکال با مدیر سایت تماس بگیرید.',
                    ),
                ));
            }

            public function process_payment($order_id)
            {
                $order = new WC_Order($order_id);

                return array(
                    'result' => 'success',
                    'redirect' => $order->get_checkout_payment_url(true),
                );
            }

            /**
             * Add IDPay Checkout items to receipt page.
             */
            public function idpay_checkout_receipt_page($order_id)
            {
                global $woocommerce;

                $order = new WC_Order($order_id);
                $currency = $order->get_order_currency();
                $currency = apply_filters('WC_IDPay_Currency', $currency, $order_id);

                $form = "";
                $form .= "<form id='idpay-checkout-form' class='woocommerce-checkout' method='POST' action=''>";
                $form .= "<input id='idpay-checkout-form-submit' type='submit' class='button' value='پرداخت' />";
                $form .= "<a class='button cancel' href='{$woocommerce->cart->get_checkout_url()}'>بازگشت</a>";
                $form .= "</form>";
                $form = apply_filters('WC_IDPay_Form', $form, $order_id, $woocommerce);

                do_action('WC_IDPay_Gateway_Before_Form', $order_id, $woocommerce);
                echo $form;
                do_action('WC_IDPay_Gateway_After_Form', $order_id, $woocommerce);


                $api_key = $this->api_key;
                $sandbox = $this->sandbox == 'no' ? 'false' : 'true';

                $amount = idpay_wc_get_amount(intval($order->order_total), $currency);
                $desc = 'سفارش شماره #' . $order->get_order_number();
                $callback = add_query_arg('wc_order', $order_id, WC()->api_request_url('wc_idpay'));

                if (empty($amount)) {
                    $notice = 'واحد پول انتخاب شده پشتیبانی نمی شود.';
                    wc_add_notice($notice, 'error');

                    return false;
                }

                $data = array(
                    'order_id' => $order_id,
                    'amount' => $amount,
                    'phone' => '',
                    'desc' => $desc,
                    'callback' => $callback,
                );

                $ch = curl_init('https://api.idpay.ir/v1/payment');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'X-API-KEY:' . $api_key,
                    'X-SANDBOX:' . $sandbox,
                ));

                $result = curl_exec($ch);
                $result = json_decode($result);
                $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($http_status != 201 || empty($result) || empty($result->id) || empty($result->link)) {
                    $note = '';
                    $note .= 'هنگام ایجاد تراکنش خطا رخ داده است.';
                    $note .= '<br/>';
                    $note .= sprintf('وضعیت خطا: %s', $http_status);
                    $order->add_order_note($note);

                    if (!empty($result->error_code) && !empty($result->error_message)) {
                        $note = '';
                        $note .= sprintf('کد خطا: %s', $result->error_code);
                        $note .= '<br/>';
                        $note .= sprintf('متن خطا: %s', $result->error_message);
                        $order->add_order_note($note);

                        $notice = $result->error_message;
                        wc_add_notice($notice, 'error');
                    }

                    return false;
                }

                // Save ID of this transaction
                update_post_meta($order_id, '_transaction_id', $result->id);

                $note = sprintf('کد تراکنش: %s', $result->id);
                $order->add_order_note($note);
                update_post_meta($order_id, 'idpay_id', $result->id);

                wp_redirect($result->link);
            }

            /**
             * Handles the return from processing the payment.
             */
            public function idpay_checkout_return_handler()
            {
                global $woocommerce;

                if (empty($_POST['id']) || empty($_POST['order_id'])) {
                    return false;
                }

                $order_id = $_POST['order_id'];

                if (empty($order_id)) {
                    $this->idpay_display_invalid_order_message();
                    wp_redirect($woocommerce->cart->get_checkout_url());
                    exit;
                }

                $order = wc_get_order($order_id);

                if (empty($order)) {
                    $this->idpay_display_invalid_order_message();
                    wp_redirect($woocommerce->cart->get_checkout_url());
                    exit;
                }

                if (get_post_meta($order_id, '_transaction_id', true) != $_POST['id']) {
                    $this->idpay_display_invalid_order_message();
                    wp_redirect($woocommerce->cart->get_checkout_url());
                    exit;
                }

                if ($order->status == 'completed') {
                    $this->idpay_display_success_message($order_id);
                    wp_redirect(add_query_arg('wc_status', 'success', $this->get_return_url($order)));
                    exit;
                }

                $api_key = $this->api_key;
                $sandbox = $this->sandbox == 'no' ? 'false' : 'true';

                $data = array(
                    'id' => get_post_meta($order_id, '_transaction_id', true),
                    'order_id' => $order_id,
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://api.idpay.ir/v1/payment/inquiry');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'X-API-KEY:' . $api_key,
                    'X-SANDBOX:' . $sandbox,
                ));

                $result = curl_exec($ch);
                $result = json_decode($result);
                $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($http_status != 200) {
                    $note = '';
                    $note .= 'هنگام بررسی وضعیت تراکنش خطا رخ داده است.';
                    $note .= '<br/>';
                    $note .= sprintf('وضعیت خطا: %s', $http_status);
                    $order->add_order_note($note);

                    if (!empty($result->error_code) && !empty($result->error_message)) {
                        $note = '';
                        $note .= sprintf('کد خطا: %s', $result->error_code);
                        $note .= '<br/>';
                        $note .= sprintf('متن خطا: %s', $result->error_message);
                        $order->add_order_note($note);

                        $notice = $result->error_message;
                        wc_add_notice($notice, 'error');
                    }

                    wp_redirect($woocommerce->cart->get_checkout_url());
                    exit;
                }

                $inquiry_status = empty($result->status) ? NULL : $result->status;
                $inquiry_track_id = empty($result->track_id) ? NULL : $result->track_id;
                $inquiry_id = empty($result->id) ? NULL : $result->id;
                $inquiry_order_id = empty($result->order_id) ? NULL : $result->order_id;
                $inquiry_amount = empty($result->amount) ? NULL : $result->amount;
                $inquiry_card_no = empty($result->card_no) ? NULL : $result->card_no;
                $inquiry_date = empty($result->date) ? NULL : $result->date;

                $status = ($inquiry_status == 100) ? 'completed' : 'failed';

                $note = sprintf('کد رهگیری آیدی پی: %s', $inquiry_track_id);
                $order->add_order_note($note);
                update_post_meta($order_id, 'idpay_track_id', $inquiry_track_id);

                $note = sprintf('وضعیت پرداخت تراکنش: %s', $inquiry_status);
                $order->add_order_note($note);
                update_post_meta($order_id, 'idpay_status', $inquiry_status);

                $note = sprintf('شماره کارت پرداخت کننده: %s', $inquiry_card_no);
                $order->add_order_note($note);
                update_post_meta($order_id, 'idpay_card_no', $inquiry_card_no);

                $currency = $order->get_order_currency();
                $currency = apply_filters('WC_IDPay_Currency', $currency, $order_id);
                $amount = idpay_wc_get_amount(intval($order->order_total), $currency);

                if (empty($inquiry_status) || empty($inquiry_track_id) || empty($inquiry_amount) || $inquiry_amount != $amount) {
                    $note = 'خطا در وضعیت تراکنش یا مغایرت با اطلاعات درگاه پرداخت';
                    $order->add_order_note($note);
                    $status = 'failed';
                }

                if ($status == 'failed') {
                    $order->update_status($status);
                    $this->idpay_display_failed_message($order_id);

                    wp_redirect($woocommerce->cart->get_checkout_url());
                    exit;
                }

                if ($status == 'completed') {
                    $order->payment_complete($inquiry_id);
                    $order->update_status($status);
                    $woocommerce->cart->empty_cart();
                    $this->idpay_display_success_message($order_id);

                    wp_redirect(add_query_arg('wc_status', 'success', $this->get_return_url($order)));
                    exit;
                }
            }

            function idpay_display_invalid_order_message()
            {
                $notice = '';
                $notice .= 'شماره سفارش ارجاع شده به آن وجود ندارد.';
                $notice .= '<br/>';
                $notice .= 'لطفا مجددا تلاش نمایید یا در صورت بروز اشکال با مدیر سایت تماس بگیرید.';
                wc_add_notice($notice, 'error');
            }

            function idpay_display_success_message($order_id)
            {
                $track_id = get_post_meta($order_id, 'idpay_track_id', true);

                $notice = wpautop(wptexturize($this->success_massage));
                $notice = str_replace("{track_id}", $track_id, $notice);
                $notice = str_replace("{order_id}", $order_id, $notice);
                wc_add_notice($notice, 'success');
            }

            function idpay_display_failed_message($order_id)
            {
                $track_id = get_post_meta($order_id, 'idpay_track_id', true);

                $notice = wpautop(wptexturize($this->failed_massage));
                $notice = str_replace("{track_id}", $track_id, $notice);
                $notice = str_replace("{order_id}", $order_id, $notice);
                wc_add_notice($notice, 'error');
            }
        }
    }
}

function idpay_wc_get_amount($amount, $currency)
{
    switch (strtolower($currency)) {
        case strtolower('IRR'):
        case strtolower('RIAL'):
            return $amount;

        case strtolower('تومان ایران'):
        case strtolower('تومان'):
        case strtolower('IRT'):
        case strtolower('Iranian_TOMAN'):
        case strtolower('Iran_TOMAN'):
        case strtolower('Iranian-TOMAN'):
        case strtolower('Iran-TOMAN'):
        case strtolower('TOMAN'):
        case strtolower('Iran TOMAN'):
        case strtolower('Iranian TOMAN'):
            return $amount * 10;

        case strtolower('IRHR'):
            return $amount * 1000;

        case strtolower('IRHT'):
            return $amount * 10000;

        default:
            return 0;
    }
}
