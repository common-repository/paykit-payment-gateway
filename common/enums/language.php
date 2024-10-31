<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Paykit_Language_Enum
{
  const VI = 'vi';
  const EN = 'en';

  public static function values(): array
  {
    return [
      self::VI,
      self::EN,
    ];
  }
}
