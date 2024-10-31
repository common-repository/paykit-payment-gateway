<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

function paykit_get_current_currency_conversion_rate(): ?float
{
  $currency = get_woocommerce_currency();
  if ($currency === 'VND') return 1;

  $currency_conversions = get_option(Paykit_Setting_Key_Enum::CURRENCY_CONVERSION);
  if (!$currency_conversions) return null;

  foreach ($currency_conversions as $index => $currency_conversion) {
    if ($currency_conversion['currency'] === $currency) {
      return (float)$currency_conversion['conversion_rate'];
    }
  }

  return null;
}
