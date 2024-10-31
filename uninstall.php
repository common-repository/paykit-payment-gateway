<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
  die;
}

require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/enums/setting-key.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/enums/payment-method.php';

foreach (Paykit_Setting_Key_Enum::values() as $key) {
  delete_option($key);
}

delete_option('woocommerce_' . strtolower(Paykit_Payment_Method_Enum::INTERNATIONAL_CARD) . '_settings');
delete_option('woocommerce_' . strtolower(Paykit_Payment_Method_Enum::DOMESTIC_CARD) . '_settings');
delete_option('woocommerce_' . strtolower(Paykit_Payment_Method_Enum::BANK_TRANSFER) . '_settings');
