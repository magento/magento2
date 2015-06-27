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
                colspan: 3,
                displayArea: 'before_grandtotal',
                title: 'FPT',
                template: 'Magento_Weee/checkout/review/weee_total'
            },
            isIncludedInSubtotal: window.checkoutConfig.isIncludedInSubtotal,
            totals: quote.getTotals(),
            getColspan: function() {
                return this.colspan;
            },
            getTitle: function() {
                return this.title;
            },
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
