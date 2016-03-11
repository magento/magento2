/*
 * Copyright © 2015 Magento. All rights reserved.
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
     * @returns {Object}
     */
    function validate(id, value, params) {
        var rule    = rulesList[id],
            message = rule.message,
            valid   = rule.handler(value, params),
            result;

        result = {
            rule: id,
            passed: true,
            message: ''
        };

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
     * @returns {Object}
     */
    function validator(rules, value) {
        var result;

        if (typeof rules === 'object') {
            result = {
                passed: true
            };

            _.every(rules, function (params, id) {
                if (params !== false) {
                    result = validate(id, value, params);

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
