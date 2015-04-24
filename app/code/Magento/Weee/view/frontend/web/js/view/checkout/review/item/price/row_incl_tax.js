/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Weee/js/view/checkout/review/item/price/weee',
        'jquery'
    ],
    function (weee, $) {
        "use strict";
        return weee.extend({
            defaults: {
                template: 'Magento_Weee/checkout/review/item/price/row_incl_tax',
                displayArea: 'row_incl_tax'
            },

            getRowDisplayPriceInclTax: function(item) {
                var rowTotalInclTax = parseFloat(item.row_total_incl_tax);
                if (!window.checkoutConfig.isWeeeEnabled) {
                    return rowTotalInclTax;
                }
                if(window.checkoutConfig.getIncludeWeeeFlag) {

                    return rowTotalInclTax + this.getRowWeeeTaxInclTax(item);
                }
                return rowTotalInclTax;
            },
            getFinalRowDisplayPriceInclTax: function(item) {
                var rowTotalInclTax = parseFloat(item.row_total_incl_tax);
                if (!window.checkoutConfig.isWeeeEnabled) {
                    return rowTotalInclTax;
                }
                return rowTotalInclTax + this.getRowWeeeTaxInclTax(item);
            },

            getRowWeeeTaxInclTax: function(item) {
                var weeeTaxAppliedAmounts = item.weee_tax_applied;
                var totalWeeeTaxIncTaxApplied = 0;
                $.each(weeeTaxAppliedAmounts, function (key, weeeTaxAppliedAmount) {
                    totalWeeeTaxIncTaxApplied+=parseFloat(Math.max(weeeTaxAppliedAmount.row_amount_incl_tax, 0));
                });
                return totalWeeeTaxIncTaxApplied;
            }

        });
    }
);
