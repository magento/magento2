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
                template: 'Magento_Weee/checkout/review/item/price/row_incl_tax',
                displayArea: 'row_incl_tax'
            },

            getRowDisplayPriceInclTax: function(item) {
                var rowTotalInclTax = parseFloat(item.row_total_incl_tax);
                if (!window.checkoutConfig.isWeeeEnabled) {
                    return rowTotalInclTax;
                }
                if(window.checkoutConfig.getIncludeWeeeFlag) {
                    return rowTotalInclTax + parseFloat(item.weee_tax_applied_row_amount);
                }
                return rowTotalInclTax;
            },
            getFinalRowDisplayPriceInclTax: function(item) {
                var rowTotalInclTax = parseFloat(item.row_total_incl_tax);
                if (!window.checkoutConfig.isWeeeEnabled) {
                    return rowTotalInclTax;
                }
                return rowTotalInclTax + parseFloat(item.weee_tax_applied_row_amount);
            }

        });
    }
);
