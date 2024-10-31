<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Paykit_Result_Enum
{
  const SUCCESS = 'SUCCESS';
  const FAILURE = 'FAILURE';
  const PENDING = 'PENDING';
  const ERROR = 'ERROR';
  const UNKNOWN = 'UNKNOWN';
}
