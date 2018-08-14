/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */

define([
    'Magento_Weee/js/view/checkout/summary/item/price/weee'
], function (weee) {
    'use strict';

    return weee.extend({
        defaults: {
            template: 'Magento_Weee/checkout/summary/item/price/row_incl_tax',
            displayArea: 'row_incl_tax'
        },

        /**
         * @param {Object} item
         * @return {Number}
         */
        getFinalRowDisplayPriceInclTax: function (item) {
            var rowTotalInclTax = parseFloat(item['row_total_incl_tax']);

            if (!window.checkoutConfig.getIncludeWeeeFlag) {
                rowTotalInclTax += this.getRowWeeeTaxInclTax(item);
            }

            return rowTotalInclTax;
        },

        /**
         * @param {Object} item
         * @return {Number}
         */
        getRowDisplayPriceInclTax: function (item) {
            var rowTotalInclTax = parseFloat(item['row_total_incl_tax']);

            if (window.checkoutConfig.getIncludeWeeeFlag) {
                rowTotalInclTax += this.getRowWeeeTaxInclTax(item);
            }

            return rowTotalInclTax;
        },

        /**
         * @param {Object}item
         * @return {Number}
         */
        getRowWeeeTaxInclTax: function (item) {
            var totalWeeeTaxInclTaxApplied = 0,
                weeeTaxAppliedAmounts;

            if (item['weee_tax_applied']) {
                weeeTaxAppliedAmounts = JSON.parse(item['weee_tax_applied']);
                weeeTaxAppliedAmounts.forEach(function (weeeTaxAppliedAmount) {
                    totalWeeeTaxInclTaxApplied += parseFloat(Math.max(weeeTaxAppliedAmount['row_amount_incl_tax'], 0));
                });
            }

            return totalWeeeTaxInclTaxApplied;
        }

    });
});
