/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(['uiComponent', 'ko', '../model/gift-options', '../model/gift-message', 'Magento_Ui/js/model/errorlist'],
    function (Component, ko, giftOptions, giftMessage, errorList) {
        "use strict";

        return Component.extend({
            defaults: {
                template: 'Magento_GiftMessage/gift-options',
                displayArea: 'shippingAdditional'
            },
            options: [],
            isGiftOptionsSelected: ko.observable(giftMessage.isGiftOptionsSelected()),
            isOrderLevelGiftOptionsSelected: ko.observable(giftMessage.isOrderLevelGiftOptionsSelected()),
            isItemLevelGiftOptionsSelected: ko.observable(giftMessage.isItemLevelGiftOptionsSelected()),
            isGiftOptionsAvailable: function() {
                return giftOptions.isGiftOptionsAvailable();
            },
            isOrderLevelGiftOptionsEnabled: function() {
                return giftOptions.isOrderLevelGiftOptionsEnabled();
            },
            isItemLevelGiftOptionsEnabled: function() {
                return giftOptions.isItemLevelGiftOptionsEnabled();
            },
            getOrderLevelGiftOptions: function() {
                return giftOptions.getOrderLevelGiftOptions();
            },
            getItemLevelGiftOptions: function() {
                return giftOptions.getItemLevelGiftOptions();
            },
            getExtraGiftOptions: function() {
                return giftOptions.getExtraGiftOptions();
            },
            collectOptions: function(giftOption, additionalFlag) {
                var self = this;
                if (giftOption.optionType === 'undefined') {
                    errorList.add('You should define type of your custom option');
                }

                if (!this.options.hasOwnProperty(giftOption.optionType)) {
                    this.options[giftOption.optionType] = [];
                }

                _.each(giftOption.submit(additionalFlag), function(optionItem) {
                    self.options[giftOption.optionType].push(optionItem);
                });
            },
            submit: function() {
                var self = this;
                var removeOrder = (giftMessage.isOrderLevelGiftOptionsSelected()
                && this.isOrderLevelGiftOptionsSelected() !== giftMessage.isOrderLevelGiftOptionsSelected())
                    ? true
                    : false;
                _.each(this.getOrderLevelGiftOptions(), function(option) {
                    self.collectOptions(option, removeOrder);
                });

                var removeItem = (giftMessage.isItemLevelGiftOptionsSelected()
                && this.isItemLevelGiftOptionsSelected() !== giftMessage.isItemLevelGiftOptionsSelected())
                    ? true
                    : false;
                _.each(this.getItemLevelGiftOptions(), function(option) {
                    self.collectOptions(option, removeItem);
                });

                _.each(this.getExtraGiftOptions(), function(option) {
                    self.collectOptions(option);
                });

                var result = this.options;
                this.options = [];
                return result;
            }
        });
    }
);
