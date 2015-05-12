/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define([
        'uiComponent',
        'ko',
        '../model/gift-options',
        'Magento_Checkout/js/model/quote',
        '../model/gift-message'
    ],
    function (Component, ko, giftOptions, quote, giftMessage) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magento_GiftMessage/order-level-gift-message',
                displayArea: 'orderLevelGiftMessage'
            },
            message: {},
            optionType: 'gift_messages',
            initialize: function() {
                var that = this;
                quote.getShippingAddress().subscribe(function(shippingAddress) {
                    var customerName = shippingAddress.firstname + ' ' + shippingAddress.lastname;
                    that.message = {
                        from: ko.observable(giftMessage.getDefaultMessageForQuote().from || customerName),
                        to: ko.observable(giftMessage.getDefaultMessageForQuote().to || customerName),
                        message: ko.observable(giftMessage.getDefaultMessageForQuote().message)
                    };
                    this.dispose();
                });
                this._super();
                giftOptions.addOrderLevelGiftOptions(this, 10);
            },
            isOrderLevelGiftMessageVisible: ko.observable(false),
            setOrderLevelGiftMessageVisible: function(data, event) {
                event.preventDefault();
                this.isOrderLevelGiftMessageVisible(!this.isOrderLevelGiftMessageVisible());
            },
            quoteId: quote.entity_id,
            submit: function(remove) {
                remove = remove || false;
                if (this.message.message() !== null) {
                    return [{
                        sender: remove ? null : this.message.from(),
                        recipient: remove ? null : this.message.to(),
                        message: remove ? null : this.message.message(),
                        extension_attributes: {
                            entity_id: this.quoteId,
                            entity_type: 'quote'
                        }
                    }];
                }
                return [];
            }
        });
    }
);
