<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Paykit_Payment_Method_Enum
{
  const INTERNATIONAL_CARD = 'INTERNATIONAL_CARD';
  const DOMESTIC_CARD = 'DOMESTIC_CARD';
  const BANK_TRANSFER = 'BANK_TRANSFER';

  public static function values(): array
  {
    return [
      self::INTERNATIONAL_CARD,
      self::DOMESTIC_CARD,
      self::BANK_TRANSFER
    ];
  }
}
