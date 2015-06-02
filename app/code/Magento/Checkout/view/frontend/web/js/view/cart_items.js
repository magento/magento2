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
                template: 'Magento_Checkout/review/cart_items'
            },
            getItemsCount: function() {
                var totals = quote.getTotals()();
                return totals.items.length;
            },
            getItems: function() {
                var totals = quote.getTotals()();
                return totals.items;
            }
        });
    }
);
