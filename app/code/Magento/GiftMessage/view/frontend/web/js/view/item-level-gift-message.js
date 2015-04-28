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
            quoteItems: [],
            quoteItemsCount: 0,
            isItemLevelGiftMessagesHidden: {},
            initialize: function() {
                var item,
                    that = this,
                    quoteItems = quote.getItems();
                quote.getShippingAddress().subscribe(function(shippingAddress) {
                    var customerName = shippingAddress.firstname + ' ' + shippingAddress.lastname;
                    for (item in quoteItems) {
                        if (quoteItems.hasOwnProperty(item)) {
                            if (quoteItems[item].is_virtual == 0) {
                                var itemId = quoteItems[item].item_id;
                                that.messages[itemId] = {
                                    from: ko.observable(customerName),
                                    to: ko.observable(customerName),
                                    message: ko.observable(null)
                                };
                                that.isItemLevelGiftMessagesHidden[itemId] = ko.observable(true);
                                that.quoteItems.push(quoteItems[item]);
                            }
                        }
                    }
                    that.quoteItemsCount = that.quoteItems.length;
                    this.dispose();
                });
                this._super();
                giftOptions.addItemLevelGiftOptions(this);
            },
            itemImages: ko.observableArray(),
            setItemLevelGiftMessageHidden: function(itemId) {
                this.isItemLevelGiftMessagesHidden[itemId](!this.isItemLevelGiftMessagesHidden[itemId]());
            },
            submit: function() {
                var itemId,
                    giftMessages = [],
                    that = this;
                for (itemId in this.messages) {
                    if (that.messages.hasOwnProperty(itemId)) {
                        if (that.messages[itemId].message() !== null) {
                            giftMessages.push({
                                sender: that.messages[itemId].from(),
                                recipient: that.messages[itemId].to(),
                                message: that.messages[itemId].message(),
                                extension_attributes: {
                                    entity_id: itemId,
                                    entity_type: 'item'
                                }
                            });
                        }
                    }
                }
                return giftMessages;
            }
        });
    }
);
