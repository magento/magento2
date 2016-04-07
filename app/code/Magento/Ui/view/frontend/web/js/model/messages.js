/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'uiClass'
], function (ko, Class) {
    'use strict';

    return Class.extend({


        initialize: function () {
            this._super()
                .initObservable();

            return this;
        },


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
            this.errorMessages.push(message);

            return true;
        },


        addSuccessMessage: function (message) {
            return this.add(message, this.successMessages);
        },


        addErrorMessage: function (message) {
            return this.add(message, this.errorMessages);
        },


        getErrorMessages: function () {
            return this.errorMessages;
        },


        getSuccessMessages: function () {
            return this.successMessages;
        },


        hasMessages: function () {
            return this.errorMessages().length > 0 || this.successMessages().length > 0;
        },


        clear: function () {
            this.errorMessages.removeAll();
            this.successMessages.removeAll();
        }
    });
});
