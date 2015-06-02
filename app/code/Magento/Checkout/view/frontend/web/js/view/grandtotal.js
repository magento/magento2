/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils'
    ],
    function (Component, quote, priceUtils) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/review/subtotal'
            },
            title: 'Grand Total',
            colspan: 2,
            getValue: function() {
                var totals = quote.getTotals()();
                if (totals) {
                    return totals.grand_total;
                }
                return quote.grand_total;
            },
            getFormattedValue: function (price) {
                var totals = quote.getTotals()();
                var subtotal = 0;
                if (totals) {
                    subtotal = totals.grand_total;
                }
                return priceUtils.formatPrice(subtotal, quote.getPriceFormat());
            }

        });
    }
);
