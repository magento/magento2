/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils'
    ],
    function (Component,quote, priceUtils) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magento_Weee/checkout/summary/weee'
            },
            isIncludedInSubtotal: window.checkoutConfig.isIncludedInSubtotal,
            totals: quote.getTotals(),
            title: 'FPT',
            colspan: 3,
            getPureValue: function() {
                var items = quote.getItems();
                var sum = 0;
                for (var i = 0; i < items.length; i++) {
                    sum += parseFloat(items[i].weee_tax_applied_row_amount);
                }
                return sum;
            },
            getValue: function() {
                var items = quote.getItems();
                var sum = 0;
                for (var i = 0; i < items.length; i++) {
                    sum += parseFloat(items[i].weee_tax_applied_row_amount);
                }
                return priceUtils.formatPrice(sum, quote.getPriceFormat());
            }
        });
    }
);
