const defaultConfig = require("@wordpress/scripts/config/webpack.config");
const WooCommerceDependencyExtractionWebpackPlugin = require("@woocommerce/dependency-extraction-webpack-plugin");
const path = require("path");

const wcDepMap = {
  "@woocommerce/blocks-registry": ["wc", "wcBlocksRegistry"],
  "@woocommerce/settings": ["wc", "wcSettings"]
};

const wcHandleMap = {
  "@woocommerce/blocks-registry": "wc-blocks-registry",
  "@woocommerce/settings": "wc-settings"
};

const requestToExternal = (request) => {
  if (wcDepMap[request]) {
    return wcDepMap[request];
  }
};

const requestToHandle = (request) => {
  if (wcHandleMap[request]) {
    return wcHandleMap[request];
  }
};

// Export configuration.
module.exports = {
  ...defaultConfig,
  entry: {
    "frontend/domestic-card-blocks":
      "/scripts/blocks-frontend/domestic-card-blocks-support.js",
    "frontend/international-card-blocks":
      "/scripts/blocks-frontend/international-card-blocks-support.js",
    "frontend/bank-transfer-blocks":
      "/scripts/blocks-frontend/bank-transfer-blocks-support.js"
  },
  output: {
    path: path.resolve(__dirname, "assets/js"),
    filename: "[name].js"
  },
  plugins: [
    ...defaultConfig.plugins.filter(
      (plugin) =>
        plugin.constructor.name !== "DependencyExtractionWebpackPlugin"
    ),
    new WooCommerceDependencyExtractionWebpackPlugin({
      requestToExternal,
      requestToHandle
    })
  ]
};
