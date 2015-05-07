/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(['underscore'],
    function(_) {
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
                return _.map(
                    _.sortBy(this.orderLevelGiftOptions, function(giftOption){
                        return giftOption.sortOrder
                    }),
                    function(giftOption) {
                        return giftOption.option
                    }
                );
            },
            getItemLevelGiftOptions: function() {
                return _.map(
                    _.sortBy(this.itemLevelGiftOptions, function(giftOption){
                        return giftOption.sortOrder
                    }),
                    function(giftOption) {
                        return giftOption.option
                    }
                );
            },
            addOrderLevelGiftOptions: function(giftOption, sortOrder) {
                this.orderLevelGiftOptions.push({'option': giftOption, 'sortOrder': sortOrder});
            },
            addItemLevelGiftOptions: function(giftOption, sortOrder) {
                this.itemLevelGiftOptions.push({'option': giftOption, 'sortOrder': sortOrder});
            }
        };
    }
);
