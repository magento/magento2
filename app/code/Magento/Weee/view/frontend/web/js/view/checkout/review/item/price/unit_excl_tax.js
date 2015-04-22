/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Weee/js/view/checkout/review/item/price/weee',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils'
    ],
    function (weee,quote, priceUtils) {
        "use strict";
        return weee.extend({
            defaults: {
                template: 'Magento_Weee/checkout/review/item/price/unit_excl_tax',
                displayArea: 'unit_excl_tax'
            },

            getUnitDisplayPriceExclTax: function(item) {
                var unitExclTax = parseFloat(item.price);
                if (!window.checkoutConfig.isWeeeEnabled) {
                    return unitExclTax;
                }
                if(window.checkoutConfig.getIncludeWeeeFlag) {
                    return unitExclTax + parseFloat(item.weee_tax_applied_amount);
                }
                return unitExclTax;
            },
            getFinalUnitDisplayPriceExclTax: function(item) {
                var unitExclTax = parseFloat(item.price);
                if (!window.checkoutConfig.isWeeeEnabled) {
                    return unitExclTax;
                }
                return unitExclTax + parseFloat(item.weee_tax_applied_amount);
            }

        });
    }
);
