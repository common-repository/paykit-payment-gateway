<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/enums/result.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/enums/gateway-code.php';

class Paykit_Response
{
  public string $result;
  public string $gateway_code;
  public ?array $error;

  public function __construct(
    string $result,
    string $gateway_code,
    ?array $error
  ) {
    $this->result = $result;
    $this->gateway_code = $gateway_code;
    $this->error = $error;
  }

  public static function from_json(array $data)
  {
    return new Paykit_Response(
      $data['result'],
      $data['gateway_code'],
      $data['error'] ?? null
    );
  }
}
