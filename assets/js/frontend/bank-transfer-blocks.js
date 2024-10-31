(() => {
  "use strict";
  const e = window.React,
    t = window.wc.wcBlocksRegistry,
    n = window.wc.wcSettings,
    a = window.wp.htmlEntities,
    i = window.wp.i18n,
    l = (0, n.getSetting)("bank_transfer_data", {}),
    r = l.name,
    c = (0, i.__)("Pay by bank transfer", "paykit-payment-gateway"),
    o = (0, a.decodeEntities)(l.title) || c,
    s = () => {
      const t = (0, a.decodeEntities)(l.description || ""),
        n = (0, a.decodeEntities)(l.note_html || "");
      return (0, e.createElement)(
        "div",
        null,
        (0, e.createElement)("div", null, t),
        (0, e.createElement)("div", { dangerouslySetInnerHTML: { __html: n } })
      );
    },
    m = {
      name: r,
      label: (0, e.createElement)((t) => {
        const { PaymentMethodLabel: n } = t.components,
          a = l.icon_url;
        return (0, e.createElement)(
          "div",
          null,
          (0, e.createElement)(n, { text: o }),
          (0, e.createElement)("img", {
            src: a,
            style: {
              "margin-left": "5px",
              "max-width": "200px",
              "max-height": "30px"
            },
            alt: "Bank transfer icon"
          })
        );
      }, null),
      content: (0, e.createElement)(s, null),
      edit: (0, e.createElement)(s, null),
      canMakePayment: () => !0,
      ariaLabel: o,
      supports: { features: l.supports }
    };
  (0, t.registerPaymentMethod)(m);
})();
