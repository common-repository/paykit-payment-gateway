<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/enums/refund.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/utils/datetime.php';

class Paykit_Refund
{
  public string $id;
  public string $payment_id;
  public float $amount;
  public string $currency;
  public string $status;
  public ?string $result;
  public DateTime $start_at;
  public ?DateTime $completed_at;

  public function __construct(
    string $id,
    string $payment_id,
    float $amount,
    string $currency,
    string $status,
    ?string $result,
    DateTime $start_at,
    ?DateTime $completed_at
  ) {
    $this->id = $id;
    $this->payment_id = $payment_id;
    $this->amount = $amount;
    $this->currency = $currency;
    $this->status = $status;
    $this->result = $result;
    $this->start_at = $start_at;
    $this->completed_at = $completed_at;
  }

  public static function from_json(array $data)
  {
    return new Paykit_Refund(
      $data['id'],
      $data['payment_id'],
      $data['amount'],
      $data['currency'],
      $data['status'],
      $data['result'] ?? '',
      paykit_convert_iso8601_str_to_datetime($data['start_at']),
      $data['completed_at'] ? paykit_convert_iso8601_str_to_datetime($data['completed_at']) : null,
    );
  }
}
