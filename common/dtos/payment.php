<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/enums/payment-method.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/enums/payment.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/utils/datetime.php';

class Paykit_Payment
{
  public string $id;
  public float $total_amount;
  public float $captured_amount;
  public float $refunded_amount;
  public float $refunding_amount;
  public string $currency;
  public string $payment_method;
  public string $status;
  public ?string $result;
  public DateTime $due_time;
  public DateTime $start_at;
  public ?DateTime $completed_at;

  public function __construct(
    string $id,
    float $total_amount,
    float $captured_amount,
    float $refunded_amount,
    float $refunding_amount,
    string $currency,
    string $payment_method,
    string $status,
    ?string $result,
    DateTime $due_time,
    DateTime $start_at,
    ?DateTime $completed_at
  ) {
    $this->id = $id;
    $this->total_amount = $total_amount;
    $this->captured_amount = $captured_amount;
    $this->refunded_amount = $refunded_amount;
    $this->refunding_amount = $refunding_amount;
    $this->currency = $currency;
    $this->payment_method = $payment_method;
    $this->status = $status;
    $this->result = $result;
    $this->due_time = $due_time;
    $this->start_at = $start_at;
    $this->completed_at = $completed_at;
  }

  public static function from_json(array $data)
  {
    return new Paykit_Payment(
      $data['id'],
      $data['total_amount'],
      $data['captured_amount'],
      $data['refunded_amount'],
      $data['refunding_amount'],
      $data['currency'],
      $data['payment_method'],
      $data['status'],
      $data['result'] ?? '',
      paykit_convert_iso8601_str_to_datetime($data['due_time']),
      paykit_convert_iso8601_str_to_datetime($data['start_at']),
      $data['completed_at'] ? paykit_convert_iso8601_str_to_datetime($data['completed_at']) : null
    );
  }
}
