<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

function paykit_get_amount_in_vnd(float $amount, string $current_currency): ?int
{
  if ($current_currency === 'VND') return (int)ceil($amount);

  $currency_conversions = get_option(Paykit_Setting_Key_Enum::CURRENCY_CONVERSION);
  if (!$currency_conversions) return null;

  foreach ($currency_conversions as $index => $currency_conversion) {
    if ($currency_conversion['currency'] === $current_currency) {
      return (int)ceil($amount * $currency_conversion['conversion_rate']);
    }
  }

  return null;
}
