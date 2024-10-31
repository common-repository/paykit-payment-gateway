<?php

/**
 * Paykit payment gateway
 *
 * @package           Paykit_Payment_Gateway
 * @author            Paykit
 * @copyright         2023 Paykit
 *
 * @wordpress-plugin
 * Plugin Name:       Paykit payment gateway
 * Description:       Increase revenue and enhance customer experience with Paykit - Vietnam's leading online payment gateway, now available on WordPress!
 * Version:           1.0.1
 * Requires PHP:      7.4
 * Author:            Paykit
 * Author URI:        https://paykit.vn/vn/home
 * Text Domain:       paykit-payment-gateway
 * Domain Path:       /languages
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

define('PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))))
  return;

// register styles, scripts and load resource on initialization
add_action('init', function () {
  wp_register_style('paykit_pgw_style', plugins_url('/styles/thankyou-page.css', __FILE__), array(), '1.0.0');
  wp_register_script('admin_paykit_pgw_script', plugins_url('/scripts/hide-show-password.js', __FILE__), array(), '1.0.0', true);

  load_plugin_textdomain('paykit-payment-gateway', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

add_action('wp_enqueue_scripts', function () {
  wp_enqueue_style('paykit_pgw_style');
});

add_action('admin_enqueue_scripts', function () {
  wp_enqueue_script('admin_paykit_pgw_script');
});

add_action('plugins_loaded', 'paykit_init', 11);
function paykit_init()
{
  // Check if WooCommerce Payment Gateways is available
  if (!class_exists('WC_Payment_Gateway')) return;

  require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/src/setting-form.php';

  require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/src/wc-order-rules-overriding.php';

  require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/src/international-card/international-card.php';
  require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/src/domestic-card/domestic-card.php';
  require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/src/bank-transfer/bank-transfer.php';

  require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/src/thankyou-page.php';
  require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/src/checkout-page.php';

  require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/src/webhook.php';

  require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/src/refund.php';
}

// Support checkout block
add_action('woocommerce_blocks_loaded', function () {
  require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/src/refund.php';

  if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
    require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/src/international-card/international-card.php';
    require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/src/domestic-card/domestic-card.php';
    require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/src/bank-transfer/bank-transfer.php';
    require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/src/international-card/blocks-support.php';
    require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/src/domestic-card/blocks-support.php';
    require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/src/bank-transfer/blocks-support.php';

    add_action(
      'woocommerce_blocks_payment_method_type_registration',
      function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
        $international_card = new Paykit_International_Card_WC_Payment_Gateway();
        $domestic_card = new Paykit_Domestic_Card_WC_Payment_Gateway();
        $bank_transfer = new Paykit_Bank_Transfer_WC_Payment_Gateway();
        $payment_method_registry->register(new Paykit_International_Card_WC_Blocks_Support($international_card));
        $payment_method_registry->register(new Paykit_Domestic_Card_WC_Blocks_Support($domestic_card));
        $payment_method_registry->register(new Paykit_Bank_Transfer_WC_Blocks_Support($bank_transfer));
      }
    );
  }
}, 11);

// Load .mo file to translate text
function paykit_load_paykit_textdomain($mofile, $domain)
{
  if ('paykit-payment-gateway' === $domain && false !== strpos($mofile, WP_LANG_DIR . '/plugins/')) {
    $locale = apply_filters('plugin_locale', determine_locale(), $domain);
    $mofile = WP_PLUGIN_DIR . '/' . dirname(plugin_basename(__FILE__)) . '/languages/mo/' . $domain . '-' . $locale . '.mo';
  }
  return $mofile;
}
add_filter('load_textdomain_mofile', 'paykit_load_paykit_textdomain', 10, 2);
