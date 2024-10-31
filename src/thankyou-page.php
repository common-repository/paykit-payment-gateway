<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/clients/paykit-pgw-client.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/enums/payment-method.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/enums/refund.php';

add_action('woocommerce_before_thankyou', function (string $order_id) {
  $order = wc_get_order($order_id);
  if (!$order || $order->get_status() !== 'on-hold') return;

  $payment_method_ids = array_map(fn($s) => strtolower($s), Paykit_Payment_Method_Enum::values());
  if (!in_array($order->get_payment_method(), $payment_method_ids)) return;

  $paykit_pgw_client = new Paykit_PGW_Client();

  try {
    $data = $paykit_pgw_client->retrieve_payment($order->get_id());
  } catch (Throwable $e) {
    error_log($e->getMessage());
    return;
  }

  if ($data->result === Paykit_Result_Enum::SUCCESS) {
    if ($data->payment->status !== Paykit_Payment_Status_Enum::CLOSED) {
      return;
    }

    switch ($data->payment->result) {
      case Paykit_Payment_Result_Enum::APPROVED:
        $order->payment_complete($data->payment->id);
        break;
      case Paykit_Payment_Result_Enum::CANCELED:
        $order->update_status('cancelled');
        break;
      case Paykit_Payment_Result_Enum::DENIED:
        $order->update_status('failed');
        break;
      case Paykit_Payment_Result_Enum::EXPIRED:
        $order->update_status('failed');
        break;
    }
  }
});

add_filter('woocommerce_thankyou_order_received_text', function (string $text, WC_Order $order) {
  $order = wc_get_order($order->get_id());
  switch ($order->get_status()) {
    case 'completed':
      return __('Your order has been completed', 'paykit-payment-gateway');
    case 'cancelled':
      return __('Your order has been cancelled', 'paykit-payment-gateway');
    case 'processing':
      return __('Your order has been paid successfully', 'paykit-payment-gateway');
    case 'failed':
      return __('Order failed for some reason', 'paykit-payment-gateway');
  }

  return $text;
}, 10, 2);
