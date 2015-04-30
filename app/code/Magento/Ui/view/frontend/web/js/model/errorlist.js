/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(['ko'], function(ko) {
    "use strict";
    var errors = ko.observableArray([]);
    return {
        add: function (error) {
            var expr = /([%])\w+/g,
                errorMessage;
            if (!error.hasOwnProperty('parameters')) {
                this.clear();
                errors.push(error.message);
                return true;
            }
            errorMessage = error.message.replace(expr, function(varName) {
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
        remove: function() {
            errors.shift();
        },
        getAll: function () {
            return errors;
        },
        clear: function() {
            errors.removeAll();
        }
    };
});
