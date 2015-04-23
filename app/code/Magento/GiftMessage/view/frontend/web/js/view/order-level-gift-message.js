/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define([
        'uiComponent',
        'ko',
        '../model/gift-options',
        'Magento_Checkout/js/model/quote'
    ],
    function (Component, ko, giftOptions, quote) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magento_GiftMessage/order-level-gift-message',
                displayArea: 'orderLevelGiftMessage'
            },
            initialize: function() {
                this._super();
                giftOptions.addOrderLevelGiftOptions(this);
            },
            isOrderLevelGiftMessageVisible: ko.observable(true),
            setOrderLevelGiftMessageVisible: function() {
                var defaultName = quote.getShippingAddress()().firstname + ' ' + quote.getShippingAddress()().lastname;
                this.isOrderLevelGiftMessageVisible(!this.isOrderLevelGiftMessageVisible());
                this.giftMessageSenderName(defaultName);
                this.giftMessageRecipientName(defaultName);
            },
            giftMessageSenderName: ko.observable(),
            giftMessageRecipientName: ko.observable()
        });
    }
);
