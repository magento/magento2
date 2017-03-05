/*
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    './rules'
], function (_, rulesList) {
    'use strict';

    /**
     * Validates provided value be the specified rule.
     *
     * @param {String} id - Rule identifier.
     * @param {*} value - Value to be checked.
     * @param {*} [params]
     * @param {*} additionalParams - additional validation params set by method caller
     * @returns {Object}
     */
    function validate(id, value, params, additionalParams) {
        var rule,
            message,
            valid,
            result = {
                rule: id,
                passed: true,
                message: ''
            };

        if (_.isObject(params)) {
            message = params.message || '';
        }

        if (!rulesList[id]) {
            return result;
        }

        rule    = rulesList[id];
        message = message || rule.message;
        valid   = rule.handler(value, params, additionalParams);

        if (!valid) {
            params = Array.isArray(params) ?
                params :
                [params];

            message = params.reduce(function (msg, param, idx) {
                return msg.replace(new RegExp('\\{' + idx + '\\}', 'g'), param);
            }, message);

            result.passed = false;
            result.message = message;
        }

        return result;
    }

    /**
     * Validates provied value by a specfied set of rules.
     *
     * @param {(String|Object)} rules - One or many validation rules.
     * @param {*} value - Value to be checked.
     * @param {*} additionalParams - additional validation params set by method caller
     * @returns {Object}
     */
    function validator(rules, value, additionalParams) {
        var result;

        if (typeof rules === 'object') {
            result = {
                passed: true
            };

            _.every(rules, function (ruleParams, id) {
                if (ruleParams.validate || ruleParams !== false || additionalParams) {
                    result = validate(id, value, ruleParams, additionalParams);

                    return result.passed;
                }

                return true;
            });

            return result;
        }

        return validate.apply(null, arguments);
    }

    /**
     * Adds new validation rule.
     *
     * @param {String} id - Rule identifier.
     * @param {Function} handler - Validation function.
     * @param {String} message - Error message.
     */
    validator.addRule = function (id, handler, message) {
        rulesList[id] = {
            handler: handler,
            message: message
        };
    };

    /**
     * Returns rule object found by provided identifier.
     *
     * @param {String} id - Rule identifier.
     * @returns {Object}
     */
    validator.getRule = function (id) {
        return rulesList[id];
    };

    return validator;
});
