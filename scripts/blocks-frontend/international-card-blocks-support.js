import { registerPaymentMethod } from "@woocommerce/blocks-registry";
import { getSetting } from "@woocommerce/settings";
import { decodeEntities } from "@wordpress/html-entities";
import { __ } from "@wordpress/i18n";

const settings = getSetting("international_card_data", {});
const name = settings.name;

const defaultLabel = __(
  "Pay by Visa/Mastercard/JCB card",
  "paykit-payment-gateway"
);

const label = decodeEntities(settings.title) || defaultLabel;

/**
 * Content component
 */
const Content = () => {
  const description = decodeEntities(settings.description || "");
  const note_html = decodeEntities(settings.note_html || "");

  return (
    <div>
      <div>{description}</div>
      <div dangerouslySetInnerHTML={{ __html: note_html }} />
    </div>
  );
};

/**
 * Label component
 *
 * @param {*} props Props from payment API.
 */
const Label = (props) => {
  const { PaymentMethodLabel } = props.components;
  const iconUrl = settings.icon_url;
  return (
    <div>
      <PaymentMethodLabel text={label} />
      <img
        src={iconUrl}
        style={{
          "margin-left": "5px",
          "max-width": "200px",
          "max-height": "30px"
        }}
        alt="International card icon"
      />
    </div>
  );
};

/**
 * International card payment method config object.
 */
const InternationalCard = {
  name: name,
  label: <Label />,
  content: <Content />,
  edit: <Content />,
  canMakePayment: () => true,
  ariaLabel: label,
  supports: {
    features: settings.supports
  }
};

registerPaymentMethod(InternationalCard);
