<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/enums/setting-key.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/enums/payment.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/enums/refund.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/enums/refund-meta-key.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/dtos/http-paykit-webhook-request.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/dtos/https-paykit-webhook-request.php';

function paykit_https_webhook_request_schema()
{
  return [
    'type' => 'object',
    'properties' => [
      'request_at' => [
        'type' => 'string',
        'required' => true
      ],
      'payment' => [
        'type' => 'object',
        'required' => true,
        'properties' => [
          'id' => [
            'type' => 'string',
            'required' => true
          ],
          'total_amount' => [
            'type' => 'number',
            'required' => true
          ],
          'captured_amount' => [
            'type' => 'number',
            'required' => true
          ],
          'refunded_amount' => [
            'type' => 'number',
            'required' => true
          ],
          'refunding_amount' => [
            'type' => 'number',
            'required' => true
          ],
          'currency' => [
            'type' => 'string',
            'required' => true
          ],
          'payment_method' => [
            'type' => 'string',
            'required' => false
          ],
          'status' => [
            'type' => 'string',
            'required' => true
          ],
          'result' => [
            'type' => 'string',
            'required' => false
          ],
          'due_time' => [
            'type' => 'string',
            'required' => true
          ],
          'start_at' => [
            'type' => 'string',
            'required' => true
          ],
          'completed_at' => [
            'type' => 'string',
            'required' => false
          ]
        ]
      ],
      'refund' => [
        'type' => 'object',
        'required' => false,
        'properties' => [
          'id' => [
            'type' => 'string',
            'required' => true
          ],
          'payment_id' => [
            'type' => 'string',
            'required' => true
          ],
          'amount' => [
            'type' => 'number',
            'required' => true
          ],
          'currency' => [
            'type' => 'string',
            'required' => true
          ],
          'status' => [
            'type' => 'string',
            'required' => true
          ],
          'result' => [
            'type' => 'string',
            'required' => false
          ],
          'start_at' => [
            'type' => 'string',
            'required' => true
          ],
          'completed_at' => [
            'type' => 'string',
            'required' => false
          ],
        ]
      ]
    ]
  ];
}

function paykit_http_webhook_request_schema()
{
  return [
    'type' => 'object',
    'properties' => [
      'request_at' => [
        'type' => 'string',
        'required' => true
      ],
      'payment_id' => [
        'type' => 'string',
        'required' => true
      ],
      'refund_id' => [
        'type' => 'string',
        'required' => false
      ]
    ]
  ];
}

add_action('rest_api_init', function () {
  // Endpoint: {base_url}/wp-json/paykit-pgw/webhook
  register_rest_route('paykit-pgw', '/webhook', [
    array(
      'methods' => 'POST',
      'callback' => 'paykit_handle_webhook',
      'permission_callback' => '__return_true'
    )
  ]);
});

function paykit_handle_webhook(WP_REST_Request $request)
{
  if (empty($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(WP_Http::NOT_FOUND);
    die();
  }

  $request_json = $request->get_json_params();

  if (str_starts_with(get_site_url(), 'https://')) {
    // Validate and sanitize request
    $schema = paykit_https_webhook_request_schema();
    if (!is_wp_error(rest_validate_value_from_schema($request_json, $schema))) {
      $sanitized_data = rest_sanitize_value_from_schema($request_json, $schema);
    } else {
      error_log('HTTPS invalid request');
      http_response_code(WP_Http::BAD_REQUEST);
      die();
    }

    // Check secret key
    if (empty($_SERVER['HTTP_SECRET_KEY'])) {
      http_response_code(WP_Http::BAD_REQUEST);
      die();
    }
    $ipn_secret_key = sanitize_text_field(wp_unslash($_SERVER['HTTP_SECRET_KEY']));
    if (!$ipn_secret_key || $ipn_secret_key !== get_option(Paykit_Setting_Key_Enum::IPN_SECRET_KEY)) {
      http_response_code(WP_Http::BAD_REQUEST);
      die();
    }

    // Parse request data
    try {
      $https_request_data = Paykit_Https_Webhook_Request::from_json($sanitized_data);
    } catch (Throwable $e) {
      error_log($e->getMessage());
      http_response_code(WP_Http::BAD_REQUEST);
      die();
    }

    paykit_handle_webhook_https($https_request_data);
  } else {
    // Validate and sanitize request
    $schema = paykit_http_webhook_request_schema();
    if (!is_wp_error(rest_validate_value_from_schema($request_json, $schema))) {
      $sanitized_data = rest_sanitize_value_from_schema($request_json, $schema);
    } else {
      error_log('HTTP invalid request');
      http_response_code(WP_Http::BAD_REQUEST);
      die();
    }

    // Parse request data
    try {
      $http_request_data = Paykit_Http_Webhook_Request::from_json($sanitized_data);
    } catch (Throwable $e) {
      error_log($e->getMessage());
      http_response_code(WP_Http::BAD_REQUEST);
      die();
    }

    paykit_handle_webhook_http($http_request_data);
  }
}

function paykit_handle_webhook_https(Paykit_Https_Webhook_Request $request_data)
{
  // Maybe update order
  $order = wc_get_order($request_data->payment->id);
  if (!$order) {
    http_response_code(WP_Http::BAD_REQUEST);
    die();
  }

  if (paykit_update_order_needed($order)) {
    if ($request_data->payment->status !== Paykit_Payment_Status_Enum::CLOSED) {
      return;
    }

    switch ($request_data->payment->result) {
      case Paykit_Payment_Result_Enum::APPROVED:
        $order->payment_complete($request_data->payment->id);
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

  // Maybe update refund
  if ($request_data->refund != null) {
    paykit_update_refund($order, $request_data->refund->id, $request_data->refund->amount, $request_data->refund->result);
  }
}

function paykit_handle_webhook_http(Paykit_Http_Webhook_Request $request_data)
{
  $order = wc_get_order($request_data->payment_id);
  if (!$order) {
    http_response_code(WP_Http::BAD_REQUEST);
    die();
  }

  $paykit_pgw_client = new Paykit_PGW_Client();

  if (paykit_update_order_needed($order) && !$request_data->refund_id) {
    // Retrieve data
    try {
      $data = $paykit_pgw_client->retrieve_payment($request_data->payment_id);
    } catch (Throwable $e) {
      error_log($e->getMessage());
      http_response_code(WP_Http::BAD_REQUEST);
      die();
    }

    if ($data->result === Paykit_Result_Enum::SUCCESS) {
      if ($data->payment->status !== Paykit_Payment_Status_Enum::CLOSED) {
        return;
      }

      // Maybe update order
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
  }

  // Maybe update refund
  if ($request_data->refund_id) {
    // Retrieve data
    try {
      $data = $paykit_pgw_client->retrieve_refund($request_data->payment_id, $request_data->refund_id);
    } catch (Throwable $e) {
      error_log($e->getMessage());
      http_response_code(WP_Http::BAD_REQUEST);
      die();
    }

    paykit_update_refund($order, $data->refund->id, $data->refund->amount, $data->refund->result);
  }
}

function paykit_update_order_needed(WC_Order $order): bool
{
  // Check status
  if (!$order || $order->get_status() !== 'on-hold') return false;

  // Check payment method
  $payment_method_ids = array_map(fn($s) => strtolower($s), Paykit_Payment_Method_Enum::values());
  if (!in_array($order->get_payment_method(), $payment_method_ids)) return false;

  return true;
}

function paykit_update_refund(WC_Order $order, string $refund_id, float $amount, ?string $result): void
{
  if (!$result) return;

  $refunds = $order->get_refunds();
  $refund_index = array_search($refund_id, array_map(fn($r) => $r->get_meta_data(Paykit_Refund_Meta_Key_Enum::PAYKIT_REFUND_ID), $refunds));

  // Refund from Paykit webapp
  if (!$refund_index) {
    if ($result === Paykit_Refund_Result_Enum::APPROVED) {
      paykit_create_refund($order, $refund_id, $amount);
    }
  }
  // Refund from Wordpress admin page
  else {
    $refund = $refunds[$refund_index];
    if ($refund->get_meta_data()['paykit_pgw_status'] !== Paykit_Refund_Status_Enum::PROCESSING) return;

    switch ($result) {
      case Paykit_Refund_Result_Enum::APPROVED:
        $refund->update_meta_data('paykit_pgw_status', Paykit_Refund_Status_Enum::CLOSED);
        $refund->save();
        break;
      case Paykit_Refund_Result_Enum::DENIED:
        $refund->delete();
        break;
    }
  }
}

function paykit_create_refund(WC_Order $order, string $refund_id, float $amount): void
{
  // Add meta data with value=microtime and unique=true to prevent duplicate refund
  $order->add_meta_data('_paykit_pgw_refund_' . $refund_id, microtime(true), true);
  $order->save_meta_data();

  $currency_conversion_rate = $order->get_meta(Paykit_Order_Meta_Key_Enum::CURRENCY_CONVERSION_RATE);
  if (!$currency_conversion_rate) $currency_conversion_rate = 1;

  $refund = wc_create_refund(array(
    'amount' => $amount / $currency_conversion_rate,
    'order_id' => $order->get_id(),
    'reason' => 'Refund from Paykit webapp, refund id: ' . $refund_id,
    'refund_payment' => false
  ));

  $refund->update_meta_data(Paykit_Refund_Meta_Key_Enum::PAYKIT_REFUND_ID, $refund_id);
  $refund->save_meta_data();
}
