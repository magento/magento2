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
            messages: [],
            allowedTags: ['div', 'span', 'b', 'strong', 'i', 'em', 'u', 'a']
        },

        /**
         * Extends Component object by storage observable messages.
         */
        initialize: function () {
            var _self = this;

            this._super().observe('messages');


            this.cookieMessages = _.unique($.cookieStorage.get('mage-messages'), 'text');

            _self.messages = customerData.get('messages').extend({
                disposableCustomerData: 'messages'
            });

            // Force to clean obsolete messages
            if (!_.isEmpty(_self.messages().messages)) {
                customerData.set('messages', {});
                _self.messages().messages = {};
            }

            $.mage.cookies.set('mage-messages', '', {
                samesite: 'strict',
                domain: !_.isEmpty($.mage.cookies.defaults.domain) ? $.mage.cookies.defaults.domain : '',
                path: !_.isEmpty($.mage.cookies.defaults.path) ? $.mage.cookies.defaults.path : '/'
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
        }
    });
});
