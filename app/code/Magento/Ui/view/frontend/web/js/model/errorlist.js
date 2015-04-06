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
            errors.push(error);
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
