/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint regexdash:true eqnull:true browser:true jquery:true*/
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'jquery/ui',
            'jquery/validate',
            'mage/translate'
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
    "use strict";
    $.extend(true, $, {
        // @TODO: Move methods 'isEmpty', 'isEmptyNoTrim', 'parseNumber', 'stripHtml' in file with utility functions
        mage: {
            /**
             * Check if string is empty with trim
             * @param {string} value
             */
            isEmpty: function (value) {
                return (value === '' || value === undefined || (value == null) || (value.length === 0) || /^\s+$/.test(value));
            },

            /**
             * Check if string is empty no trim
             * @param {string} value
             */
            isEmptyNoTrim: function (value) {
                return (value === '' || (value == null) || (value.length === 0));
            },


            /**
             * Checks if {value} is between numbers {from} and {to}
             * @param {string} value
             * @param {string} from
             * @param {string} to
             * @returns {boolean}
             */
            isBetween: function (value, from, to) {
                return ($.mage.isEmpty(from) || value >= $.mage.parseNumber(from)) &&
                    ($.mage.isEmpty(to) || value <= $.mage.parseNumber(to));
            },

            /**
             * Parse price string
             * @param {string} value
             */
            parseNumber: function (value) {
                if (typeof value !== 'string') {
                    return parseFloat(value);
                }
                var isDot = value.indexOf('.');
                var isComa = value.indexOf(',');
                if (isDot !== -1 && isComa !== -1) {
                    if (isComa > isDot) {
                        value = value.replace('.', '').replace(',', '.');
                    } else {
                        value = value.replace(',', '');
                    }
                } else if (isComa !== -1) {
                    value = value.replace(',', '.');
                }
                return parseFloat(value);
            },

            /**
             * Removes HTML tags and space characters, numbers and punctuation.
             * @param value Value being stripped.
             * @return {*}
             */
            stripHtml: function (value) {
                return value.replace(/<.[^<>]*?>/g, ' ').replace(/&nbsp;|&#160;/gi, ' ')
                    .replace(/[0-9.(),;:!?%#$'"_+=\/-]*/g, '');
            }
        }
    });

    $.validator.addMethod = function (name, method, message, dontSkip) {
        $.validator.methods[name] = method;
        $.validator.messages[name] = message !== undefined ? message : $.validator.messages[name];

        if (method.length < 3 || dontSkip) {
            $.validator.addClassRules(name, $.validator.normalizeRule(name));
        }
    };

    /**
     * Javascript object with credit card types
     * 0 - regexp for card number
     * 1 - regexp for cvn
     * 2 - check or not credit card number trough Luhn algorithm by
     */
    var creditCartTypes = {
        'SO': [new RegExp('^(6334[5-9]([0-9]{11}|[0-9]{13,14}))|(6767([0-9]{12}|[0-9]{14,15}))$'), new RegExp('^([0-9]{3}|[0-9]{4})?$'), true],
        'SM': [new RegExp('(^(5[0678])[0-9]{11,18}$)|(^(6[^05])[0-9]{11,18}$)|(^(601)[^1][0-9]{9,16}$)|(^(6011)[0-9]{9,11}$)|(^(6011)[0-9]{13,16}$)|(^(65)[0-9]{11,13}$)|(^(65)[0-9]{15,18}$)|(^(49030)[2-9]([0-9]{10}$|[0-9]{12,13}$))|(^(49033)[5-9]([0-9]{10}$|[0-9]{12,13}$))|(^(49110)[1-2]([0-9]{10}$|[0-9]{12,13}$))|(^(49117)[4-9]([0-9]{10}$|[0-9]{12,13}$))|(^(49118)[0-2]([0-9]{10}$|[0-9]{12,13}$))|(^(4936)([0-9]{12}$|[0-9]{14,15}$))'), new RegExp('^([0-9]{3}|[0-9]{4})?$'), true],
        'VI': [new RegExp('^4[0-9]{12}([0-9]{3})?$'), new RegExp('^[0-9]{3}$'), true],
        'MC': [new RegExp('^(?:5[1-5][0-9]{2}|222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720)[0-9]{12}$'), new RegExp('^[0-9]{3}$'), true],
        'AE': [new RegExp('^3[47][0-9]{13}$'), new RegExp('^[0-9]{4}$'), true],
        'DI': [new RegExp('^(6011(0|[2-4]|74|7[7-9]|8[6-9]|9)|6(4[4-9]|5))\\d*$'), new RegExp('^[0-9]{3}$'), true],
        'JCB': [new RegExp('^35(2[8-9]|[3-8])\\d*$'), new RegExp('^[0-9]{3}$'), true],
        'DN': [new RegExp('^(3(0[0-5]|095|6|[8-9]))\\d*$'), new RegExp('^[0-9]{3}$'), true],
        'UN': [new RegExp('^(622(1(2[6-9]|[3-9])|[3-8]|9([[0-1]|2[0-5]))|62[4-6]|628([2-8]))\\d*?$'), new RegExp('^[0-9]{3}$'), true],
        'MI': [new RegExp('^(5(0|[6-9])|63|67(?!59|6770|6774))\\d*$'), new RegExp('^[0-9]{3}$'), true],
        'MD': [new RegExp('^6759(?!24|38|40|6[3-9]|70|76)|676770|676774\\d*$'), new RegExp('^[0-9]{3}$'), true]
    };

    /**
     * validate credit card number using mod10
     * @param s
     * @return {Boolean}
     */
    function validateCreditCard(s) {
        // remove non-numerics
        var v = "0123456789",
            w = "", i, j, k, m, c, a, x;
        for (i = 0; i < s.length; i++) {
            x = s.charAt(i);
            if (v.indexOf(x, 0) != -1)
                w += x;
        }
        // validate number
        j = w.length / 2;
        k = Math.floor(j);
        m = Math.ceil(j) - k;
        c = 0;
        for (i = 0; i < k; i++) {
            a = w.charAt(i * 2 + m) * 2;
            c += a > 9 ? Math.floor(a / 10 + a % 10) : a;
        }
        for (i = 0; i < k + m; i++) {
            c += w.charAt(i * 2 + 1 - m) * 1;
        }
        return (c % 10 === 0);
    }

    /**
     * validate all table required inputs at once, using single hidden input
     * @param {String} value
     * @param {HTMLElement} element
     *
     * @return {Boolean}
     */
    function tableSingleValidation(value, element) {
        var empty = $(element).closest('table')
            .find('input.required-option:visible')
            .filter(function (i, el) {
                return $.mage.isEmpty(el.value);
            })
            .length;
        return empty === 0;
    }

    /**
     * Collection of validation rules including rules from additional-methods.js
     * @type {Object}
     */
    var rules = {
        "max-words": [
            function (value, element, params) {
                return this.optional(element) || $.mage.stripHtml(value).match(/\b\w+\b/g).length < params;
            },
            'Please enter {0} words or less.'
        ],
        "min-words": [
            function (value, element, params) {
                return this.optional(element) || $.mage.stripHtml(value).match(/\b\w+\b/g).length >= params;
            },
            'Please enter at least {0} words.'
        ],
        "range-words": [
            function (value, element, params) {
                return this.optional(element) ||
                    $.mage.stripHtml(value).match(/\b\w+\b/g).length >= params[0] &&
                    value.match(/bw+b/g).length < params[1];
            },
            'Please enter between {0} and {1} words.'
        ],
        "letters-with-basic-punc": [
            function (value, element) {
                return this.optional(element) || /^[a-z\-.,()'\"\s]+$/i.test(value);
            },
            'Letters or punctuation only please'
        ],
        "alphanumeric": [
            function (value, element) {
                return this.optional(element) || /^\w+$/i.test(value);
            },
            'Letters, numbers, spaces or underscores only please'
        ],
        "letters-only": [
            function (value, element) {
                return this.optional(element) || /^[a-z]+$/i.test(value);
            },
            'Letters only please'
        ],
        "no-whitespace": [
            function (value, element) {
                return this.optional(element) || /^\S+$/i.test(value);
            },
            'No white space please'
        ],
        "zip-range": [
            function (value, element) {
                return this.optional(element) || /^90[2-5]-\d{2}-\d{4}$/.test(value);
            },
            'Your ZIP-code must be in the range 902xx-xxxx to 905-xx-xxxx'
        ],
        "integer": [
            function (value, element) {
                return this.optional(element) || /^-?\d+$/.test(value);
            },
            'A positive or negative non-decimal number please'
        ],
        "vinUS": [
            function (v) {
                if (v.length !== 17) {
                    return false;
                }
                var i, n, d, f, cd, cdv;
                var LL = ["A", "B", "C", "D", "E", "F", "G", "H", "J", "K", "L", "M", "N", "P", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"];
                var VL = [1, 2, 3, 4, 5, 6, 7, 8, 1, 2, 3, 4, 5, 7, 9, 2, 3, 4, 5, 6, 7, 8, 9];
                var FL = [8, 7, 6, 5, 4, 3, 2, 10, 0, 9, 8, 7, 6, 5, 4, 3, 2];
                var rs = 0;
                for (i = 0; i < 17; i++) {
                    f = FL[i];
                    d = v.slice(i, i + 1);
                    if (i === 8) {
                        cdv = d;
                    }
                    if (!isNaN(d)) {
                        d *= f;
                    } else {
                        for (n = 0; n < LL.length; n++) {
                            if (d.toUpperCase() === LL[n]) {
                                d = VL[n];
                                d *= f;
                                if (isNaN(cdv) && n === 8) {
                                    cdv = LL[n];
                                }
                                break;
                            }
                        }
                    }
                    rs += d;
                }
                cd = rs % 11;
                if (cd === 10) {
                    cd = "X";
                }
                if (cd === cdv) {
                    return true;
                }
                return false;
            },
            'The specified vehicle identification number (VIN) is invalid.'
        ],
        "dateITA": [
            function (value, element) {
                var check = false;
                var re = /^\d{1,2}\/\d{1,2}\/\d{4}$/;
                if (re.test(value)) {
                    var adata = value.split('/');
                    var gg = parseInt(adata[0], 10);
                    var mm = parseInt(adata[1], 10);
                    var aaaa = parseInt(adata[2], 10);
                    var xdata = new Date(aaaa, mm - 1, gg);
                    if ((xdata.getFullYear() === aaaa) &&
                        (xdata.getMonth() === mm - 1) && (xdata.getDate() === gg )) {
                        check = true;
                    } else {
                        check = false;
                    }
                } else {
                    check = false;
                }
                return this.optional(element) || check;
            },
            'Please enter a correct date'
        ],
        "dateNL": [
            function (value, element) {
                return this.optional(element) || /^\d\d?[\.\/-]\d\d?[\.\/-]\d\d\d?\d?$/.test(value);
            },
            'Vul hier een geldige datum in.'
        ],
        "time": [
            function (value, element) {
                return this.optional(element) || /^([01]\d|2[0-3])(:[0-5]\d){0,2}$/.test(value);
            },
            'Please enter a valid time, between 00:00 and 23:59'
        ],
        "time12h": [
            function (value, element) {
                return this.optional(element) || /^((0?[1-9]|1[012])(:[0-5]\d){0,2}(\ [AP]M))$/i.test(value);
            },
            'Please enter a valid time, between 00:00 am and 12:00 pm'
        ],
        "phoneUS": [
            function (phone_number, element) {
                phone_number = phone_number.replace(/\s+/g, "");
                return this.optional(element) || phone_number.length > 9 &&
                    phone_number.match(/^(1-?)?(\([2-9]\d{2}\)|[2-9]\d{2})-?[2-9]\d{2}-?\d{4}$/);
            },
            'Please specify a valid phone number'
        ],
        "phoneUK": [
            function (phone_number, element) {
                return this.optional(element) || phone_number.length > 9 &&
                    phone_number.match(/^(\(?(0|\+44)[1-9]{1}\d{1,4}?\)?\s?\d{3,4}\s?\d{3,4})$/);
            },
            'Please specify a valid phone number'
        ],
        "mobileUK": [
            function (phone_number, element) {
                return this.optional(element) || phone_number.length > 9 &&
                    phone_number.match(/^((0|\+44)7(5|6|7|8|9){1}\d{2}\s?\d{6})$/);
            },
            'Please specify a valid mobile number'
        ],
        "stripped-min-length": [
            function (value, element, param) {
                return $(value).text().length >= param;
            },
            'Please enter at least {0} characters'
        ],
        "email2": [
            function (value, element) {
                return this.optional(element) || /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)*(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i.test(value);
            },
            $.validator.messages.email
        ],
        "url2": [
            function (value, element) {
                return this.optional(element) || /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)*(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(value);
            },
            $.validator.messages.url
        ],
        "credit-card-types": [
            function (value, element, param) {
                if (/[^0-9-]+/.test(value)) {
                    return false;
                }
                value = value.replace(/\D/g, "");

                var validTypes = 0x0000;

                if (param.mastercard) {
                    validTypes |= 0x0001;
                }
                if (param.visa) {
                    validTypes |= 0x0002;
                }
                if (param.amex) {
                    validTypes |= 0x0004;
                }
                if (param.dinersclub) {
                    validTypes |= 0x0008;
                }
                if (param.enroute) {
                    validTypes |= 0x0010;
                }
                if (param.discover) {
                    validTypes |= 0x0020;
                }
                if (param.jcb) {
                    validTypes |= 0x0040;
                }
                if (param.unknown) {
                    validTypes |= 0x0080;
                }
                if (param.all) {
                    validTypes = 0x0001 | 0x0002 | 0x0004 | 0x0008 | 0x0010 | 0x0020 | 0x0040 | 0x0080;
                }
                if (validTypes & 0x0001 && /^(51|52|53|54|55)/.test(value)) { //mastercard
                    return value.length === 16;
                }
                if (validTypes & 0x0002 && /^(4)/.test(value)) { //visa
                    return value.length === 16;
                }
                if (validTypes & 0x0004 && /^(34|37)/.test(value)) { //amex
                    return value.length === 15;
                }
                if (validTypes & 0x0008 && /^(300|301|302|303|304|305|36|38)/.test(value)) { //dinersclub
                    return value.length === 14;
                }
                if (validTypes & 0x0010 && /^(2014|2149)/.test(value)) { //enroute
                    return value.length === 15;
                }
                if (validTypes & 0x0020 && /^(6011)/.test(value)) { //discover
                    return value.length === 16;
                }
                if (validTypes & 0x0040 && /^(3)/.test(value)) { //jcb
                    return value.length === 16;
                }
                if (validTypes & 0x0040 && /^(2131|1800)/.test(value)) { //jcb
                    return value.length === 15;
                }
                if (validTypes & 0x0080) { //unknown
                    return true;
                }
                return false;
            },
            'Please enter a valid credit card number.'
        ],
        "ipv4": [
            function (value, element) {
                return this.optional(element) || /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/i.test(value);
            },
            'Please enter a valid IP v4 address.'
        ],
        "ipv6": [
            function (value, element) {
                return this.optional(element) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
            },
            'Please enter a valid IP v6 address.'
        ],
        "pattern": [
            function (value, element, param) {
                return this.optional(element) || param.test(value);
            },
            'Invalid format.'
        ],
        "allow-container-className": [
            function (element) {
                if (element.type === 'radio' || element.type === 'checkbox') {
                    return $(element).hasClass('change-container-classname');
                }
            },
            ''
        ],
        "validate-no-html-tags": [
            function (value) {
                return !/<(\/)?\w+/.test(value);
            },
            'HTML tags are not allowed.'
        ],
        "validate-select": [
            function (value) {
                return ((value !== "none") && (value != null) && (value.length !== 0));
            },
            'Please select an option.'
        ],
        "validate-no-empty": [
            function (value) {
                return !$.mage.isEmpty(value);
            },
            'Empty Value.'
        ],
        "validate-alphanum-with-spaces": [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^[a-zA-Z0-9 ]+$/.test(v);
            },
            'Please use only letters (a-z or A-Z), numbers (0-9) or spaces only in this field.'
        ],
        "validate-data": [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^[A-Za-z]+[A-Za-z0-9_]+$/.test(v);
            },
            'Please use only letters (a-z or A-Z), numbers (0-9) or underscore (_) in this field, and the first character should be a letter.'
        ],
        "validate-street": [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^[ \w]{3,}([A-Za-z]\.)?([ \w]*\#\d+)?(\r\n| )[ \w]{3,}/.test(v);
            },
            'Please use only letters (a-z or A-Z), numbers (0-9), spaces and "#" in this field.'
        ],
        "validate-phoneStrict": [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^(\()?\d{3}(\))?(-|\s)?\d{3}(-|\s)\d{4}$/.test(v);
            },
            'Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.'
        ],
        "validate-phoneLax": [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^((\d[\-. ]?)?((\(\d{3}\))|\d{3}))?[\-. ]?\d{3}[\-. ]?\d{4}$/.test(v);
            },
            'Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.'
        ],
        "validate-fax": [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^(\()?\d{3}(\))?(-|\s)?\d{3}(-|\s)\d{4}$/.test(v);
            },
            'Please enter a valid fax number (Ex: 123-456-7890).'
        ],
        "validate-email": [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(v);
            },
            'Please enter a valid email address (Ex: johndoe@domain.com).'
        ],
        "validate-emailSender": [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^[\S ]+$/.test(v);
            },
            'Please enter a valid email address (Ex: johndoe@domain.com).'
        ],
        "validate-password": [
            function (v) {
                if (v == null) {
                    return false;
                }
                /*strip leading and trailing spaces*/
                var pass = $.trim(v);
                if (!pass.length) {
                    return true;
                }
                return !(pass.length > 0 && pass.length < 6);
            },
            'Please enter 6 or more characters. Leading and trailing spaces will be ignored.'
        ],
        "validate-admin-password": [
            function (v) {
                if (v == null) {
                    return false;
                }
                var pass = $.trim(v);
                /*strip leading and trailing spaces*/
                if (0 === pass.length) {
                    return true;
                }
                if (!(/[a-z]/i.test(v)) || !(/[0-9]/.test(v))) {
                    return false;
                }
                if (pass.length < 7) {
                    return false;
                }
                return true;
            },
            'Please enter 7 or more characters, using both numeric and alphabetic.'
        ],
        "validate-customer-password": [
            function (v, elm) {
                var validator = this,
                    length = 0,
                    counter = 0;
                var passwordMinLength = $(elm).data('password-min-length');
                var passwordMinCharacterSets = $(elm).data('password-min-character-sets');
                var pass = $.trim(v);
                var result = pass.length >= passwordMinLength;
                if (result == false) {
                    validator.passwordErrorMessage = $.mage.__(
                        "Minimum length of this field must be equal or greater than %1 symbols." +
                        " Leading and trailing spaces will be ignored."
                    ).replace('%1', passwordMinLength);
                    return result;
                }
                if (pass.match(/\d+/)) {
                    counter ++;
                }
                if (pass.match(/[a-z]+/)) {
                    counter ++;
                }
                if (pass.match(/[A-Z]+/)) {
                    counter ++;
                }
                if (pass.match(/[^a-zA-Z0-9]+/)) {
                    counter ++;
                }
                if (counter < passwordMinCharacterSets) {
                    result = false;
                    validator.passwordErrorMessage = $.mage.__(
                        "Minimum of different classes of characters in password is %1." +
                        " Classes of characters: Lower Case, Upper Case, Digits, Special Characters."
                    ).replace('%1', passwordMinCharacterSets);
                }
                return result;
            }, function () {
                return this.passwordErrorMessage;
            }
        ],
        "validate-url": [
            function (v) {
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }
                v = (v || '').replace(/^\s+/, '').replace(/\s+$/, '');
                return (/^(http|https|ftp):\/\/(([A-Z0-9]([A-Z0-9_-]*[A-Z0-9]|))(\.[A-Z0-9]([A-Z0-9_-]*[A-Z0-9]|))*)(:(\d+))?(\/[A-Z0-9~](([A-Z0-9_~-]|\.)*[A-Z0-9~]|))*\/?(.*)?$/i).test(v);

            },
            'Please enter a valid URL. Protocol is required (http://, https:// or ftp://).'
        ],
        "validate-clean-url": [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^(http|https|ftp):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+.(com|org|net|dk|at|us|tv|info|uk|co.uk|biz|se)$)(:(\d+))?\/?/i.test(v) || /^(www)((\.[A-Z0-9][A-Z0-9_-]*)+.(com|org|net|dk|at|us|tv|info|uk|co.uk|biz|se)$)(:(\d+))?\/?/i.test(v);

            },
            'Please enter a valid URL. For example http://www.example.com or www.example.com.'
        ],
        "validate-xml-identifier": [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^[A-Z][A-Z0-9_\/-]*$/i.test(v);

            },
            'Please enter a valid XML-identifier (Ex: something_1, block5, id-4).'
        ],
        "validate-ssn": [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^\d{3}-?\d{2}-?\d{4}$/.test(v);

            },
            'Please enter a valid social security number (Ex: 123-45-6789).'
        ],
        "validate-zip-us": [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /(^\d{5}$)|(^\d{5}-\d{4}$)/.test(v);

            },
            'Please enter a valid zip code (Ex: 90602 or 90602-1234).'
        ],
        "validate-date-au": [
            function (v) {
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }
                var regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
                if ($.mage.isEmpty(v) || !regex.test(v)) {
                    return false;
                }
                var d = new Date(v.replace(regex, '$2/$1/$3'));
                return parseInt(RegExp.$2, 10) === (1 + d.getMonth()) &&
                    parseInt(RegExp.$1, 10) === d.getDate() &&
                    parseInt(RegExp.$3, 10) === d.getFullYear();

            },
            'Please use this date format: dd/mm/yyyy. For example 17/03/2006 for the 17th of March, 2006.'
        ],
        "validate-currency-dollar": [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^\$?\-?([1-9]{1}[0-9]{0,2}(\,[0-9]{3})*(\.[0-9]{0,2})?|[1-9]{1}\d*(\.[0-9]{0,2})?|0(\.[0-9]{0,2})?|(\.[0-9]{1,2})?)$/.test(v);

            },
            'Please enter a valid $ amount. For example $100.00.'
        ],
        "validate-not-negative-number": [
            function (v) {
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }
                v = $.mage.parseNumber(v);
                return !isNaN(v) && v >= 0;

            },
            'Please enter a number 0 or greater in this field.'
        ],
        // validate-not-negative-number should be replaced in all places with this one and then removed
        "validate-zero-or-greater": [
            function (v) {
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }
                v = $.mage.parseNumber(v);
                return !isNaN(v) && v >= 0;

            },
            'Please enter a number 0 or greater in this field.'
        ],
        "validate-greater-than-zero": [
            function (v) {
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }
                v = $.mage.parseNumber(v);
                return !isNaN(v) && v > 0;
            },
            'Please enter a number greater than 0 in this field.'
        ],
        "validate-css-length": [
            function (v) {
                if (v !== '') {
                    return (/^[0-9]*\.*[0-9]+(px|pc|pt|ex|em|mm|cm|in|%)?$/).test(v);
                }
                return true;
            },
            'Please input a valid CSS-length (Ex: 100px, 77pt, 20em, .5ex or 50%).'
        ],
        /** @description Additional methods */
        "validate-number": [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || (!isNaN($.mage.parseNumber(v)) && /^\s*-?\d*(\.\d*)?\s*$/.test(v));
            },
            'Please enter a valid number in this field.'
        ],
        "required-number": [
            function (v) {
                return !!v.length;
            },
            'Please enter a valid number in this field.'
        ],
        "validate-number-range": [
            function (v, elm, param) {
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }

                var numValue = $.mage.parseNumber(v);
                if (isNaN(numValue)) {
                    return false;
                }

                var dataAttrRange = /^(-?[\d.,]+)?-(-?[\d.,]+)?$/,
                    classNameRange = /^number-range-(-?[\d.,]+)?-(-?[\d.,]+)?$/,
                    result = true,
                    range, m, classes, ii;

                range = param;
                if (typeof range === 'object') {
                    m = dataAttrRange.exec(range);
                    if (m) {
                        result = result && $.mage.isBetween(numValue, m[1], m[2]);
                    }
                } else if (elm && elm.className) {
                    classes = elm.className.split(" ");
                    ii = classes.length;

                    while (ii--) {
                        range = classes[ii];
                        m = classNameRange.exec(range);
                        if (m) {
                            result = result && $.mage.isBetween(numValue, m[1], m[2]);
                            break;
                        }
                    }
                }

                return result;
            },
            'The value is not within the specified range.',
            true
        ],
        "validate-digits": [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || !/[^\d]/.test(v);
            },
            'Please enter a valid number in this field.'
        ],
        "validate-digits-range": [
            function (v, elm, param) {
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }

                var numValue = $.mage.parseNumber(v);
                if (isNaN(numValue)) {
                    return false;
                }

                var dataAttrRange = /^(-?\d+)?-(-?\d+)?$/,
                    classNameRange = /^digits-range-(-?\d+)?-(-?\d+)?$/,
                    result = true,
                    range, m, classes, ii;
                range = param;

                if (typeof range === 'object') {
                    m = dataAttrRange.exec(range);
                    if (m) {
                        result = result && $.mage.isBetween(numValue, m[1], m[2]);
                    }
                } else if (elm && elm.className) {
                    classes = elm.className.split(" ");
                    ii = classes.length;

                    while (ii--) {
                        range = classes[ii];
                        m = classNameRange.exec(range);
                        if (m) {
                            result = result && $.mage.isBetween(numValue, m[1], m[2]);
                            break;
                        }
                    }
                }

                return result;
            },
            'The value is not within the specified range.',
            true
        ],
        'validate-range': [
            function (v, elm) {
                var minValue, maxValue;
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                } else if ($.validator.methods['validate-digits'] && $.validator.methods['validate-digits'](v)) {
                    minValue = maxValue = $.mage.parseNumber(v);
                } else {
                    var ranges = /^(-?\d+)?-(-?\d+)?$/.exec(v);

                    if (ranges) {
                        minValue = $.mage.parseNumber(ranges[1]);
                        maxValue = $.mage.parseNumber(ranges[2]);
                        if (minValue > maxValue) {
                            return false;
                        }
                    } else {
                        return false;
                    }
                }
                var reRange = /^range-(-?\d+)?-(-?\d+)?$/,
                    result = true;

                var values = $(elm).prop('class').split(" ");

                for (var i = values.length - 1; i >= 0; i--) {
                    var name = values[i];
                    var validRange = reRange.exec(name);
                    if (validRange) {
                        var minValidRange = $.mage.parseNumber(validRange[1]);
                        var maxValidRange = $.mage.parseNumber(validRange[2]);
                        result = result &&
                        (isNaN(minValidRange) || minValue >= minValidRange) &&
                        (isNaN(maxValidRange) || maxValue <= maxValidRange);
                    }
                }
                return result;
            },
            'The value is not within the specified range.'
        ],
        "validate-alpha": [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^[a-zA-Z]+$/.test(v);
            },
            'Please use letters only (a-z or A-Z) in this field.'
        ],
        "validate-code": [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^[a-z]+[a-z0-9_]+$/.test(v);
            },
            'Please use only letters (a-z), numbers (0-9) or underscore (_) in this field, and the first character should be a letter.'
        ],
        "validate-alphanum": [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^[a-zA-Z0-9]+$/.test(v);
            },
            'Please use only letters (a-z or A-Z) or numbers (0-9) in this field. No spaces or other characters are allowed.'
        ],
        "validate-date": [
            function (v) {
                var test = new Date(v);
                return $.mage.isEmptyNoTrim(v) || !isNaN(test);
            }, 'Please enter a valid date.'

        ],
        "validate-date-range": [
            function (v, elm) {
                var m = /\bdate-range-(\w+)-(\w+)\b/.exec(elm.className);
                if (!m || m[2] === 'to' || $.mage.isEmptyNoTrim(v)) {
                    return true;
                }

                var currentYear = new Date().getFullYear() + '';
                var normalizedTime = function (v) {
                    v = v.split(/[.\/]/);
                    if (v[2] && v[2].length < 4) {
                        v[2] = currentYear.substr(0, v[2].length) + v[2];
                    }
                    return new Date(v.join('/')).getTime();
                };

                var dependentElements = $(elm.form).find('.validate-date-range.date-range-' + m[1] + '-to');
                return !dependentElements.length || $.mage.isEmptyNoTrim(dependentElements[0].value) ||
                    normalizedTime(v) <= normalizedTime(dependentElements[0].value);
            },
            'Make sure the To Date is later than or the same as the From Date.'
        ],
        "validate-cpassword": [
            function () {
                var conf = $('#confirmation').length > 0 ? $('#confirmation') : $($('.validate-cpassword')[0]);
                var pass = false;
                if ($('#password')) {
                    pass = $('#password');
                }
                var passwordElements = $('.validate-password');
                for (var i = 0; i < passwordElements.length; i++) {
                    var passwordElement = $(passwordElements[i]);
                    if (passwordElement.closest('form').attr('id') === conf.closest('form').attr('id')) {
                        pass = passwordElement;
                    }
                }
                if ($('.validate-admin-password').length) {
                    pass = $($('.validate-admin-password')[0]);
                }
                return (pass.val() === conf.val());
            },
            'Please make sure your passwords match.'
        ],
        "validate-identifier": [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/.test(v);
            },
            'Please enter a valid URL Key (Ex: "example-page", "example-page.html" or "anotherlevel/example-page").'
        ],
        "validate-zip-international": [
            /*function(v) {
             // @TODO: Cleanup
             return Validation.get('IsEmpty').test(v) || /(^[A-z0-9]{2,10}([\s]{0,1}|[\-]{0,1})[A-z0-9]{2,10}$)/.test(v);
             }*/
            function () {
                return true;
            },
            'Please enter a valid zip code.'
        ],
        "validate-one-required": [
            function (v, elm) {
                var p = $(elm).parent();
                var options = p.find('input');
                return options.map(function (elm) {
                        return $(elm).val();
                    }).length > 0;
            },
            'Please select one of the options above.'
        ],
        "validate-state": [
            function (v) {
                return (v !== 0 || v === '');
            },
            'Please select State/Province.'
        ],
        "required-file": [
            function (v, elm) {
                var result = !$.mage.isEmptyNoTrim(v);
                if (!result) {
                    var ovId = $(elm).attr('id') + '_value';
                    if ($(ovId)) {
                        result = !$.mage.isEmptyNoTrim($(ovId).val());
                    }
                }
                return result;
            },
            'Please select a file.'
        ],
        "validate-ajax-error": [
            function (v, element) {
                element = $(element);
                element.on('change.ajaxError', function () {
                    element.removeClass('validate-ajax-error');
                    element.off('change.ajaxError');
                });
                return !element.hasClass('validate-ajax-error');
            },
            ''
        ],
        "validate-optional-datetime": [
            function (v, elm, param) {
                var dateTimeParts = $('.datetime-picker[id^="options_' + param + '"]'),
                    hasWithValue = false, hasWithNoValue = false,
                    pattern = /day_part$/i;
                for (var i = 0; i < dateTimeParts.length; i++) {
                    if (!pattern.test($(dateTimeParts[i]).attr('id'))) {
                        if ($(dateTimeParts[i]).val() === "") {
                            hasWithValue = true;
                        } else {
                            hasWithNoValue = true;
                        }
                    }
                }
                return hasWithValue ^ hasWithNoValue;
            },
            'The field isn\'t complete.'
        ],
        "validate-required-datetime": [
            function (v, elm, param) {
                var dateTimeParts = $('.datetime-picker[id^="options_' + param + '"]');
                for (var i = 0; i < dateTimeParts.length; i++) {
                    if (dateTimeParts[i].value === "") {
                        return false;
                    }
                }
                return true;
            },
            'This is a required field.'
        ],
        "validate-one-required-by-name": [
            function (v, elm, selector) {
                var name = elm.name.replace(/([\\"])/g, '\\$1'),
                    container = this.currentForm,
                    selector = selector === true ? 'input[name="' + name + '"]:checked' : selector;

                return !!container.querySelectorAll(selector).length;
            },
            'Please select one of the options.'
        ],
        "less-than-equals-to": [
            function (value, element, params) {
                if ($.isNumeric($(params).val()) && $.isNumeric(value)) {
                    this.lteToVal = $(params).val();
                    return parseFloat(value) <= parseFloat($(params).val());
                }
                return true;
            },
            function () {
                var message = $.mage.__('Please enter a value less than or equal to %s.');
                return message.replace('%s', this.lteToVal);
            }
        ],
        "greater-than-equals-to": [
            function (value, element, params) {
                if ($.isNumeric($(params).val()) && $.isNumeric(value)) {
                    this.gteToVal = $(params).val();
                    return parseFloat(value) >= parseFloat($(params).val());
                }
                return true;
            },
            function () {
                var message = $.mage.__('Please enter a value greater than or equal to %s.');
                return message.replace('%s', this.gteToVal);
            }
        ],
        "validate-emails": [
            function (value) {
                if ($.mage.isEmpty(value)) {
                    return true;
                }
                var valid_regexp = /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i,
                    emails = value.split(/[\s\n\,]+/g);
                for (var i = 0; i < emails.length; i++) {
                    if (!valid_regexp.test(emails[i].trim())) {
                        return false;
                    }
                }
                return true;
            }, "Please enter valid email addresses, separated by commas. For example, johndoe@domain.com, johnsmith@domain.com."
        ],

        "validate-cc-type-select": [
            /**
             * Validate credit card type matches credit card number
             * @param value - select credit card type
             * @param element - element contains the select box for credit card types
             * @param params - selector for credit card number
             * @return {boolean}
             */
                function (value, element, params) {
                if (value && params && creditCartTypes[value]) {
                    return creditCartTypes[value][0].test($(params).val().replace(/\s+/g, ''));
                }
                return false;
            }, 'Card type does not match credit card number.'
        ],
        "validate-cc-number": [
            /**
             * Validate credit card number based on mod 10
             * @param value - credit card number
             * @return {boolean}
             */
                function (value) {
                if (value) {
                    return validateCreditCard(value);
                }
                return false;
            }, 'Please enter a valid credit card number.'
        ],
        "validate-cc-type": [
            /**
             * Validate credit card number is for the correct credit card type
             * @param value - credit card number
             * @param element - element contains credit card number
             * @param params - selector for credit card type
             * @return {boolean}
             */
                function (value, element, params) {
                if (value && params) {
                    var ccType = $(params).val();
                    value = value.replace(/\s/g, '').replace(/\-/g, '');
                    if (creditCartTypes[ccType] && creditCartTypes[ccType][0]) {
                        return creditCartTypes[ccType][0].test(value);
                    } else if (creditCartTypes[ccType] && !creditCartTypes[ccType][0]) {
                        return true;
                    }
                }
                return false;
            }, 'Credit card number does not match credit card type.'
        ],
        "validate-cc-exp": [
            /**
             * Validate credit card expiration date, make sure it's within the year and not before current month
             * @param value - month
             * @param element - element contains month
             * @param params - year selector
             * @return {Boolean}
             */
                function (value, element, params) {
                var isValid = false;
                if (value && params) {
                    var month = value,
                        year = $(params).val(),
                        currentTime = new Date(),
                        currentMonth = currentTime.getMonth() + 1,
                        currentYear = currentTime.getFullYear();
                    isValid = !year || year > currentYear || (year == currentYear && month >= currentMonth);
                }
                return isValid;
            }, 'Incorrect credit card expiration date.'
        ],
        "validate-cc-cvn": [
            /**
             * Validate credit card cvn based on credit card type
             * @param value - credit card cvn
             * @param element - element contains credit card cvn
             * @param params - credit card type selector
             * @return {*}
             */
                function (value, element, params) {
                if (value && params) {
                    var ccType = $(params).val();
                    if (creditCartTypes[ccType] && creditCartTypes[ccType][0]) {
                        return creditCartTypes[ccType][1].test(value);
                    }
                }
                return false;
            }, 'Please enter a valid credit card verification number.'
        ],
        "validate-cc-ukss": [
            /**
             * Validate Switch/Solo/Maestro issue number and start date is filled
             * @param value - input field value
             * @return {*}
             */
                function (value) {
                return value;
            }, 'Please enter issue number or start date for switch/solo card type.'
        ],

        "validate-length": [
            function (v, elm) {
                var reMax = new RegExp(/^maximum-length-[0-9]+$/),
                    reMin = new RegExp(/^minimum-length-[0-9]+$/),
                    validator = this,
                    result = true,
                    length = 0;
                $.each(elm.className.split(' '), function (index, name) {
                    if (name.match(reMax) && result) {
                        length = name.split('-')[2];
                        validator.attrLength = length;
                        result = (v.length <= length);
                    }
                    if (name.match(reMin) && result && $.mage.isEmpty(v)) {
                        length = name.split('-')[2];
                        result = v.length >= length;
                    }
                });
                return result;
            }, function () {
                return $.mage.__("Maximum length of this field must be equal or less than %1 symbols.")
                    .replace('%1', this.attrLength);
            }
        ],
        'required-entry': [
            function (value) {
                return !$.mage.isEmpty(value);
            }, $.mage.__('This is a required field.')
        ],
        'not-negative-amount': [
            function (v) {
                if (v.length)
                    return (/^\s*\d+([,.]\d+)*\s*%?\s*$/).test(v);
                else
                    return true;
            },
            'Please enter positive number in this field.'
        ],
        'validate-per-page-value-list': [
            function (v) {
                var isValid = !$.mage.isEmpty(v);
                var values = v.split(',');
                for (var i = 0; i < values.length; i++) {
                    if (!/^[0-9]+$/.test(values[i])) {
                        isValid = false;
                    }
                }
                return isValid;
            },
            'Please enter a valid value, ex: 10,20,30'
        ],
        'validate-per-page-value': [
            function (v, elm) {
                if ($.mage.isEmpty(v)) {
                    return false;
                }
                var values = $('#' + elm.id + '_values').val().split(',');
                return values.indexOf(v) != -1;
            },
            'Please enter a valid value from list'
        ],
        'validate-new-password': [
            function (v) {

                if ($.validator.methods['validate-password'] && !$.validator.methods['validate-password'](v)) {
                    return false;
                }
                if ($.mage.isEmpty(v) && v !== '') {
                    return false;
                }
                return true;
            },
            'Please enter 6 or more characters. Leading and trailing spaces will be ignored.'
        ],
        'required-if-not-specified': [
            function (value, element, params) {
                var valid = false;

                // if there is an alternate, determine its validity
                var alternate = $(params);
                if (alternate.length > 0) {
                    valid = this.check(alternate);
                    // if valid, it may be blank, so check for that
                    if (valid) {
                        var alternateValue = alternate.val();
                        if (typeof alternateValue == 'undefined' || alternateValue.length === 0) {
                            valid = false;
                        }
                    }
                }

                if (!valid)
                    valid = !this.optional(element);

                return valid;
            },
            'This is a required field.'
        ],
        'required-if-all-sku-empty-and-file-not-loaded': [
            function (value, element, params) {
                var valid = false;
                var alternate = $(params.specifiedId);

                if (alternate.length > 0) {
                    valid = this.check(alternate);
                    // if valid, it may be blank, so check for that
                    if (valid) {
                        var alternateValue = alternate.val();
                        if (typeof alternateValue == 'undefined' || alternateValue.length === 0) {
                            valid = false;
                        }
                    }
                }

                if (!valid)
                    valid = !this.optional(element);

                $('input[' + params.dataSku + '=true]').each(function () {
                    if ($(this).val() !== '') {
                        valid = true;
                    }
                });

                return valid;
            }, 'Please enter valid SKU key.'
        ],
        'required-if-specified': [
            function (value, element, params) {
                var valid = true;

                // if there is an dependent, determine its validity
                var dependent = $(params);
                if (dependent.length > 0) {
                    valid = this.check(dependent);
                    // if valid, it may be blank, so check for that
                    if (valid) {
                        var dependentValue = dependent.val();
                        valid = typeof dependentValue != 'undefined' && dependentValue.length > 0;
                    }
                }

                if (valid) {
                    valid = !this.optional(element);
                } else {
                    valid = true; // dependent was not valid, so don't even check
                }

                return valid;
            },
            'This is a required field.'
        ],
        'required-number-if-specified': [
            function (value, element, params) {
                var valid = true,
                    dependent = $(params),
                    depeValue;

                if (dependent.length) {
                    valid = this.check(dependent);

                    if (valid) {
                        depeValue = dependent[0].value;
                        valid = !!(depeValue && depeValue.length);
                    }
                }

                return valid ? !!value.length : true;
            },
            'Please enter a valid number.'
        ],
        'datetime-validation': [
            function (value, element) {
                var isValid = true;

                if ($(element).val().length === 0) {
                    isValid = false;
                    $(element).addClass('mage-error');
                }

                return isValid;
            },
            'This is required field'
        ],
        'required-text-swatch-entry': [
            tableSingleValidation,
            'Admin is a required field in the each row.'
        ],
        'required-visual-swatch-entry': [
            tableSingleValidation,
            'Admin is a required field in the each row.'
        ],
        'required-dropdown-attribute-entry': [
            tableSingleValidation,
            'Admin is a required field in the each row.'
        ],
        'validate-item-quantity': [
            function (value, element, params) {
                // obtain values for validation
                var qty = $.mage.parseNumber(value);

                // validate quantity
                var isMinAllowedValid = typeof params.minAllowed === 'undefined' || (qty >= $.mage.parseNumber(params.minAllowed));
                var isMaxAllowedValid = typeof params.maxAllowed === 'undefined' || (qty <= $.mage.parseNumber(params.maxAllowed));
                var isQtyIncrementsValid = typeof params.qtyIncrements === 'undefined' || (qty % $.mage.parseNumber(params.qtyIncrements) === 0);

                return isMaxAllowedValid && isMinAllowedValid && isQtyIncrementsValid && qty > 0;
            },
            ''
        ]
    };

    $.each(rules, function (i, rule) {
        rule.unshift(i);
        $.validator.addMethod.apply($.validator, rule);
    });
    $.validator.addClassRules({
        "required-option": {
            required: true
        },
        "required-options-count": {
            required: true
        },
        "validate-both-passwords": {
            'validate-cpassword': true
        }
    });
    $.validator.messages = $.extend($.validator.messages, {
        required: $.mage.__('This is a required field.')
    });

    if ($.metadata) {
        // Setting the type as html5 to enable data-validate attribute
        $.metadata.setType("html5");
    }

    var showLabel = $.validator.prototype.showLabel;
    $.extend(true, $.validator.prototype, {
        showLabel: function (element, message) {
            showLabel.call(this, element, message);

            // ARIA (adding aria-invalid & aria-describedby)
            var label = this.errorsFor(element),
                elem = $(element);

            if (!label.attr('id')) {
                label.attr('id', this.idOrName(element) + '-error');
            }
            elem.attr('aria-invalid', 'true')
                .attr('aria-describedby', label.attr('id'));
        }
    });

    /**
     * Validate form field without instantiating validate plug-in
     * @param {Element||String} element - DOM element or selector
     * @return {Boolean} validation result
     */
    $.validator.validateElement = function (element) {
        element = $(element);
        var form = element.get(0).form,
            validator = form ? $(form).data('validator') : null;
        if (validator) {
            return validator.element(element.get(0));
        } else {
            var valid = true,
                classes = element.prop('class').split(' ');
            $.each(classes, $.proxy(function (i, className) {
                if (this.methods[className] && !this.methods[className](element.val(), element.get(0))) {
                    valid = false;
                    return valid;
                }
            }, this));
            return valid;
        }
    };

    var originValidateDelegate = $.fn.validateDelegate;

    $.fn.validateDelegate = function () {
        if (!this[0].form) {
            return this;
        }

        return originValidateDelegate.apply(this, arguments);
    };

    /**
     * Validate single element.
     *
     * @param {Element} element
     * @returns {*}
     */
    $.validator.validateSingleElement = function (element) {
        var errors = {},
            valid = true,
            validateConfig = {
                errorElement: 'label',
                ignore: '.ignore-validate'
            },
            form, validator, classes;

        element = $(element).not(validateConfig.ignore);

        if (!element.length) {
            return true;
        }

        form = element.get(0).form;
        validator = form ? $(form).data('validator') : null;

        if (validator) {
            return validator.element(element.get(0));
        }

        classes = element.prop('class').split(' ');
        validator = element.parent().data('validator') ||
            $.mage.validation(validateConfig, element.parent()).validate;

        element.removeClass(validator.settings.errorClass);
        validator.toHide = validator.toShow;
        validator.hideErrors();
        validator.toShow = validator.toHide = $([]);

        $.each(classes, $.proxy(function (i, className) {
            if (this.methods[className] && !this.methods[className](element.val(), element.get(0))) {
                valid = false;
                errors[element.get(0).name] = this.messages[className];
                validator.invalid[element.get(0).name] = true;
                validator.showErrors(errors);

                return valid;
            }
        }, this));

        return valid;
    };

    $.widget("mage.validation", {
        options: {
            meta: "validate",
            onfocusout: false,
            onkeyup: false,
            onclick: false,
            ignoreTitle: true,
            errorClass: 'mage-error',
            errorElement: 'div',
            errorPlacement: function (error, element) {
                var errorPlacement = element;
                // logic for date-picker error placement
                if (element.hasClass('hasDatepicker')) {
                    errorPlacement = element.siblings('img');
                }
                // logic for field wrapper
                var fieldWrapper = element.closest('.addon');
                if (fieldWrapper.length) {
                    errorPlacement = fieldWrapper.after(error);
                }
                //logic for checkboxes/radio
                if (element.is(':checkbox') || element.is(':radio')) {
                    errorPlacement = element.siblings('label').last();
                }
                errorPlacement.after(error);
            }
        },
        /**
         * Check if form pass validation rules without submit
         * @return boolean
         */
        isValid: function () {
            return this.element.valid();
        },

        /**
         * Remove validation error messages
         */
        clearError: function () {
            if (arguments.length) {
                $.each(arguments, $.proxy(function (index, item) {
                    this.validate.prepareElement(item);
                    this.validate.hideErrors();
                }, this));
            } else {
                this.validate.resetForm();
            }
        },
        /**
         * Validation creation
         * @protected
         */
        _create: function () {
            this.validate = this.element.validate(this.options);

            // ARIA (adding aria-required attribute)
            this.element
                .find('.field.required')
                .find('.control')
                .find('input, select, textarea')
                .attr('aria-required', 'true');

            this._listenFormValidate();
        },
        /**
         * Validation listening
         * @protected
         */
        _listenFormValidate: function () {
            $('form').on('invalid-form.validate', function (event, validation) {
                var firstActive = $(validation.errorList[0].element || []),
                    lastActive = $(validation.findLastActive() || validation.errorList.length && validation.errorList[0].element || []);

                if (lastActive.is(':hidden')) {
                    var parent = lastActive.parent();
                    var windowHeight = $(window).height();
                    $('html, body').animate({
                        scrollTop: parent.offset().top - windowHeight / 2
                    });
                }

                // ARIA (removing aria attributes if success)
                var successList = validation.successList;
                if (successList.length) {
                    $.each(successList, function () {
                        $(this)
                            .removeAttr('aria-describedby')
                            .removeAttr('aria-invalid');
                    })
                }
                if (firstActive.length) {
                    firstActive.focus();
                }
            });
        }
    });

    return $.mage.validation;
}));
