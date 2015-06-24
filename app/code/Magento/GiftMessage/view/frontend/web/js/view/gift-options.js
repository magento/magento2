/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(['uiComponent', 'ko', '../model/gift-options', '../model/gift-message', 'Magento_Ui/js/model/messageList'],
    function (Component, ko, giftOptions, giftMessage, messageList) {
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
                return this.filterOptions(giftOptions.getOrderLevelGiftOptions());
            },
            getItemLevelGiftOptions: function() {
                return this.filterOptions(giftOptions.getItemLevelGiftOptions());
            },
            getExtraGiftOptions: function() {
                return this.filterOptions(giftOptions.getExtraGiftOptions());
            },
            filterOptions: function(options) {
                return _.filter(options, function(option) {
                        var result = true;
                        if (option.isDirectRendering !== 'undefined') {
                            result = !option.isDirectRendering;
                        }
                        return result;
                    }
                );
            },
            collectOptions: function(giftOption, additionalFlag) {
                if (!this.isAvailableForSubmiting(giftOption)) {
                    return false;
                }
                var self = this;
                if (giftOption.optionType === 'undefined') {
                    messageList.addErrorMessage('You should define type of your custom option');
                }

                if (!this.options.hasOwnProperty(giftOption.optionType)) {
                    this.options[giftOption.optionType] = [];
                }

                _.each(giftOption.submit(additionalFlag), function(optionItem) {
                    self.options[giftOption.optionType].push(optionItem);
                });
            },
            isAvailableForSubmiting: function(option) {
                return typeof option.isSubmit == 'undefined' || option.isSubmit ? true : false;
            },
            submit: function() {
                var self = this;

                var removeOrder = giftOptions.isItemLevelGiftOptionsEnabled() && this.isOrderLevelGiftOptionsEnabled()
                    && (!this.isGiftOptionsSelected() || !this.isOrderLevelGiftOptionsSelected())
                    ? true
                    : false;
                _.each(giftOptions.getOrderLevelGiftOptions(), function(option) {
                    self.collectOptions(option, removeOrder);
                });

                var removeItem = giftOptions.isItemLevelGiftOptionsEnabled() && this.isItemLevelGiftOptionsEnabled()
                && (!this.isGiftOptionsSelected() || !this.isItemLevelGiftOptionsSelected())
                    ? true
                    : false;
                _.each(giftOptions.getItemLevelGiftOptions(), function(option) {
                    self.collectOptions(option, removeItem);
                });

                _.each(giftOptions.getExtraGiftOptions(), function(option) {
                    self.collectOptions(option);
                });

                var result = this.options;
                this.options = [];
                return result;
            }
        });
    }
);
