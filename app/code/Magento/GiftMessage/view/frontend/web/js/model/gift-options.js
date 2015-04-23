/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define([],
    function() {
        "use strict";
        var isOrderLevelGiftOptionsEnabled = window.checkoutConfig.isOrderLevelGiftOptionsEnabled || false,
            isItemLevelGiftOptionsEnabled = window.checkoutConfig.isItemLevelGiftOptionsEnabled || false;

        return {
            orderLevelGiftOptions: [],
            itemLevelGiftOptions: [],
            isGiftOptionsAvailable: function() {
                return isOrderLevelGiftOptionsEnabled || isItemLevelGiftOptionsEnabled;
            },
            isOrderLevelGiftOptionsEnabled: function() {
                return isOrderLevelGiftOptionsEnabled;
            },
            isItemLevelGiftOptionsEnabled: function() {
                return isItemLevelGiftOptionsEnabled;
            },
            getOrderLevelGiftOptions: function() {
                return this.orderLevelGiftOptions;
            },
            getItemLevelGiftOptions: function() {
                return this.itemLevelGiftOptions;
            },
            addOrderLevelGiftOptions: function(giftOption) {
                this.orderLevelGiftOptions.push(giftOption);
            },
            addItemLevelGiftOptions: function(giftOption) {
                this.itemLevelGiftOptions.push(giftOption);
            }
        };
    }
);
