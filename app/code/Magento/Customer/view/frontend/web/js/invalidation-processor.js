/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "underscore",
    "uiClass",
    "require"
], function (_, Class, require) {
    "use strict";

    return Class.extend({
        defaults: {
            invalidationRules: {}
        },

        /**
         * Process all rules in loop, each rule can invalidate some sections in customer data
         *
         * @param {Object} customerData
         */
        process: function (customerData) {
            var rule;

            _.each(this.invalidationRules, function (rulePath, ruleName) {
                debugger;
                rule = require(rulePath);
                if (!_.isFunction(rule.process)) {
                    throw new Error("Rule " + ruleName + " should implement invalidationProcessor interface");
                }

                rule.process(customerData);
            });
        }
    });
});
