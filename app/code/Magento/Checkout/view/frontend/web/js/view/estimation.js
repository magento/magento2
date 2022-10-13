/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/sidebar'
], function (Component, quote, priceUtils, totals, sidebarModel) {
    'use strict';

    return Component.extend({
        isLoading: totals.isLoading,

        /**
         * @return {Number}
         */
        getQuantity: function () {
            if (totals.totals()) {
                return parseFloat(totals.totals()['items_qty']);
            }

            return 0;
        },

        /**
         * @return {Number}
         */
        getPureValue: function () {
            if (totals.totals()) {
                return parseFloat(totals.getSegment('grand_total').value);
            }

            return 0;
        },

        /**
         * Show sidebar.
         */
        showSidebar: function () {
            sidebarModel.show();
        },

        /**
         * @param {*} price
         * @return {*|String}
         */
        getFormattedPrice: function (price) {
            return priceUtils.formatPriceLocale(price, quote.getPriceFormat());
        },

        /**
         * @return {*|String}
         */
        getValue: function () {
            return this.getFormattedPrice(this.getPureValue());
        }
    });
});
