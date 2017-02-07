/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals'
], function (Component, quote, priceUtils, totals) {
    'use strict';

    return Component.extend({
        defaults: {
            isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
            template: 'Magento_Tax/checkout/summary/grand-total'
        },
        totals: quote.getTotals(),
        isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,

        /**
         * @return {*}
         */
        isDisplayed: function () {
            return this.isFullMode();
        },

        /**
         * @return {*|String}
         */
        getValue: function () {
            var price = 0;

            if (this.totals()) {
                price = totals.getSegment('grand_total').value;
            }

            return this.getFormattedPrice(price);
        },

        /**
         * @return {*|String}
         */
        getBaseValue: function () {
            var price = 0;

            if (this.totals()) {
                price = this.totals()['base_grand_total'];
            }

            return priceUtils.formatPrice(price, quote.getBasePriceFormat());
        },

        /**
         * @return {*}
         */
        getGrandTotalExclTax: function () {
            var total = this.totals();

            if (!total) {
                return 0;
            }

            return this.getFormattedPrice(totals['grand_total']);
        },

        /**
         * @return {Boolean}
         */
        isBaseGrandTotalDisplayNeeded: function () {
            var total = this.totals();

            if (!total) {
                return false;
            }

            return totals['base_currency_code'] != totals['quote_currency_code']; //eslint-disable-line eqeqeq
        }
    });
});
