/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        'mageUtils',
        './shipping-rates-validation-rules'
    ],
    function ($, utils, validationRules) {
        "use strict";
        var checkoutConfig = window.checkoutConfig;

        return {
            validationErrors: [],
            validate: function(address) {
                var rules = validationRules.getRules(),
                    self = this;

                $.each(rules, function(field, rule) {
                    if (rule.required && utils.isEmpty(address[field])) {
                        self.validationErrors.push('Field ' + field + ' is required.');
                    }
                });

                if (!Boolean(this.validationErrors.length)) {
                    if (address.country_id == checkoutConfig.originCountryCode) {
                        return !utils.isEmpty(address.postcode);
                    }
                    return true;
                }
                return false;
            }
        };
    }
);
