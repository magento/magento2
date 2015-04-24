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
                displayArea: 'giftOptions'
            },
            isGiftOptionsSelected: ko.observable(false),
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
            isOrderLevelGiftOptionsSelected: ko.observable(false),
            isItemLevelGiftOptionsSelected: ko.observable(false)
        });
    }
);
