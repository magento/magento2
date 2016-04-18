/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'uiComponent'
    ],
    function (Component) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/summary/item/details'
            },
            getValue: function(quoteItem) {
                return quoteItem.name;
            }
        });
    }
);
