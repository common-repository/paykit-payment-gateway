<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/enums/setting-key.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/enums/payment-method.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/enums/result.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/clients/dtos/payment-page-checkout.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/clients/dtos/retrieve-payment.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/clients/dtos/retrieve-refund.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/clients/dtos/refund.php';

class Paykit_PGW_Client
{
  private string $base_url;
  private string $api_key;
  private string $secret_key;
  private string $ipn_secret_key;

  public function __construct()
  {
    $this->base_url = get_option(Paykit_Setting_Key_Enum::BASE_URL);
    $this->api_key = get_option(Paykit_Setting_Key_Enum::API_KEY);
    $this->secret_key = get_option(Paykit_Setting_Key_Enum::SECRET_KEY);
    $this->ipn_secret_key = get_option(Paykit_Setting_Key_Enum::IPN_SECRET_KEY);
  }

  private function prepare_request_args()
  {
    if (!$this->base_url || !$this->api_key || !$this->secret_key || !$this->ipn_secret_key) {
      throw new Exception(esc_html_e('Paykit payment gateway configuration not completed', 'paykit-payment-gateway'));
    }

    return array(
      'timeout'     => 30,
      'headers'     => array(
        'Content-Type' => 'application/json',
        'api-key' => $this->api_key,
        'secret-key' => $this->secret_key
      )
    );
  }

  private function post_http(string $url, array $body): array
  {
    $args = $this->prepare_request_args();
    $args['body'] = wp_json_encode($body);

    $response = wp_remote_post($url, $args);

    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
      $body = json_decode(wp_remote_retrieve_body($response), true);
      return $body;
    } else {
      $error_message = is_wp_error($response) ? $response->get_error_message() : 'HTTP code: ' . wp_remote_retrieve_response_code($response);
      throw new Exception(esc_html($error_message));
    }
  }

  public function payment_page_checkout(
    string $payment_method,
    string $payment_id,
    float $amount,
    string $currency,
    string $description = null,
    DateTime $due_time = null,
    string $success_url = null,
    string $cancel_url = null,
    string $ipn_url = null,
    string $display_language = null
  ): Paykit_Payment_Page_Checkout_Response {
    $url = $this->base_url . '/payment-page-checkout';

    $body = array(
      'payment_method' => $payment_method,
      'payment' => array(
        "id" => $payment_id,
        "amount" => $amount,
        "currency" => $currency
      )
    );
    if ($description) $body['payment']['description'] = $description;
    if ($due_time) $body['payment']['due_time'] = $due_time;
    if ($success_url) $body['payment']['success_url'] = $success_url;
    if ($cancel_url) $body['payment']['cancel_url'] = $cancel_url;
    if ($ipn_url) $body['payment']['ipn_url'] = $ipn_url;
    if ($display_language) $body['payment']['display_language'] = $display_language;

    $res_body = $this->post_http($url, $body);

    return Paykit_Payment_Page_Checkout_Response::from_json($res_body);
  }

  public function refund(
    string $payment_id,
    string $refund_id,
    float $amount,
    string $currency
  ): Paykit_Refund_Response {
    $url = $this->base_url . '/refund';
    $body = array(
      'payment_id' => $payment_id,
      'amount' => $amount,
      'refund_id' => $refund_id,
      'currency' => $currency
    );
    $res_body = $this->post_http($url, $body);

    return Paykit_Refund_Response::from_json($res_body);
  }

  public function retrieve_payment(
    string $payment_id
  ): Paykit_Retrieve_Payment_Response {
    $url = $this->base_url . '/retrieve-payment';
    $body = array('payment_id' => $payment_id);
    $res_body = $this->post_http($url, $body);

    return Paykit_Retrieve_Payment_Response::from_json($res_body);
  }

  public function retrieve_refund(
    string $payment_id,
    string $refund_id
  ): Paykit_Retrieve_Refund_Response {
    $url = $this->base_url . '/retrieve-refund';
    $body = array(
      'payment_id' => $payment_id,
      'refund_id' => $refund_id
    );
    $res_body = $this->post_http($url, $body);

    return Paykit_Retrieve_Refund_Response::from_json($res_body);
  }
}
