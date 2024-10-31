<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Paykit_Setting_Key_Enum
{
  const BASE_URL = 'paykit_wc_pgw_settings_base_url';
  const API_KEY = 'paykit_wc_pgw_settings_api_key';
  const SECRET_KEY = 'paykit_wc_pgw_settings_secret_key';
  const IPN_SECRET_KEY = 'paykit_wc_pgw_settings_ipn_secret_key';
  const CURRENCY_CONVERSION = 'paykit_wc_pgw_settings_currency_conversion';

  public static function values(): array
  {
    return array(
      self::BASE_URL,
      self::API_KEY,
      self::SECRET_KEY,
      self::IPN_SECRET_KEY,
      self::CURRENCY_CONVERSION
    );
  }
}
