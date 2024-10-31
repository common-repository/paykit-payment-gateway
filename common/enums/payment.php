<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Paykit_Payment_Status_Enum
{
  const OPEN = 'OPEN';
  const PROCESSING = 'PROCESSING';
  const CLOSED = 'CLOSED';
}

class Paykit_Payment_Result_Enum
{
  const CANCELED = 'CANCELED';
  const APPROVED = 'APPROVED';
  const DENIED = 'DENIED';
  const EXPIRED = 'EXPIRED';
}
