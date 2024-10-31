<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/src/base/base-wc-paykit-pgw-blocks-support.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/src/international-card/international-card.php';


class Paykit_International_Card_WC_Blocks_Support extends Paykit_Base_WC_Blocks_Support
{
  public function initialize()
  {
    $this->script_url = plugins_url('/paykit-payment-gateway/assets/js/frontend/international-card-blocks.js');
    $this->script_asset_path = PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/assets/js/frontend/international-card-blocks.asset.php';
  }
}
