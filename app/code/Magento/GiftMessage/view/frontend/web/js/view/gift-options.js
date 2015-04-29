/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(['uiComponent', 'ko', '../model/gift-options', '../model/gift-message'],
    function (Component, ko, giftOptions, giftMessage) {
        "use strict";

        return Component.extend({
            defaults: {
                template: 'Magento_GiftMessage/gift-options',
                displayArea: 'shippingAdditional'
            },
            isGiftOptionsSelected: ko.observable(giftMessage.isGiftOptionsSelected()),
            isOrderLevelGiftOptionsSelected: ko.observable(giftMessage.isOrderLevelGiftOptionsSelected()),
            isItemLevelGiftOptionsSelected: ko.observable(giftMessage.isItemLevelGiftOptionsSelected()),
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
                var orderLevelOptions = [],
                    itemLevelOptions = [],
                    giftOptions;
                if (giftMessage.isOrderLevelGiftOptionsSelected() &&
                    this.isOrderLevelGiftOptionsSelected() !== giftMessage.isOrderLevelGiftOptionsSelected()
                ) {
                    orderLevelOptions = this.getOrderLevelGiftOptions()[0].submit(true);
                } else {
                    orderLevelOptions = this.getOrderLevelGiftOptions()[0].submit();
                }
                if (giftMessage.isItemLevelGiftOptionsSelected() &&
                    this.isItemLevelGiftOptionsSelected() !== giftMessage.isItemLevelGiftOptionsSelected()
                ) {
                    itemLevelOptions = this.getItemLevelGiftOptions()[0].submit(true);
                } else {
                    itemLevelOptions = this.getItemLevelGiftOptions()[0].submit();
                }
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
