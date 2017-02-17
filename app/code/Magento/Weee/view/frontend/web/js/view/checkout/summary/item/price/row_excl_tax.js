/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Weee/js/view/checkout/summary/item/price/weee'
], function (weee) {
    'use strict';

    return weee.extend({
        defaults: {
            template: 'Magento_Weee/checkout/summary/item/price/row_excl_tax'
        },

        /**
         * @param {Object} item
         * @return {Number}
         */
        getFinalRowDisplayPriceExclTax: function (item) {
            var rowTotalExclTax = parseFloat(item['row_total']);

            if (!window.checkoutConfig.getIncludeWeeeFlag) {
                rowTotalExclTax += parseFloat(item['weee_tax_applied_amount']);
            }

            return rowTotalExclTax;
        },

        /**
         * @param {Object} item
         * @return {Number}
         */
        getRowDisplayPriceExclTax: function (item) {
            var rowTotalExclTax = parseFloat(item['row_total']);

            if (window.checkoutConfig.getIncludeWeeeFlag) {
                rowTotalExclTax += this.getRowWeeeTaxExclTax(item);
            }

            return rowTotalExclTax;
        },

        /**
         * @param {Object} item
         * @return {Number}
         */
        getRowWeeeTaxExclTax: function (item) {
            var totalWeeeTaxExclTaxApplied = 0,
                weeeTaxAppliedAmounts;

            if (item['weee_tax_applied']) {
                weeeTaxAppliedAmounts = JSON.parse(item['weee_tax_applied']);
                weeeTaxAppliedAmounts.forEach(function (weeeTaxAppliedAmount) {
                    totalWeeeTaxExclTaxApplied += parseFloat(Math.max(weeeTaxAppliedAmount['row_amount'], 0));
                });
            }

            return totalWeeeTaxExclTaxApplied;
        }

    });
});
