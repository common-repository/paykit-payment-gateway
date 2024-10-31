<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/dtos/paykit-response.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/dtos/payment.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/dtos/refund.php';

class Paykit_Retrieve_Refund_Response extends Paykit_Response
{
  public ?Paykit_Payment $payment;
  public ?Paykit_Refund $refund;

  public function __construct(
    string $result,
    string $gateway_code,
    ?array $error,
    ?Paykit_Payment $payment,
    ?Paykit_Refund $refund
  ) {
    parent::__construct($result, $gateway_code, $error);

    $this->payment = $payment;
    $this->refund = $refund;
  }

  public static function from_json(array $data)
  {
    return new Paykit_Retrieve_Refund_Response(
      $data['result'],
      $data['gateway_code'],
      $data['error'] ?? null,
      $data['payment'] ? Paykit_Payment::from_json($data['payment']) : null,
      $data['refund'] ? Paykit_Refund::from_json($data['refund']) : null
    );
  }
}
