<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/enums/setting-key.php';

// Only allow to pay pending orders
add_filter('woocommerce_valid_order_statuses_for_payment', function () {
  return array('pending', 'checkout-draft');
});
add_filter('woocommerce_my_account_my_orders_actions', function ($actions, WC_Order $order) {
  if ($order->get_status() !== 'pending') unset($actions['pay']);
  return $actions;
});


// defer emails from sending until after the order is sent
add_filter('woocommerce_defer_transactional_emails', '__return_true');

// Rollback stock when order is failed (because WooCommerce not do it for us)
add_action('woocommerce_order_status_on-hold_to_failed', 'wc_update_total_sales_counts');
add_action('woocommerce_order_status_on-hold_to_failed', 'wc_maybe_increase_stock_levels');

// Filter available payment gateways based on currency
add_filter('woocommerce_available_payment_gateways', 'paykit_filter_woocommerce_available_payment_gateways');
function paykit_filter_woocommerce_available_payment_gateways($available_gateways)
{
  $currency = get_woocommerce_currency();
  if ($currency === 'VND') return $available_gateways;

  // Not VND, check if currency is in currency conversions set by user for Paykit payment gateway
  $currency_conversions = get_option(Paykit_Setting_Key_Enum::CURRENCY_CONVERSION) ?? array();
  $available_gateways = array_filter($available_gateways, function ($gateway) use ($currency, $currency_conversions) {
    if (!($gateway instanceof Paykit_Base_WC_Payment_Gateway) && !($gateway instanceof Paykit_Base_WC_Blocks_Support)) {
      return true;
    }

    foreach ($currency_conversions as $index => $currency_conversion) {
      if ($currency_conversion['currency'] === $currency) {
        return true;
      }
    }

    // Not found in currency conversions
    return false;
  });
  return $available_gateways;
}
