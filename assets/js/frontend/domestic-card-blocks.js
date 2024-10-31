(() => {
  "use strict";
  const e = window.React,
    t = window.wc.wcBlocksRegistry,
    n = window.wc.wcSettings,
    i = window.wp.htmlEntities,
    c = window.wp.i18n,
    a = (0, n.getSetting)("domestic_card_data", {}),
    l = a.name,
    r = (0, c.__)("Pay by NAPAS card", "paykit-payment-gateway"),
    o = (0, i.decodeEntities)(a.title) || r,
    d = () => {
      const t = (0, i.decodeEntities)(a.description || ""),
        n = (0, i.decodeEntities)(a.note_html || "");
      return (0, e.createElement)(
        "div",
        null,
        (0, e.createElement)("div", null, t),
        (0, e.createElement)("div", { dangerouslySetInnerHTML: { __html: n } })
      );
    },
    m = {
      name: l,
      label: (0, e.createElement)((t) => {
        const { PaymentMethodLabel: n } = t.components,
          i = a.icon_url;
        return (0, e.createElement)(
          "div",
          null,
          (0, e.createElement)(n, { text: o }),
          (0, e.createElement)("img", {
            src: i,
            style: {
              "margin-left": "5px",
              "max-width": "200px",
              "max-height": "30px"
            },
            alt: "Domestic card icon"
          })
        );
      }, null),
      content: (0, e.createElement)(d, null),
      edit: (0, e.createElement)(d, null),
      canMakePayment: () => !0,
      ariaLabel: o,
      supports: { features: a.supports }
    };
  (0, t.registerPaymentMethod)(m);
})();
