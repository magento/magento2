/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "customerDataInvalidationRules",
    "underscore"
], function (invalidationRules, _) {
    "use strict";

    return {
        /**
         * Process all rules in loop, each rule can invalidate some sections in customer data
         *
         * @param {Object} customerData
         */
        process: function (customerData) {
            _.each(invalidationRules, function (rule, ruleName) {
                if (!_.isFunction(rule.process)) {
                    throw new Error("Rule " + ruleName + " should implement invalidationProcessor interface");
                }

                rule.process(customerData);
            });
        }
    }
});
