/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(['uiComponent', 'ko', '../model/gift-options'],
    function (Component, ko, giftOptions) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magento_GiftMessage/gift-options',
                displayArea: 'shippingAdditional'
            },
            isGiftOptionsSelected: ko.observable(false),
            isOrderLevelGiftOptionsSelected: ko.observable(false),
            isItemLevelGiftOptionsSelected: ko.observable(false),
            isGiftOptionsAvailable: function() {
                return giftOptions.isGiftOptionsAvailable();
            },
            isOrderLevelGiftOptionsEnabled: function() {
                return giftOptions.isOrderLevelGiftOptionsEnabled();
            },
            isItemLevelGiftOptionsEnabled: function() {
                return giftOptions.isItemLevelGiftOptionsEnabled();
            },
            getOrderLevelGiftOptions: function() {
                return giftOptions.getOrderLevelGiftOptions();
            },
            getItemLevelGiftOptions: function() {
                return giftOptions.getItemLevelGiftOptions();
            },
            submit: function() {
                var orderLevelOptions = this.getOrderLevelGiftOptions()[0].submit(),
                    itemLevelOptions = this.getItemLevelGiftOptions()[0].submit(),
                    giftOptions = orderLevelOptions.concat(itemLevelOptions);
                if (giftOptions.length === 0) {
                    return [];
                }
                return {
                    gift_messages: giftOptions
                };
            }
        });
    }
);
