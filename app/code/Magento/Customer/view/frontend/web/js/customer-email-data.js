/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Customer/js/customer-data'
    ],
    function (storage) {
        "use strict";

        var cacheKey = 'customer-email';
        return {
            getValidatedValue: function () {
                var obj = storage.get(cacheKey)();
                return (obj.validatedValue) ? obj.validatedValue : '';
            },

            setValidatedValue: function (email) {
                var obj = storage.get(cacheKey)();
                obj.validatedValue = email;
                storage.set(cacheKey, obj);
            },

            getInputFieldValue: function () {
                var obj = storage.get(cacheKey)();
                return (obj.inputFieldValue) ? obj.inputFieldValue : '';
            },

            setInputFieldValue: function (email) {
                var obj = storage.get(cacheKey)();
                obj.inputFieldValue = email;
                storage.set(cacheKey, obj);
            }
        };
    }
);
