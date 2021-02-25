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
         * @param {*} amount
         * @return {*|String}
         */
        getFormattedPercent: function (amount) {
            var format = Object.assign({}, quote.getPriceFormat());

            format.requiredPrecision = this.countPrecision(amount, format.decimalSymbol);
            format.pattern = '%s';

            return priceUtils.formatPrice(amount, format, false);
        },

        /**
         * @param {*} amount
         * @param {*} decimalSymbol
         * @return {*|Number}
         */
        countPrecision: function (amount, decimalSymbol) {
            var decimalValue = amount.split(decimalSymbol);

            return decimalValue[1].length;
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
