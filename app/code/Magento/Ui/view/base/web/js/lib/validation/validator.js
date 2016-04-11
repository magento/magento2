/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
   './rules'
], function (rules) {
    'use strict';    

    function validate(rule, value, params){
        var isValid   = true,
            rule      = rules[rule],
            message   = true,
            validator;

        if (rule) {
            validator = rule[0];
            isValid   = validator(value, params);
            params    = Array.isArray(params) ? params : [params];
            message   = params.reduce(function (message, param, idx) {
                return message.replace(new RegExp('\\{' + idx + '\\}', 'g'), param);
            }, rule[1]);
        }

        return !isValid ? message : '';
    }

    /**
     * Validates value by rule and it's params.
     * @param {(String|Object)} rule - One or many validation rules.
     * @param {*} value - Value to validate.
     * @param {*} [params] - Rule configuration
     * @return {String} Resulting error message if value is invalid.
     */
    function validator(rule, value, params){
        var msg = '';

        if(_.isObject(rule)){
            _.some(rule, function(params, rule){
                return !!(msg = validate(rule, value, params));
            });
        }
        else{
            msg = validate.apply(null, arguments);
        }

        return msg;
    }

    /**
     * Adds new validation rule.
     * 
     * @param {String} rule - rule name
     * @param {Function} validator - validation function
     * @param {String} message - validation message
     */
    validator.addRule = function(rule, validator, message){
        rules[rule] = [validator, message];
    }

    return validator;
});