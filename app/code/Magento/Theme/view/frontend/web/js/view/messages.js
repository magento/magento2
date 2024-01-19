/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'underscore',
    'escaper',
    'jquery/jquery-storageapi'
], function ($, Component, customerData, _, escaper) {
    'use strict';

    return Component.extend({
        defaults: {
            cookieMessages: [],
            cookieMessagesObservable: [],
            messages: [],
            allowedTags: ['div', 'span', 'b', 'strong', 'i', 'em', 'u', 'a']
        },

        /**
         * Extends Component object by storage observable messages.
         */
        initialize: function () {
            this._super().observe(
                [
                    'cookieMessagesObservable'
                ]
            );

            // The "cookieMessages" variable is not used anymore. It exists for backward compatibility; to support
            // merchants who have overwritten "messages.phtml" which would still point to cookieMessages instead of the
            // observable variant (also see https://github.com/magento/magento2/pull/37309).
            this.cookieMessages = _.unique($.cookieStorage.get('mage-messages'), 'text');
            this.cookieMessagesObservable(this.cookieMessages);

            this.messages = customerData.get('messages').extend({
                disposableCustomerData: 'messages'
            });

            $.mage.cookies.set('mage-messages', '', {
                samesite: 'strict',
                domain: ''
            });
        },

        /**
         * Prepare the given message to be rendered as HTML
         *
         * @param {String} message
         * @return {String}
         */
        prepareMessageForHtml: function (message) {
            return escaper.escapeHtml(message, this.allowedTags);
        },
        purgeMessages: function () {
            if (!_.isEmpty(this.messages().messages)) {
                customerData.set('messages', {});
            }
        }
    });
});
