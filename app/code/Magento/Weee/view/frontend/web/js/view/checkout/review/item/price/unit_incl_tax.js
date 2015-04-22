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
    function (weee,$) {
        "use strict";
        return weee.extend({
            defaults: {
                template: 'Magento_Weee/checkout/review/item/price/unit_incl_tax',
                displayArea: 'unit_incl_tax'
            },

            getUnitDisplayPriceInclTax: function(item) {
                var unitInclTax = parseFloat(item.price_incl_tax);
                if (!window.checkoutConfig.isWeeeEnabled) {
                    return unitInclTax;
                }
                if(window.checkoutConfig.getIncludeWeeeFlag) {
                    return unitInclTax + this.getWeeeTaxInclTax(item);
                }
                return unitInclTax;
            },
            getFinalUnitDisplayPriceInclTax: function(item) {
                var unitInclTax = parseFloat(item.price_incl_tax);
                if (!window.checkoutConfig.isWeeeEnabled) {
                    return unitInclTax;
                }
                return unitInclTax + this.getWeeeTaxInclTax(item);
            },
            getItemId: function(item) {
                return item.item_id;
            },
            getWeeeTaxInclTax: function(item) {
                var weeeTaxAppliedAmounts = item.weee_tax_applied;
                var totalWeeeTaxIncTaxApplied = 0;
                $.each(weeeTaxAppliedAmounts, function (key, weeeTaxAppliedAmount) {
                    totalWeeeTaxIncTaxApplied+=parseFloat(Math.max(weeeTaxAppliedAmount.amount_incl_tax, 0));
                });
                return totalWeeeTaxIncTaxApplied;
            }
        });
    }
);
