/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
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
            message   = rule[1];
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