/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(['underscore', './gift-options'],
    function(_, giftOptions) {
        "use strict";
        var itemLevelDefaultMessages, orderLevelDefaultMessage,
            isItemLevelGiftOptionsSelected = false,
            isOrderLevelGiftOptionsSelected = false,
            isGiftOptionsSelected = false;
        if (giftOptions.isItemLevelGiftOptionsEnabled() && _.isObject(window.checkoutConfig.giftMessage.itemLevel)) {
            itemLevelDefaultMessages = window.checkoutConfig.giftMessage.itemLevel;
            isItemLevelGiftOptionsSelected = true;
            isGiftOptionsSelected = true;
        }
        if (giftOptions.isOrderLevelGiftOptionsEnabled() && _.isObject(window.checkoutConfig.giftMessage.orderLevel)) {
            orderLevelDefaultMessage = window.checkoutConfig.giftMessage.orderLevel;
            isOrderLevelGiftOptionsSelected = true;
            isGiftOptionsSelected = true;
        }
        return {
            getDefaultMessageForItem: function(itemId) {
                if (_.isObject(itemLevelDefaultMessages) && itemLevelDefaultMessages.hasOwnProperty(itemId)) {
                    return {
                        from: itemLevelDefaultMessages[itemId].sender,
                        to: itemLevelDefaultMessages[itemId].recipient,
                        message: itemLevelDefaultMessages[itemId].message
                    };
                }
                return {
                    from: null, to: null, message: null
                };
            },
            getDefaultMessageForQuote: function() {
                if (orderLevelDefaultMessage) {
                    return {
                        from: orderLevelDefaultMessage.sender,
                        to: orderLevelDefaultMessage.recipient,
                        message: orderLevelDefaultMessage.message
                    };
                }
                return {
                    from: null, to: null, message: null
                };
            },
            isGiftOptionsSelected: function() {
                return isGiftOptionsSelected;
            },
            isItemLevelGiftOptionsSelected: function() {
                return isItemLevelGiftOptionsSelected;
            },
            isOrderLevelGiftOptionsSelected: function() {
                return isOrderLevelGiftOptionsSelected;
            }
        };
    }
);
