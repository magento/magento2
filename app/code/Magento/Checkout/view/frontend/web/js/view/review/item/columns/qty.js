/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        '../column'
    ],
    function (column) {
        "use strict";
        return column.extend({
            defaults: {
                ownClass: 'qty',
                columnTitle: 'Qty',
                template: 'Magento_Checkout/review/item/columns/qty'
            },
            getValue: function(quoteItem) {
                return quoteItem.qty;
            }
        });
    }
);
