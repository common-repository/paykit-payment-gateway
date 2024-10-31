<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/enums/payment-method.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/clients/paykit-pgw-client.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/clients/dtos/payment-page-checkout.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/src/base/base-wc-paykit-pgw.php';

add_filter('woocommerce_payment_gateways', 'paykit_add_bank_transfer_to_wc');

function paykit_add_bank_transfer_to_wc($gateways)
{
  $gateways[] = 'Paykit_Bank_Transfer_WC_Payment_Gateway';
  return $gateways;
}

class Paykit_Bank_Transfer_WC_Payment_Gateway extends Paykit_Base_WC_Payment_Gateway
{
  private $default_icon;

  public function __construct()
  {
    parent::__construct();

    // Setup general properties.
    $this->setup_properties();

    // Load the settings.
    $this->init_form_fields();
    $this->init_settings();

    // Get settings.
    $this->title = $this->get_option('title');
    $this->description = $this->get_option('description');

    // Not support refunds
    if (($key = array_search('refunds', $this->supports)) !== false) {
      unset($this->supports[$key]);
    }

    add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
  }

  /**
   * Setup general properties for the gateway.
   */
  protected function setup_properties()
  {
    $this->id                 = strtolower(Paykit_Payment_Method_Enum::BANK_TRANSFER);
    $this->default_icon       = WC_HTTPS::force_https_url(plugins_url('/paykit-payment-gateway/assets/icon/bank-transfer.png'));
    $this->icon               = $this->get_option('icon') ?? $this->default_icon;
    $this->method_title       = __('Paykit payment gateway - Bank transfer VietQR', 'paykit-payment-gateway');
    $this->method_description = __('Your customers can pay by bank transfer via Paykit payment gateway.', 'paykit-payment-gateway');
    $this->has_fields         = false;
  }

  /**
   * Return the gateway's icon.
   *
   * @return string
   */
  public function get_icon()
  {
    $icon = $this->icon ? '<img style="max-width: 200px; max-height: 30px;" src="' . $this->icon . '" alt="' . esc_attr($this->get_title()) . '" />' : '';

    return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
  }

  /**
   * Initialise Gateway Settings Form Fields.
   */
  public function init_form_fields()
  {
    $this->form_fields = array(
      'enabled'            => array(
        'title'       => __('Enable/Disable', 'paykit-payment-gateway'),
        'label'       => __('Enable Paykit payment gateway - Bank transfer VietQR', 'paykit-payment-gateway'),
        'type'        => 'checkbox',
        'description' => '',
        'default'     => 'no',
      ),
      'title'              => array(
        'title'       => __('Title', 'paykit-payment-gateway'),
        'type'        => 'text',
        'description' => __('Paykit payment gateway - Bank transfer VietQR title that the customer will see on your checkout.', 'paykit-payment-gateway'),
        'default'     => __('Pay by bank transfer', 'paykit-payment-gateway'),
        'desc_tip'    => true,
      ),
      'description'        => array(
        'title'       => __('Description', 'paykit-payment-gateway'),
        'type'        => 'textarea',
        'description' => __('Paykit payment gateway - Bank transfer VietQR description that the customer will see on your website.', 'paykit-payment-gateway'),
        'default'     => __('Transfer from bank app and e-wallet', 'paykit-payment-gateway'),
        'desc_tip'    => true
      ),
      'icon' => array(
        'title'       => __('Icon', 'paykit-payment-gateway'),
        'type'        => 'image',
        'default'     => $this->default_icon
      )
    );
  }
}
