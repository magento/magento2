/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/model/quote',
        'uiComponent'
    ],
    function (quote, Component) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/summary/cart-items'
            },
            getItemsCount: function() {
                var totals = quote.getTotals()();
                return totals.items.length;
            },
            getItems: function() {
                var totals = quote.getTotals()();
                if (!totals || !totals.items) {
                    return [];
                }
                return totals.items;
            },
            getItemsQty: function() {
                var qty = 0;
                var items = this.getItems();
                for(var i in items) {
                    qty += items[i].qty;
                }
                return qty;
            }
        });
    }
);
