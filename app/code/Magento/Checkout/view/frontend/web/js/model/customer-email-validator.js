/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'Magento_Customer/js/model/customer',
    'mage/validation'
], function ($, customer) {
    'use strict';

    return {
        /**
         * Validate checkout agreements
         *
         * @returns {Boolean}
         */
        validate: function () {
            var emailValidationResult = customer.isLoggedIn(),
                loginFormSelector = 'form[data-role=email-with-possible-login]';

            if (!customer.isLoggedIn()) {
                $(loginFormSelector).validation();
                emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
            }

            return emailValidationResult;
        }
    };
});
