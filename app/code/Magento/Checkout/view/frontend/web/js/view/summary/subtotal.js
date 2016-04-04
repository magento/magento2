/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote'
    ],
    function (Component, quote) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/summary/subtotal'
            },
            getPureValue: function() {
                var totals = quote.getTotals()();
                if (totals) {
                    return totals.subtotal;
                }
                return quote.subtotal;
            },
            getValue: function () {
                return this.getFormattedPrice(this.getPureValue());
            }

        });
    }
);
