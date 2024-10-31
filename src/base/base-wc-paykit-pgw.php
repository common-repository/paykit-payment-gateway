<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/enums/payment-method.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/enums/language.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/clients/paykit-pgw-client.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/clients/dtos/payment-page-checkout.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/utils/amount.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/utils/currency.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/enums/order-meta-key.php';

abstract class Paykit_Base_WC_Payment_Gateway extends WC_Payment_Gateway
{
  private $paykit_pgw_client;

  public function __construct()
  {
    $this->paykit_pgw_client = new Paykit_PGW_Client();
  }

  public function process_payment($order_id)
  {
    $order = wc_get_order($order_id);
    $order_total_in_vnd = paykit_get_amount_in_vnd($order->get_total(), $order->get_currency());

    if (!$order || !$order_total_in_vnd || $order_total_in_vnd <= 0) return array();

    $order->update_status('on-hold');

    // Save currency conversion rate
    if ($order->get_currency() !== 'VND') {
      $currency_conversions = get_option(Paykit_Setting_Key_Enum::CURRENCY_CONVERSION);
      if ($currency_conversions) {
        foreach ($currency_conversions as $index => $currency_conversion) {
          if ($currency_conversion['currency'] === $order->get_currency()) {
            $order->add_meta_data(Paykit_Order_Meta_Key_Enum::CURRENCY_CONVERSION_RATE, $currency_conversion['conversion_rate'], true);
            $order->save();
          }
        }
      }
    }

    $return_url = $this->get_return_url($order);
    $display_language = $this->get_display_language();
    try {
      $data = $this->paykit_pgw_client->payment_page_checkout(
        strtoupper($this->id),
        $order->get_id(),
        $order_total_in_vnd,
        'VND',
        null,
        null,
        $return_url,
        $return_url,
        get_rest_url(null, "paykit-pgw/webhook"),
        $display_language
      );
    } catch (Throwable $e) {
      error_log($e->getMessage());
      $order->update_status('failed', 'Server error: ' . $e->getMessage());
      return array(
        'result' => 'success',
        'redirect' => $return_url,
      );
    }

    if ($data->result !== Paykit_Result_Enum::SUCCESS) {
      $order->update_status('failed', 'Call Paykit payment gateway failed: ' . $data->result);
      return array(
        'result' => 'success',
        'redirect' => $return_url,
      );
    }

    return array(
      'result' => 'success',
      'redirect' => $data->checkout_url,
    );
  }

  public function process_refund($order_id, $amount = null, $reason = '')
  {
    $order = wc_get_order($order_id);

    $currency_conversion_rate = $order->get_meta(Paykit_Order_Meta_Key_Enum::CURRENCY_CONVERSION_RATE);
    if (!$currency_conversion_rate) $currency_conversion_rate = 1;

    if (!$amount || $amount <= 0) {
      return new WP_Error('paykit_pgw_refund_invalid_amount', __('Invalid amount to refund', 'paykit-payment-gateway'));
    }

    if (!$order || !in_array($order->get_status(), array('processing', 'completed'))) {
      return new WP_Error('paykit_pgw_refund_invalid_order', __('Invalid order to refund', 'paykit-payment-gateway'));
    }

    $refund_id = end(explode(' ###wc_paykit_pgw_refund', $reason));
    $refund = wc_get_order($refund_id);
    if (!$refund) {
      return new WP_Error('paykit_pgw_refund_error', __('Error. Please try again!', 'paykit-payment-gateway'));
    }

    // Convert amount to VND
    $refund_amount_in_vnd = ceil($amount);
    if ($order->get_currency() !== 'VND') {
      $refund_amount_in_vnd = ceil($amount * $currency_conversion_rate);
    }

    try {
      $data = $this->paykit_pgw_client->refund(
        $order->get_id(),
        $refund_id,
        $refund_amount_in_vnd,
        'VND',
      );
    } catch (Throwable $e) {
      error_log($e->getMessage());
      return new WP_Error('paykit_pgw_refund_error', __('Error when refunding: ', 'paykit-payment-gateway') . $e->getMessage());
    }

    $refund->update_meta_data('paykit_pgw_status', $data->refund->status);
    $refund->save();
    if ($data->refund->status === Paykit_Refund_Status_Enum::PROCESSING) {
      $order->add_order_note(
        sprintf(
          /* translators: %1$s: refund ID, %2$s: refund amount in VND */
          __('Gateway refund is being processed (ID: %1$s, Amount: %2$s VND)', 'paykit-payment-gateway'),
          $refund_id,
          $refund_amount_in_vnd
        )
      );
      return true;
    } else if ($data->refund->result === Paykit_Refund_Result_Enum::APPROVED) {
      $order->add_order_note(
        sprintf(
          /* translators: %1$s: refund ID, %2$s: refund amount in VND */
          __('Gateway refund approved (ID: %1$s, Amount: %2$s VND)', 'paykit-payment-gateway'),
          $refund_id,
          $refund_amount_in_vnd
        )
      );
      return true;
    }

    return new WP_Error('paykit_pgw_refund_failed', __('Refund failed. Please try again!', 'paykit-payment-gateway'));
  }

  // Generate HTML code for admin settings with "image" field type
  public function generate_image_html($key, $data)
  {
    $field_key = $this->get_field_key($key);
    $defaults  = array(
      'title'             => '',
      'disabled'          => false,
      'class'             => '',
      'css'               => '',
      'placeholder'       => '',
      'type'              => 'text',
      'desc_tip'          => false,
      'description'       => '',
      'custom_attributes' => array(),
    );

    $data  = wp_parse_args($data, $defaults);

    wp_enqueue_media();
    ob_start();
?>
    <tr valign="top">
      <th scope="row" class="titledesc">
        <label for="<?php echo esc_attr($field_key); ?>"><?php echo wp_kses_post($data['title']); ?></label>
      </th>
      <td class="forminp" style="display: flex; align-items: center; gap: 10px;">
        <img id="image-<?php echo esc_attr($field_key); ?>" style="max-width: 200px; max-height: 50px;" src="<?php echo esc_attr($this->get_option($key)); ?>">
        <input style="display: none;" type="text" id="<?php echo esc_attr($field_key); ?>" name="<?php echo esc_attr($field_key); ?>" value="<?php echo esc_attr($this->get_option($key)); ?>" />
        <button type="button" class="button" id="upload_image_button">Chọn ảnh</button>
      </td>
    </tr>
    <?php

    ?>
    <script>
      jQuery(document).ready(function($) {
        var image_uploader = wp.media({
          title: 'Chọn ảnh',
          button: {
            text: 'Chọn ảnh'
          },
          library: {
            type: ['image']
          },
          multiple: false // Cho phép chọn nhiều ảnh hay không
        });

        $('#upload_image_button').click(function(e) {
          e.preventDefault();

          // Mở popup media uploader
          image_uploader.open();
        });

        // Lấy URL ảnh khi chọn ảnh
        image_uploader.on('select', function() {
          var image = image_uploader.state().get('selection').first().toJSON();
          var image_url = image.url;

          // Hiển thị ảnh đã chọn
          $('#<?php echo esc_attr($field_key); ?>').val(image_url);
          $('#image-<?php echo esc_attr($field_key); ?>').attr('src', image_url);
        });
      });
    </script>
<?php

    return ob_get_clean();
  }

  private function get_display_language()
  {
    if (!get_locale()) {
      return null;
    }

    $wp_locale = substr(get_locale(), 0, 2);
    if (in_array($wp_locale, Paykit_Language_Enum::values())) {
      return $wp_locale;
    } else {
      return Paykit_Language_Enum::EN;
    }
  }
}
