/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */

define([
    'ko',
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/cart/totals-processor/default'
], function (ko, Component, quote, totals, totalsDefaultProcessor) {
    'use strict';

    var isTaxDisplayedInGrandTotal = window.checkoutConfig.includeTaxInGrandTotal,
        isFullTaxSummaryDisplayed = window.checkoutConfig.isFullTaxSummaryDisplayed,
        isZeroTaxDisplayed = window.checkoutConfig.isZeroTaxDisplayed;

    quote.shippingAddress.subscribe(function () {
        totalsDefaultProcessor.estimateTotals(quote.shippingAddress());
    });

    return Component.extend({
        defaults: {
            isTaxDisplayedInGrandTotal: isTaxDisplayedInGrandTotal,
            notCalculatedMessage: 'Not yet calculated',
            template: 'Magento_Tax/checkout/summary/tax'
        },
        totals: quote.getTotals(),
        isFullTaxSummaryDisplayed: isFullTaxSummaryDisplayed,

        /**
         * @return {Boolean}
         */
        ifShowValue: function () {
            if (this.isFullMode() && this.getPureValue() == 0) { //eslint-disable-line eqeqeq
                return isZeroTaxDisplayed;
            }

            return true;
        },

        /**
         * @return {Boolean}
         */
        ifShowDetails: function () {
            if (!this.isFullMode()) {
                return false;
            }

            return this.getPureValue() > 0 && isFullTaxSummaryDisplayed;
        },

        /**
         * @return {Number}
         */
        getPureValue: function () {
            var amount = 0,
                taxTotal;

            if (this.totals()) {
                taxTotal = totals.getSegment('tax');

                if (taxTotal) {
                    amount = taxTotal.value;
                }
            }

            return amount;
        },

        /**
         * @return {*|Boolean}
         */
        isCalculated: function () {
            return this.totals() && this.isFullMode() && totals.getSegment('tax') != null;
        },

        /**
         * @return {*}
         */
        getValue: function () {
            var amount;

            if (!this.isCalculated()) {
                return this.notCalculatedMessage;
            }
            amount = totals.getSegment('tax').value;

            return this.getFormattedPrice(amount);
        },

        /**
         * @param {*} amount
         * @return {*|String}
         */
        formatPrice: function (amount) {
            return this.getFormattedPrice(amount);
        },

        /**
         * @return {Array}
         */
        getDetails: function () {
            var taxSegment = totals.getSegment('tax');

            if (taxSegment && taxSegment['extension_attributes']) {
                return taxSegment['extension_attributes']['tax_grandtotal_details'];
            }

            return [];
        }
    });
});
