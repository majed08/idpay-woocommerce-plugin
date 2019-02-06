=== IDPay Payment Gateway for Woocommerce ===
Contributors: majidlotfinia, jazaali, imikiani
Tags: woocommerce, payment, idpay, gateway, آیدی پی
Stable tag: 2.1
Tested up to: 5.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

[IDPay](https://idpay.ir) payment method for [Woocommerce](https://wordpress.org/plugins/woocommerce/).

== Description ==

[IDPay](https://idpay.ir) is one of the Financial Technology providers in Iran.

IDPay provides some payment services and this plugin enables the IDPay's payment gateway for Woocommerce.

== Installation ==

After creating a web service on https://idpay.ir and getting an API Key, follow this instruction:

1. Activate plugin IDPay for Woocommerce.
2. Go tho WooCommerce > Settings > Payments.
3. Enable IDPay payment gateway.
4. Go to Manage.
5. Enter the API Key.

If you need to use this plugin in Test mode, check the "Sandbox".

Also there is a complete documentation [here](https://blog.idpay.ir/helps/99) which helps you to install the plugin step by step.

Thank you so much for using IDPay Payment Gateway.

== Changelog ==

= 2.1, February 06, 2019 =
* Fix bug.

= 2.0, February 05, 2019 =
* Publish for web service version 1.1.
* Improvements in the code.
* Increase timeout of wp_safe_remote_post().
* Fix bugs.
* Send customer information such as phone and email to the gateway.

= 1.0.6, December 09, 2018 =
* Change order status to 'processing' after a successful payment.
* Add 'Domain Path' to the plugin descriptions.

= 1.0.5, December 08, 2018 =
* Solve problem with strings' translations.

= 1.0.4, December 01, 2018 =
* Change text domain.
* Add asset images.

= 1.0.3, November 21, 2018 =
* [Coding Standards](https://codex.wordpress.org/WordPress_Coding_Standards).
* Change files structure.
* PHP documentations.
* Use wp_safe_remote_post() instead of curl.
* Translation of strings.

= 1.0.2, November 10, 2018 =
* Save and show errors which might be occurred during the payment process.

= 1.0.1, November 10, 2018 =
* Save card number when saving the payment details.

= 1.0, October 15, 2018 =
* First release.
