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
                template: 'Magento_Weee/checkout/review/item/price/row_excl_tax',
                displayArea: 'row_excl_tax'
            },

            getRowDisplayPriceExclTax: function(item) {
                var rowTotalExclTax = parseFloat(item.row_total);
                if (!window.checkoutConfig.isWeeeEnabled) {
                    return rowTotalExclTax;
                }
                if(window.checkoutConfig.getIncludeWeeeFlag) {
                    return rowTotalExclTax + parseFloat(item.weee_tax_applied_amount);
                }
                return rowTotalExclTax;
            },
            getFinalRowDisplayPriceExclTax: function(item) {
                var rowTotalExclTax = parseFloat(item.row_total);
                if (!window.checkoutConfig.isWeeeEnabled) {
                    return rowTotalExclTax;
                }
                return rowTotalExclTax + parseFloat(item.weee_tax_applied_amount);
            }

        });
    }
);
