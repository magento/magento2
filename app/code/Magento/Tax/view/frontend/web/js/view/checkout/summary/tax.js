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
    'mage/translate',
    'underscore'
], function (ko, Component, quote, totals, $t, _) {
    'use strict';

    var isTaxDisplayedInGrandTotal = window.checkoutConfig.includeTaxInGrandTotal,
        isFullTaxSummaryDisplayed = window.checkoutConfig.isFullTaxSummaryDisplayed,
        isZeroTaxDisplayed = window.checkoutConfig.isZeroTaxDisplayed,
        taxAmount = 0,
        rates = 0;

    return Component.extend({
        defaults: {
            isTaxDisplayedInGrandTotal: isTaxDisplayedInGrandTotal,
            notCalculatedMessage: $t('Not yet calculated'),
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
         * @param {*} parent
         * @param {*} percentage
         * @return {*|String}
         */
        getTaxAmount: function (parent, percentage) {
            var totalPercentage = 0;

            taxAmount = parent.amount;
            rates = parent.rates;
            _.each(rates, function (rate) {
                totalPercentage += parseFloat(rate.percent);
            });

            return this.getFormattedPrice(this.getPercentAmount(taxAmount, totalPercentage, percentage));
        },

        /**
         * @param {*} amount
         * @param {*} totalPercentage
         * @param {*} percentage
         * @return {*|String}
         */
        getPercentAmount: function (amount, totalPercentage, percentage) {
            return parseFloat(amount * percentage / totalPercentage);
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
