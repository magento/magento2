/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Weee/js/view/checkout/summary/item/price/weee'
    ],
    function (weee) {
        "use strict";
        return weee.extend({
            defaults: {
                template: 'Magento_Weee/checkout/summary/item/price/row_incl_tax',
                displayArea: 'row_incl_tax'
            },
            getFinalRowDisplayPriceInclTax: function(item) {
                var rowTotalInclTax = parseFloat(item.row_total_incl_tax);
                if(!window.checkoutConfig.getIncludeWeeeFlag) {

                    return rowTotalInclTax + this.getRowWeeeTaxInclTax(item);
                }
                return rowTotalInclTax;
            },

            getRowDisplayPriceInclTax: function(item) {
                var rowTotalInclTax = parseFloat(item.row_total_incl_tax);
                return rowTotalInclTax + this.getRowWeeeTaxInclTax(item);
            },

            getRowWeeeTaxInclTax: function(item) {
                var totalWeeeTaxIncTaxApplied = 0;
                if (item.weee_tax_applied) {
                    var weeeTaxAppliedAmounts = JSON.parse(item.weee_tax_applied);
                    weeeTaxAppliedAmounts.forEach(function (weeeTaxAppliedAmount) {
                        totalWeeeTaxIncTaxApplied += parseFloat(Math.max(weeeTaxAppliedAmount.row_amount_incl_tax, 0));
                    });
                }
                return totalWeeeTaxIncTaxApplied;
            }

        });
    }
);
