/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'jquery',
    'uiComponent',
    '../model/messageList'
], function (ko, $, Component, globalMessages) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Ui/messages',
            selector: '[data-role=checkout-messages]',
            isHidden: false,
            listens: {
                isHidden: 'onHiddenChange'
            }
        },

        initialize: function (config, messageContainer) {
            this._super()
                .initObservable();

            this.messageContainer = messageContainer || config.messageContainer || globalMessages;

            return this;
        },

        initObservable: function () {
            this._super()
                .observe('isHidden');

            return this;
        },

        isVisible: function () {
            return this.isHidden(this.messageContainer.hasMessages());
        },

        removeAll: function () {
            this.messageContainer.clear();
        },

        onHiddenChange: function (isHidden) {
            var self = this;
            // Hide message block if needed
            if (isHidden) {
                setTimeout(function () {
                    $(self.selector).hide('blind', {}, 500)
                }, 5000);
            }
        }
    });
});
