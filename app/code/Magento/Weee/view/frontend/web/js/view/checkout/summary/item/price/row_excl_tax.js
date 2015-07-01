/**
 * Copyright © 2015 Magento. All rights reserved.
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
                template: 'Magento_Weee/checkout/summary/item/price/row_excl_tax'
            },

            getFinalRowDisplayPriceExclTax: function(item) {
                var rowTotalExclTax = parseFloat(item.row_total);
                if(!window.checkoutConfig.getIncludeWeeeFlag) {
                    return rowTotalExclTax + parseFloat(item.weee_tax_applied_amount);
                }
                return rowTotalExclTax
            }

        });
    }
);
