<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

function paykit_convert_iso8601_str_to_datetime(string $iso_str): ?DateTime
{
  if (!$iso_str) return null;

  $datetime = null;
  if (strpos($iso_str, '.') !== false) {
    $datetime = date_create_from_format('Y-m-d\TH:i:s.u\Z', $iso_str, new DateTimeZone("UTC"));
  } else {
    $datetime = date_create_from_format('Y-m-d\TH:i:s\Z', $iso_str, new DateTimeZone("UTC"));
  }

  if (!$datetime) {
    return null;
  } else {
    return $datetime;
  }
}
