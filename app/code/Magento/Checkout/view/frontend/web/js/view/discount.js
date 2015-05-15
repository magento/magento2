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
                template: 'Magento_Checkout/review/discount',
                displayArea: 'totals'
            },
        colspan: 3,
        style: '',
        fieldName: 'Discount',
        totals: quote.getTotals(),
        getPureValue: function() {
            var price = 0;
            if (this.totals()) {
                price = this.totals().discount_amount;
            }
            return price;
        },
        getValue: function() {
            var price = 0;
            if (this.totals()) {
                price = this.totals().discount_amount;
            }
            return priceUtils.formatPrice(price, quote.getPriceFormat());
        }
        });
    }
);
