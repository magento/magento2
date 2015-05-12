/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(['underscore'],
    function(_) {
        "use strict";
        var isOrderLevelGiftOptionsEnabled = window.checkoutConfig.isOrderLevelGiftOptionsEnabled || false,
            isItemLevelGiftOptionsEnabled = window.checkoutConfig.isItemLevelGiftOptionsEnabled || false,
            isAllowPrintedCard = window.checkoutConfig.giftWrapping.isAllowPrintedCard || false,
            isAllowGiftReceipt = window.checkoutConfig.giftWrapping.isAllowGiftReceipt || false;

        return {
            orderLevelGiftOptions: [],
            itemLevelGiftOptions: [],
            extraGiftOptions: [],
            isGiftOptionsAvailable: function() {
                return isOrderLevelGiftOptionsEnabled
                    || isItemLevelGiftOptionsEnabled
                    || isAllowPrintedCard
                    || isAllowGiftReceipt;
            },
            isOrderLevelGiftOptionsEnabled: function() {
                return isOrderLevelGiftOptionsEnabled;
            },
            isItemLevelGiftOptionsEnabled: function() {
                return isItemLevelGiftOptionsEnabled;
            },
            getExtraGiftOptions: function() {
                return this.getGiftOptions(this.extraGiftOptions);
            },
            getOrderLevelGiftOptions: function() {
                return this.getGiftOptions(this.orderLevelGiftOptions);
            },
            getItemLevelGiftOptions: function() {
                return this.getGiftOptions(this.itemLevelGiftOptions);
            },
            getGiftOptions: function(options) {
                return _.filter(
                    _.map(
                        _.sortBy(options, function(giftOption){
                            return giftOption.sortOrder
                        }),
                        function(giftOption) {
                            return giftOption.option
                        }
                    ), function(option) {
                        return !(option.hasOwnProperty('isDirectRendering') && option.isDirectRendering);
                    }
                );
            },
            setExtraGiftOptions: function (giftOption, sortOrder) {
                this.extraGiftOptions.push({'option': giftOption, 'sortOrder': sortOrder});
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
