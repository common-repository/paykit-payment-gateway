<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/utils/amount.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/utils/currency.php';

class Paykit_Checkout_Page_Service
{
  static public function get_current_cart_currency_note_html(): string
  {
    $currency = get_woocommerce_currency();
    $cart_total_str =  WC()->cart ? WC()->cart->total : null;

    if ($cart_total_str && $currency !== 'VND') {
      $cart_total = (float)$cart_total_str;
      $cart_total_in_vnd = paykit_get_amount_in_vnd($cart_total, $currency);
      $conversion_rate = paykit_get_current_currency_conversion_rate();

      if ($cart_total_in_vnd && $conversion_rate) {
        $thousand_separator = apply_filters('wc_get_price_thousand_separator', ',');
        $decimal_separator = apply_filters('wc_get_price_decimal_separator', '.');

        $cart_total_formatted = number_format($cart_total, 10, $decimal_separator, $thousand_separator);
        $cart_total_formatted = rtrim($cart_total_formatted, '0');
        $cart_total_formatted = rtrim($cart_total_formatted, $decimal_separator);

        return '<div style="
                        font-size: small;
                        font-style: italic;
                        margin: 4px 0px
                      ">
                  '
          . sprintf(
            /* translators: %1$s: total amount in VND, %2$s: total amount in current currency, %3$s: current currency, %4$s: exchange rate to VND */
            __('Note: Payment amount will be %1$s for %2$s (Exchange rate: %3$s = %4$s)', 'paykit-payment-gateway'),
            '<b>' . number_format($cart_total_in_vnd, 0, $decimal_separator, $thousand_separator) . ' VND</b>',
            '<b>' . $cart_total_formatted . ' ' . $currency . '</b>',
            '1 ' . $currency,
            number_format($conversion_rate, 0, $decimal_separator, $thousand_separator) . ' VND'
          ) . '</div>';
      }
    }

    return '';
  }
}
