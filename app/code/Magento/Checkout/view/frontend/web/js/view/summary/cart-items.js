/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/model/totals',
        'uiComponent'
    ],
    function (totals, Component) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/summary/cart-items'
            },
            totals: totals.totals(),
            getItems: totals.getItems(),
            getItemsQty: function() {
                return parseInt(this.totals.items_qty) || 0;
            }
        });
    }
);
