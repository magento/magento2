/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        '../details'
    ],
    function (details) {
        "use strict";
        return details.extend({
            defaults: {
                ownClass: 'subtotal',
                columnTitle: 'Subtotal',
                template: 'Magento_Checkout/review/item/details/subtotal'
            },
            displayArea: 'after_details',
            getValue: function(quoteItem) {
                return this.getFormattedPrice(quoteItem.row_total);
            }
        });
    }
);
