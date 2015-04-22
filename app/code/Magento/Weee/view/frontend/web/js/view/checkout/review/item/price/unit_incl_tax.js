/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Weee/js/view/checkout/review/item/price/weee'
    ],
    function (weee) {
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
                    return unitInclTax + parseFloat(item.weee_tax_incl_tax);
                }
                return unitInclTax;
            },
            getFinalUnitDisplayPriceInclTax: function(item) {
                var unitInclTax = parseFloat(item.price_incl_tax);
                if (!window.checkoutConfig.isWeeeEnabled) {
                    return unitInclTax;
                }
                return unitInclTax + parseFloat(item.weee_tax_incl_tax);
            },
            getItemId: function(item) {
                return item.item_id;
            }
        });
    }
);
