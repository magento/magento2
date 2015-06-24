/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(['ko'], function (ko) {
    'use strict';

    var errors = ko.observableArray([]);
    var success =  ko.observableArray([]);

    return {
        errors: errors,
        success: success,
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
            errors.push(message);

            return true;
        },
        addSuccessMessage: function (message) {
            return this.add(message, this.success)

        },
        addErrorMessage: function (message) {
            return this.add(message, this.errors)
        },
        /**
         * Remove first error message in list
         */
        remove: function (type) {
            type.shift();
        },
        /**
         * Get all error messages
         * @returns {Object}
         */
        getAll: function () {
            return errors;
        },
        /**
         * Clear error list
         */
        clear: function () {
            errors.removeAll();
            success.removeAll();
        },
        getAllErrors: function () {
            return errors;
        },
        getAllSuccess: function () {
            return success;
        }
    };
});
