<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/dtos/paykit-response.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/dtos/payment.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/dtos/refund.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/utils/datetime.php';

class Paykit_Http_Webhook_Request
{
  public DateTime $request_at;
  public string $payment_id;
  public ?string $refund_id;

  public function __construct(
    DateTime $request_at,
    string $payment_id,
    ?string $refund_id
  ) {
    $this->request_at = $request_at;
    $this->payment_id = $payment_id;
    $this->refund_id = $refund_id;
  }

  public static function from_json(array $data)
  {
    return new Paykit_Http_Webhook_Request(
      paykit_convert_iso8601_str_to_datetime($data['request_at']),
      $data['payment_id'],
      $data['refund_id'] ?? null
    );
  }
}
