<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/enums/setting-key.php';

// Add Paykit payment gateway settings tab
function paykit_filter_woocommerce_settings_tabs_array($settings_tabs)
{
  $settings_tabs['paykit-pgw'] = __('Paykit payment gateway', 'paykit-payment-gateway');

  return $settings_tabs;
}
add_filter('woocommerce_settings_tabs_array', 'paykit_filter_woocommerce_settings_tabs_array', 99);


// All Paykit payment gateway setting fields
function paykit_get_settings()
{
  $settings = array();
  $settings[] = array(
    'name' => __('Paykit payment gateway Settings', 'paykit-payment-gateway'),
    'type' => 'title',
    'desc' => __('You need to set these settings before using Paykit payment gateway.', 'paykit-payment-gateway'),
    'id'   => 'wc_paykit_pgw_settings_title'
  );
  $settings[] = array(
    'name'     => __('API URL', 'paykit-payment-gateway'),
    'id'       => Paykit_Setting_Key_Enum::BASE_URL,
    'type'     => 'text',
    'desc'     => __('Paykit payment gateway Endpoint to access payment gateway service', 'paykit-payment-gateway')
  );
  $settings[] = array(
    'name'     => __('API key', 'paykit-payment-gateway'),
    'id'       => Paykit_Setting_Key_Enum::API_KEY,
    'type'     => 'text',
    'desc'     => __('API key provided by Paykit', 'paykit-payment-gateway')
  );
  $settings[] = array(
    'name'     => __('Secret key', 'paykit-payment-gateway'),
    'id'       => Paykit_Setting_Key_Enum::SECRET_KEY,
    'type'     => 'password',
    'desc'     => __('Secret key of the client', 'paykit-payment-gateway'),
    'class'    => 'paykit-input-hide-show-password'
  );
  $settings[] = array(
    'name'     => __('Webhook Secret key', 'paykit-payment-gateway'),
    'desc_tip' => __('Secret key is used to validate Webhook requests from Paykit', 'paykit-payment-gateway'),
    'id'       => Paykit_Setting_Key_Enum::IPN_SECRET_KEY,
    'type'     => 'password',
    'desc'     => __('Webhook secret key provided by Paykit', 'paykit-payment-gateway'),
    'class'    => 'paykit-input-hide-show-password'
  );
  $settings[] = array(
    'name'     => __('Currency conversion', 'paykit-payment-gateway'),
    'desc_tip' => __('Currently Paykit only supports VND, need to configure how to convert other currencies', 'paykit-payment-gateway'),
    'id'       => Paykit_Setting_Key_Enum::CURRENCY_CONVERSION,
    'type'     => 'paykit-currency-conversion-input-type',
    'default'  => array()
  );
  $settings[] = array('type' => 'sectionend', 'id' => 'wc_paykit_pgw_settings_options');

  return $settings;
}
// Specify how to display Currency conversion field
add_action('woocommerce_admin_field_paykit-currency-conversion-input-type', 'paykit_display_currency_conversion_input', 10);
function paykit_display_currency_conversion_input($field)
{
  $option_value = $field['value'] ?? array();
  $option_name = $field['field_name'];
  $currencies = get_woocommerce_currencies();

  // Custom attribute handling
  $custom_attributes = array();
  if (!empty($field['custom_attributes']) && is_array($field['custom_attributes'])) {
    foreach ($field['custom_attributes'] as $attribute => $attribute_value) {
      $custom_attributes[] = esc_attr($attribute) . '="' . esc_attr($attribute_value) . '"';
    }
  }

  // Description handling
  $field_description = WC_Admin_Settings::get_field_description($field);
  $tooltip_html      = $field_description['tooltip_html'];

  // Display field
?>
  <tr class="<?php echo esc_attr($field['row_class']); ?>">
    <th scope="row" class="titledesc">
      <label for="<?php echo esc_attr($field['id']); ?>">
        <?php echo esc_html($field['title']); ?>
        <?php echo wp_kses($tooltip_html, array('span' => array('class' => array(), 'tabindex' => array(), 'aria-label' => array(), 'data-tip' => array()))); ?>
      </label>
    </th>
    <td id="paykit-pgw-currency-conversion-container" class="forminp forminp-<?php echo esc_attr(sanitize_title($field['type'])); ?>">
      <?php
      foreach ($option_value as $index => $value) {
      ?>
        <div class="paykit-pgw-currency-conversion-item" style="display: flex; align-items: center; gap: 4px; margin-bottom: 8px;">
          <b>1</b>
          <select name="<?php echo esc_attr($option_name) ?>[<?php echo esc_attr($index) ?>][currency]" style="width: 200px">
            <?php
            foreach ($currencies as $currency_code => $currency_text) {
            ?>
              <option value="<?php echo esc_attr($currency_code) ?>" <?php if ($value['currency'] == $currency_code) echo 'selected' ?>>
                <?php echo esc_attr($currency_code) ?> - <?php echo esc_attr($currency_text) ?>
              </option>
            <?php } ?>
          </select>
          <b>=</b>
          <input type="number" name="<?php echo esc_attr($option_name) ?>[<?php echo esc_attr($index) ?>][conversion_rate]" value="<?php echo esc_attr($value['conversion_rate']) ?>" style="width: 200px" />
          <b>VND</b>
          <span class="paykit-pgw-currency-conversion-remove-item" style="margin-left: 10px; cursor: pointer;">✕</span>
        </div>
      <?php
      }
      ?>

      <span id="paykit-pgw-currency-conversion-add-item" class="button" style="margin-top: 4px; width: 460px; text-align: center;">╋</span>
    </td>
  </tr>
  <?php

  // Add script to add new field and remove field
  ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const addFieldButton = document.getElementById('paykit-pgw-currency-conversion-add-item');
      addFieldButton.addEventListener('click', () => {
        const container = document.getElementById('paykit-pgw-currency-conversion-container');
        const itemCount = container.querySelectorAll('.paykit-pgw-currency-conversion-item').length;

        const newItem = document.createElement('div');
        newItem.classList.add('paykit-pgw-currency-conversion-item');
        newItem.style.cssText = 'display: flex; align-items: center; gap: 4px; margin-bottom: 8px;';
        newItem.innerHTML = `
        <b>1</b>
        <select name="<?php echo esc_attr($option_name) ?>[${itemCount}][currency]" style="width: 200px">
          <?php
          foreach ($currencies as $currency_code => $currency_text) {
          ?>
            <option value="<?php echo esc_attr($currency_code) ?>">
              <?php echo esc_attr($currency_code) ?> - <?php echo esc_attr($currency_text) ?>
            </option>
          <?php } ?>
        </select>
        <b>=</b>
        <input type="number" name="<?php echo esc_attr($option_name) ?>[${itemCount}][conversion_rate]" style="width: 200px" />
        <b>VND</b>
        <span class="paykit-pgw-currency-conversion-remove-item" style="margin-left: 10px; cursor: pointer;">✕</span>
        `;

        container.insertBefore(newItem, addFieldButton);
      });

      document.addEventListener('click', (event) => {
        if (event.target.classList.contains('paykit-pgw-currency-conversion-remove-item')) {
          event.target.parentNode.remove();
        }
      });
    });
  </script>
<?php
}
// Specify how to update Currency conversion field
$currency_conversion_field_id = Paykit_Setting_Key_Enum::CURRENCY_CONVERSION;
add_filter("woocommerce_admin_settings_sanitize_option_$currency_conversion_field_id", 'paykit_update_currency_conversion_input_value', 10, 3);
function paykit_update_currency_conversion_input_value($value, $field, $raw_value)
{
  $valid_currencies = get_woocommerce_currencies();
  $valid_item = array();
  $exist_currencies = array();
  foreach ($value as $index => $item) {
    $currency = $item['currency'];
    $conversion_rate = $item['conversion_rate'];

    if (!$currency || !$conversion_rate) continue;
    if ($currency === 'VND') continue;
    if (!array_key_exists($currency, $valid_currencies)) continue;
    if (in_array($currency, $exist_currencies)) continue;

    $exist_currencies[] = $currency;
    $valid_item[] = array(
      'currency' => $currency,
      'conversion_rate' => $conversion_rate
    );
  }
  return $valid_item;
}


// Display Paykit payment gateway settings form
add_action('woocommerce_settings_paykit-pgw', 'paykit_display_settings', 10);
function paykit_display_settings()
{
  WC_Admin_Settings::output_fields(paykit_get_settings());
}


// On save Paykit payment gateway settings
add_action('woocommerce_settings_save_paykit-pgw', 'paykit_save_settings', 10);
function paykit_save_settings()
{
  WC_Admin_Settings::save_fields(paykit_get_settings());
}
