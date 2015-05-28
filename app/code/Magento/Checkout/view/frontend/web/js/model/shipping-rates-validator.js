/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        './shipping-rates-validation-rules'
    ],
    function ($, shippingRatesValidationRules) {
        "use strict";
        return {
            validators: [],
            registerValidator: function(validator) {
                this.validators.push(validator);
            },
            validateAddress: function(address) {
                var valid = false;
                $.each(this.validators, function(index, validator) {
                    var result = validator.validate(address, shippingRatesValidationRules.getRules());
                    if (result) {
                        valid = true;
                        return false;
                    }
                });
                return valid;
            }
        };
    }
);
