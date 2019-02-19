/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/template',
    'jquery/ui'
], function ($, mageTemplate) {
    'use strict';

    $.widget('mage.notification', {
        options: {
            templates: {
                global: '<div data-role="messages" id="messages">' +
                    '<div class="message <% if (data.error) { %>error<% } %>"><div><%- data.message %></div></div>' +
                '</div>',
                error: '<div data-role="messages" id="messages">' +
                    '<div class="messages"><div class="message message-error error">' +
                        '<div data-ui-id="messages-message-error"><%- data.message %></div></div>' +
                    '</div></div>'
            }
        },
        placeholder: '[data-role=messages]',

        /**
         * Notification creation
         * @protected
         */
        _create: function () {
            $(document).on('ajaxComplete ajaxError', $.proxy(this._add, this));
        },

        /**
         * Add new message
         * @protected
         * @param {Object} event - object
         * @param {Object} jqXHR - The jQuery XMLHttpRequest object returned by $.ajax()
         */
        _add: function (event, jqXHR) {
            var response;

            try {
                response = JSON.parse(jqXHR.responseText);

                if (response && response.error && response['html_message']) {
                    $(this.placeholder).html(response['html_message']);
                }
            } catch (e) {}
        },

        /**
         * Adds new message.
         *
         * @param {Object} data - Data with a message to be displayed.
         */
        add: function (data) {
            var template = data.error ? this.options.templates.error : this.options.templates.global,
                message = mageTemplate(template, {
                    data: data
                }),
                messageContainer;

            if (typeof data.insertMethod === 'function') {
                data.insertMethod(message);
            } else {
                messageContainer = data.messageContainer || this.placeholder;
                $(messageContainer).prepend(message);
            }

            return this;
        },

        /**
         * Removes error messages.
         */
        clear: function () {
            $(this.placeholder).html('');
        }
    });

    return $.mage.notification;
});
