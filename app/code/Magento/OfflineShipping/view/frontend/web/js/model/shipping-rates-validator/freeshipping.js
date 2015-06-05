/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        'mageUtils',
        '../shipping-rates-validation-rules/freeshipping'
    ],
    function ($, utils, validationRules) {
        "use strict";
        return {
            validationErrors: [],
            validate: function(address) {
                var self = this;
                this.validationErrors = [];
                $.each(validationRules.getRules(), function(field, rule) {
                    if (rule.required && utils.isEmpty(address[field])) {
                        self.validationErrors.push('Field ' + field + ' is required.');
                    }
                });
                return !Boolean(this.validationErrors.length);
            }
        };
    }
);
