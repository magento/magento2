/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/step-navigator'
], function (Component, quote, priceUtils, totals, stepNavigator) {
    'use strict';

    return Component.extend({
        /**
         * @param {*} price
         * @return {*|String}
         */
        getFormattedPrice: function (price) {
            return priceUtils.formatPrice(price, quote.getPriceFormat());
        },

        /**
         * @param {*} price
         * @param {*} precision
         * @return {*|String}
         */
        getFormattedPercent: function (price, precision) {
            var format = Object.assign({}, quote.getPriceFormat());

            format.requiredPrecision = precision;
            format.pattern = '%s';

            return priceUtils.formatPrice(price, format, false);
        },

        /**
         * @return {*}
         */
        getTotals: function () {
            return totals.totals();
        },

        /**
         * @return {*}
         */
        isFullMode: function () {
            if (!this.getTotals()) {
                return false;
            }

            return stepNavigator.isProcessed('shipping');
        }
    });
});
