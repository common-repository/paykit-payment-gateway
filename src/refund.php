<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/enums/payment-method.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/enums/refund-meta-key.php';

/**
 * When custom payment gateway, process_refund funtion parameters don't include refund_id, so we need 2 steps below
 */
// Custom refund id and add id to reason
add_action('woocommerce_create_refund', function (WC_Order_Refund $refund, $args) {
  if (!paykit_is_order_paid_via_paykit_pgw($refund->get_parent_id())) return;

  $refund_id = $refund->get_id();
  $refund->update_meta_data(Paykit_Refund_Meta_Key_Enum::PAYKIT_REFUND_ID, $refund_id);
  $refund->set_reason($refund->get_reason() . ' ###wc_paykit_pgw_refund' . $refund_id);
  $refund->save();
}, 10, 2);

// Remove id in reason when refund created
add_action('woocommerce_refund_created', function (string $refund_id, $args) {
  $refund = wc_get_order($refund_id);
  if (!$refund) return;
  if (!paykit_is_order_paid_via_paykit_pgw($refund->get_parent_id())) return;

  $reason = preg_replace('/ ###wc_paykit_pgw_refund\w+$/', '', $refund->get_reason());
  $refund->set_reason($reason);
  $refund->save();
}, 10, 2);

function paykit_is_order_paid_via_paykit_pgw(string $order_id)
{
  $order = wc_get_order($order_id);
  $payment_method_ids = array_map(fn($s) => strtolower($s), Paykit_Payment_Method_Enum::values());

  return $order && in_array($order->get_payment_method(), $payment_method_ids);
}
