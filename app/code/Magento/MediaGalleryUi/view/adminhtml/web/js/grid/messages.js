/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiElement',
    'escaper'
], function (Element, escaper) {
    'use strict';

    return Element.extend({
        defaults: {
            template: 'Magento_MediaGalleryUi/grid/messages',
            messageDelay: 5,
            messages: [],
            allowedTags: ['div', 'span', 'b', 'strong', 'i', 'em', 'u', 'a']
        },

        /**
         * Init observable variables
         * @return {Object}
         */
        initObservable: function () {
            this._super()
                .observe([
                    'messages'
                ]);

            return this;
        },

        /**
         * Get messages
         *
         * @returns {Array}
         */
        get: function () {
            return this.messages();
        },

        /**
         * Add message
         *
         * @param {String} type
         * @param {String} message
         */
        add: function (type, message) {
            this.messages.push({
                code: type,
                message: message
            });
        },

        /**
         * Clear messages
         */
        clear: function () {
            this.messages.removeAll();
        },

        /**
         * Schedule message cleanup
         *
         * @param {Number} delay
         */
        scheduleCleanup: function (delay) {
            // eslint-disable-next-line no-unused-vars
            var timerId;

            delay = delay || this.messageDelay;

            timerId = setTimeout(function () {
                clearTimeout(timerId);
                this.clear();
            }.bind(this), Number(delay) * 1000);
        },

        /**
         * Prepare the given message to be rendered as HTML
         *
         * @param {String} message
         * @return {String}
         */
        prepareMessageUnsanitizedHtml: function (message) {
            return escaper.escapeHtml(message, this.allowedTags);
        }
    });
});
