/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

define(['uiComponent'], function (Component) {
    'use strict';

    var quoteMessages = window.checkoutConfig.quoteMessages;

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/item/details/message'
        },
        displayArea: 'item_message',
        quoteMessages: quoteMessages,

        /**
         * @param {Object} item
         * @return {null}
         */
        getMessage: function (item) {
            if (this.quoteMessages[item['item_id']]) {
                return this.quoteMessages[item['item_id']];
            }

            return null;
        }
    });
});
