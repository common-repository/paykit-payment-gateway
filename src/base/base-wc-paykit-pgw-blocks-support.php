<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/services/checkout_page_service.php';

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

abstract class Paykit_Base_WC_Blocks_Support extends AbstractPaymentMethodType
{

  protected Paykit_Base_WC_Payment_Gateway $gateway;

  /**
   * Url of blocks support script.
   */
  protected string $script_url = '';

  /**
   * Path to built of blocks support script.
   */
  protected string $script_asset_path = '';

  /**
   * Name must be set first in constructor
   */
  public function __construct(Paykit_Base_WC_Payment_Gateway $gateway)
  {
    $this->gateway = $gateway;
    $this->settings = get_option($gateway->get_option_key(), []);
    $this->name = $gateway->id;
  }

  /**
   * Initializes the payment method type ($settings, $gateway, $name, $script_url).
   */
  abstract public function initialize();

  /**
   * Returns if this payment method should be active. If false, the scripts will not be enqueued.
   *
   * @return boolean
   */
  public function is_active()
  {
    return $this->gateway->is_available();
  }

  /**
   * Returns an array of scripts/handles to be registered for this payment method.
   *
   * @return array
   */
  public function get_payment_method_script_handles()
  {
    $script_asset      = file_exists($this->script_asset_path)
      ? require($this->script_asset_path)
      : array(
        'dependencies' => array(),
        'version'      => '1.0.0'
      );
    $handle_name = 'wc-' . $this->name . '-payments-blocks';

    wp_register_script(
      $handle_name,
      $this->script_url,
      $script_asset['dependencies'],
      $script_asset['version'],
      true
    );

    if (function_exists('wp_set_script_translations')) {
      wp_set_script_translations($handle_name, 'woocommerce-gateway-' . $this->name, PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/languages/');
    }

    return [$handle_name];
  }

  /**
   * Returns an array of key=>value pairs of data made available to the payment methods script.
   *
   * @return array
   */
  public function get_payment_method_data()
  {
    return [
      'name' => $this->name,
      'title'       => $this->get_setting('title'),
      'description' => $this->get_setting('description'),
      'note_html'        => Paykit_Checkout_Page_Service::get_current_cart_currency_note_html(),
      'supports'    => array_filter($this->gateway->supports, [$this->gateway, 'supports']),
      'icon_url' => $this->gateway->icon
    ];
  }
}
