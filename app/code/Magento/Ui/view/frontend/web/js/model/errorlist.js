/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(['ko'], function (ko) {
    'use strict';

    var errors = ko.observableArray([]);

    return {
        /**
         * Add error message to list.
         * @param {Object} error
         * @returns {Boolean}
         */
        add: function (error) {
            var expr = /([%])\w+/g,
                errorMessage;

            if (!error.hasOwnProperty('parameters')) {
                this.clear();
                errors.push(error.message);

                return true;
            }
            errorMessage = error.message.replace(expr, function (varName) {
                varName = varName.substr(1);

                if (error.parameters.hasOwnProperty(varName)) {
                    return error.parameters[varName];
                }

                return error.parameters.shift();
            });
            this.clear();
            errors.push(errorMessage);

            return true;
        },
        /**
         * Remove first error message in list
         */
        remove: function () {
            errors.shift();
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
        }
    };
});
