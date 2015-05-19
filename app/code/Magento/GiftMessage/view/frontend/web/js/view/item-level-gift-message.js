/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(['uiComponent', 'ko', '../model/gift-options', 'Magento_Checkout/js/model/quote', '../model/gift-message'],
    function (Component, ko, giftOptions, quote, giftMessage) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magento_GiftMessage/item-level-gift-message',
                displayArea: 'itemLevelGiftMessage'
            },
            messages: {},
            quoteItems: [],
            quoteItemsCount: 0,
            imagePlaceholder: window.checkoutConfig.staticBaseUrl +
                '/frontend/Magento/blank/en_US/Magento_Catalog/images/product/placeholder/thumbnail.jpg',
            optionType: 'gift_messages',
            initialize: function() {
                var item,
                    that = this,
                    quoteItems = quote.getItems();
                quote.getShippingAddress().subscribe(function(shippingAddress) {
                    var name = shippingAddress.firstname + ' ' + shippingAddress.lastname;
                    for (item in quoteItems) {
                        if (quoteItems.hasOwnProperty(item)) {
                            if (quoteItems[item].is_virtual === '0') {
                                var itemId = quoteItems[item].item_id;
                                that.messages[itemId] = {
                                    from: ko.observable(giftMessage.getDefaultMessageForItem(itemId).from || name),
                                    to: ko.observable(giftMessage.getDefaultMessageForItem(itemId).to || name),
                                    message: ko.observable(giftMessage.getDefaultMessageForItem(itemId).message)
                                };
                                quoteItems[item].isItemLevelGiftMessageVisible = ko.observable(false);
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
            setItemLevelGiftMessageHidden: function(data, event) {
                event.preventDefault();
                if (data.hasOwnProperty('item_id')) {
                    this.isItemLevelGiftMessageVisible(!this.isItemLevelGiftMessageVisible());
                }
            },
            submit: function(remove) {
                remove = remove || false;
                var itemId,
                    giftMessages = [],
                    that = this;
                for (itemId in this.messages) {
                    if (that.messages.hasOwnProperty(itemId)) {
                        if (that.messages[itemId].message() !== null) {
                            giftMessages.push({
                                sender: remove ? null : that.messages[itemId].from(),
                                recipient: remove ? null : that.messages[itemId].to(),
                                message: remove ? null : that.messages[itemId].message(),
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
