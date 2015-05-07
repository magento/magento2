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
            message: {},
            initialize: function() {
                var that = this;
                quote.getShippingAddress().subscribe(function(shippingAddress) {
                    var customerName = shippingAddress.firstname + ' ' + shippingAddress.lastname;
                    that.message = {
                        from: ko.observable(customerName),
                        to: ko.observable(customerName),
                        message: ko.observable(null)
                    };
                    this.dispose();
                });
                this._super();
                giftOptions.addOrderLevelGiftOptions(this, 10);
            },
            isOrderLevelGiftMessageVisible: ko.observable(true),
            setOrderLevelGiftMessageVisible: function() {
                this.isOrderLevelGiftMessageVisible(!this.isOrderLevelGiftMessageVisible());
            },
            quoteId: quote.entity_id,
            getData: function() {
                return this.message;
            }
        });
    }
);
