/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mageUtils',
    './shipping-rates-validation-rules',
    'mage/translate'
], function ($, utils, validationRules, $t) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return {
        validationErrors: [],

        /**
         * @param {Object} address
         * @return {Boolean}
         */
        validate: function (address) {
            var rules = validationRules.getRules(),
                self = this;

            $.each(rules, function (field, rule) {
                var message;

                if (rule.required && utils.isEmpty(address[field])) {
                    message = $t('Field ') + field + $t(' is required.');
                    self.validationErrors.push(message);
                }
            });

            if (!Boolean(this.validationErrors.length)) {
                if (address['country_id'] == checkoutConfig.originCountryCode) { //eslint-disable-line eqeqeq
                    return !utils.isEmpty(address.postcode);
                }

                return true;
            }

            return false;
        }
    };
});
