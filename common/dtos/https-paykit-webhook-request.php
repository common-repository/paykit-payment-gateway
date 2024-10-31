<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/dtos/paykit-response.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/dtos/payment.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/dtos/refund.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/utils/datetime.php';

class Paykit_Https_Webhook_Request
{
  public DateTime $request_at;
  public Paykit_Payment $payment;
  public ?Paykit_Refund $refund;

  public function __construct(
    DateTime $request_at,
    Paykit_Payment $payment,
    ?Paykit_Refund $refund
  ) {
    $this->request_at = $request_at;
    $this->payment = $payment;
    $this->refund = $refund;
  }

  public static function from_json(array $data)
  {
    return new Paykit_Https_Webhook_Request(
      paykit_convert_iso8601_str_to_datetime($data['request_at']),
      Paykit_Payment::from_json($data['payment']),
      $data['refund'] ? Paykit_Refund::from_json($data['refund']) : null
    );
  }
}
