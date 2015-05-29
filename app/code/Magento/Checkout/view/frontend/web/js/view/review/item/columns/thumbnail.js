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
        var imageData = window.checkoutConfig.imageData;
        return column.extend({
            defaults: {
                template: 'Magento_Checkout/review/item/columns/thumbnail'
            },
            imageData: imageData,
            getValue: function(item) {
                if (this.imageData[item.item_id]) {
                    return this.imageData[item.item_id];
                }
                return null;
                //return "/pub/static/frontend/Magento/blank/en_US/Magento_Catalog/images/product/placeholder/thumbnail.jpg";
            }
        });
    }
);
