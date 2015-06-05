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
                displayArea: 'after_details',
                template: 'Magento_Checkout/summary/item/details/subtotal'
            },
            getValue: function(quoteItem) {
                return this.getFormattedPrice(quoteItem.row_total);
            }
        });
    }
);
