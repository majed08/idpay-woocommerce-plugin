=== IDPay Payment Gateway for Woocommerce ===
Contributors: majidlotfinia, jazaali, imikiani
Tags: woocommerce, payment, idpay, gateway
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

[IDPay](https://idpay.ir) payment method for [Woocommerce](https://wordpress.org/plugins/woocommerce/).

== Description ==

[IDPay](https://idpay.ir) is one of the Financial Technology providers in Iran.

IDPay provides some payment services and this plugin enables the IDPay's payment gateway for Woocommerce.

== Installation ==

After creating a web service on https://idpay.ir and getting an API Key, follow the following instruction:

1. Activate plugin IDPay for Woocommerce.
2. Go tho WooCommerce > Settings > Payments.
3. Enable IDPay payment gateway.
4. Go to Manage.
5. Enter the API Key.

If you need to use this plugin in Test mode, check the "Sandbox". in the

== Changelog ==

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
