<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Paykit_Refund_Status_Enum
{
  const PROCESSING = 'PROCESSING';
  const CLOSED = 'CLOSED';
}

class Paykit_Refund_Result_Enum
{
  const APPROVED = 'APPROVED';
  const DENIED = 'DENIED';
}
