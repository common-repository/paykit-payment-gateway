<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/src/domestic-card/domestic-card.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/src/international-card/international-card.php';
require_once PAYKIT_PAYMENT_GATEWAY_PLUGIN_PATH . '/common/services/checkout_page_service.php';


add_action('woocommerce_checkout_after_terms_and_conditions', 'paykit_woocommerce_checkout_after_terms_and_conditions', 10);
function paykit_woocommerce_checkout_after_terms_and_conditions()
{
  $note_html = Paykit_Checkout_Page_Service::get_current_cart_currency_note_html();

  if (strlen($note_html) > 0) {
?>
    <div id="paykit-currency-note" style="display: none;">
      <?php echo esc_html($note_html); ?>
    </div>
    <script>
      var radios = document.forms["checkout"].elements["payment_method"];
      for (var i = 0, max = radios.length; i < max; i++) {
        radios[i].onclick = function() {
          if (this.value === '<?php echo esc_html((new Paykit_Domestic_Card_WC_Payment_Gateway())->id); ?>' || this.value === '<?php echo esc_html((new Paykit_International_Card_WC_Payment_Gateway())->id); ?>') {
            document.getElementById('paykit-currency-note').style.display = 'block';
          } else {
            document.getElementById('paykit-currency-note').style.display = 'none';
          }
        }
      }
    </script>
<?php
  }
}
