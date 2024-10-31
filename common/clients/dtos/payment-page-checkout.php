<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/dtos/paykit-response.php';

class Paykit_Payment_Page_Checkout_Response extends Paykit_Response
{
  public ?string $checkout_url;
  public ?array $payment;

  public function __construct(
    string $result,
    string $gateway_code,
    ?array $error,
    ?string $checkout_url,
    ?array $payment
  ) {
    parent::__construct($result, $gateway_code, $error);

    $this->checkout_url = $checkout_url;
    $this->payment = $payment;
  }

  public static function from_json(array $data)
  {
    return new Paykit_Payment_Page_Checkout_Response(
      $data['result'],
      $data['gateway_code'],
      $data['error'] ?? null,
      $data['checkout_url'] ?? null,
      $data['payment'] ?? null
    );
  }
}
