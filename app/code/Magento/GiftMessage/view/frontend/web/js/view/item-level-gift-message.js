/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(['uiComponent', 'ko', '../model/gift-options', 'Magento_Checkout/js/model/quote'],
    function (Component, ko, giftOptions, quote) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magento_GiftMessage/Item-level-gift-message',
                displayArea: 'itemLevelGiftMessage'
            },
            messages: {},
            isItemLevelGiftMessagesHidden: {},
            initialize: function() {
                var item,
                    that = this,
                    quoteItems = quote.getItems();
                quote.getShippingAddress().subscribe(function(shippingAddress) {
                    var customerName = shippingAddress.firstname + ' ' + shippingAddress.lastname;
                    for (item in quoteItems) {
                        if (quoteItems.hasOwnProperty(item)) {
                            var itemId = quoteItems[item].item_id;
                            that.messages[itemId] = {
                                from: ko.observable(customerName),
                                to: ko.observable(customerName),
                                message: ko.observable(null)
                            };
                            that.isItemLevelGiftMessagesHidden[itemId] = ko.observable(true);
                        }
                    }
                    this.dispose();
                });
                this._super();
                giftOptions.addItemLevelGiftOptions(this);
            },
            quoteItems: quote.getItems(),
            quoteItemsCount: quote.getItems().length,
            itemImages: ko.observableArray(),
            setItemLevelGiftMessageHidden: function(itemId) {
                this.isItemLevelGiftMessagesHidden[itemId](!this.isItemLevelGiftMessagesHidden[itemId]());
            },
            getData: function() {
                return this.messages;
            }
        });
    }
);
