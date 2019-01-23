/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'ko',
    'uiClass'
], function (ko, Class) {
    'use strict';

    return Class.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super()
                .initObservable();

            return this;
        },

        /** @inheritdoc */
        initObservable: function () {
            this.errorMessages = ko.observableArray([]);
            this.successMessages = ko.observableArray([]);

            return this;
        },

        /**
         * Add  message to list.
         * @param {Object} messageObj
         * @param {Object} type
         * @returns {Boolean}
         */
        add: function (messageObj, type) {
            var expr = /([%])\w+/g,
                message;

            if (!messageObj.hasOwnProperty('parameters')) {
                this.clear();
                type.push(messageObj.message);

                return true;
            }
            message = messageObj.message.replace(expr, function (varName) {
                varName = varName.substr(1);

                if (messageObj.parameters.hasOwnProperty(varName)) {
                    return messageObj.parameters[varName];
                }

                return messageObj.parameters.shift();
            });
            this.clear();
            type.push(message);

            return true;
        },

        /**
         * Add success message.
         *
         * @param {Object} message
         * @return {*|Boolean}
         */
        addSuccessMessage: function (message) {
            return this.add(message, this.successMessages);
        },

        /**
         * Add error message.
         *
         * @param {Object} message
         * @return {*|Boolean}
         */
        addErrorMessage: function (message) {
            return this.add(message, this.errorMessages);
        },

        /**
         * Get error messages.
         *
         * @return {Array}
         */
        getErrorMessages: function () {
            return this.errorMessages;
        },

        /**
         * Get success messages.
         *
         * @return {Array}
         */
        getSuccessMessages: function () {
            return this.successMessages;
        },

        /**
         * Checks if an instance has stored messages.
         *
         * @return {Boolean}
         */
        hasMessages: function () {
            return this.errorMessages().length > 0 || this.successMessages().length > 0;
        },

        /**
         * Removes stored messages.
         */
        clear: function () {
            this.errorMessages.removeAll();
            this.successMessages.removeAll();
        }
    });
});
