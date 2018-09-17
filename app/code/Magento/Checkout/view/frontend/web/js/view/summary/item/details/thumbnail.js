/**
 * Copyright © Magento, Inc. All rights reserved.
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
        var imageData = window.checkoutConfig.imageData;
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/summary/item/details/thumbnail'
            },
            displayArea: 'before_details',
            imageData: imageData,
            getImageItem: function(item) {
                if (this.imageData[item.item_id]) {
                    return this.imageData[item.item_id];
                }
                return [];
            },
            getSrc: function(item) {
                if (this.imageData[item.item_id]) {
                    return this.imageData[item.item_id]['src'];
                }
                return null;
            },
            getWidth: function(item) {
                if (this.imageData[item.item_id]) {
                    return this.imageData[item.item_id]['width'];
                }
                return null;
            },
            getHeight: function(item) {
                if (this.imageData[item.item_id]) {
                    return this.imageData[item.item_id]['height'];
                }
                return null;
            },
            getAlt: function(item) {
                if (this.imageData[item.item_id]) {
                    return this.imageData[item.item_id]['alt'];
                }
                return null;
            }
        });
    }
);
