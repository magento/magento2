/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

(function (factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'moment',
            'jquery-ui-modules/widget',
            'jquery/validate',
            'mage/translate'
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($, moment) {
    'use strict';

    var creditCartTypes, rules, showLabel, originValidateDelegate;

    $.extend(true, $, {
        // @TODO: Move methods 'isEmpty', 'isEmptyNoTrim', 'parseNumber', 'stripHtml' in file with utility functions
        mage: {
            /**
             * Check if string is empty with trim
             * @param {String} value
             */
            isEmpty: function (value) {
                return value === '' || value === undefined ||
                    value == null || value.length === 0 || /^\s+$/.test(value);
            },

            /**
             * Check if string is empty no trim
             * @param {String} value
             */
            isEmptyNoTrim: function (value) {
                return value === '' || value == null || value.length === 0;
            },

            /**
             * Checks if {value} is between numbers {from} and {to}
             * @param {String} value
             * @param {String} from
             * @param {String} to
             * @returns {Boolean}
             */
            isBetween: function (value, from, to) {
                return ($.mage.isEmpty(from) || value >= $.mage.parseNumber(from)) &&
                    ($.mage.isEmpty(to) || value <= $.mage.parseNumber(to));
            },

            /**
             * Parse price string
             * @param {String} value
             */
            parseNumber: function (value) {
                var isDot, isComa;

                if (typeof value !== 'string') {
                    return parseFloat(value);
                }
                isDot = value.indexOf('.');
                isComa = value.indexOf(',');

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
             *
             * @param {String} value - Value being stripped.
             * @return {String}
             */
            stripHtml: function (value) {
                return value.replace(/<.[^<>]*?>/g, ' ').replace(/&nbsp;|&#160;/gi, ' ')
                    .replace(/[0-9.(),;:!?%#$'"_+=\/-]*/g, '');
            }
        }
    });

    /**
     * @param {String} name
     * @param {*} method
     * @param {*} message
     * @param {*} dontSkip
     */
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
    creditCartTypes = {
        'SO': [
            new RegExp('^(6334[5-9]([0-9]{11}|[0-9]{13,14}))|(6767([0-9]{12}|[0-9]{14,15}))$'),
            new RegExp('^([0-9]{3}|[0-9]{4})?$'),
            true
        ],
        'SM': [
            new RegExp('(^(5[0678])[0-9]{11,18}$)|(^(6[^05])[0-9]{11,18}$)|' +
                '(^(601)[^1][0-9]{9,16}$)|(^(6011)[0-9]{9,11}$)|(^(6011)[0-9]{13,16}$)|' +
                '(^(65)[0-9]{11,13}$)|(^(65)[0-9]{15,18}$)|(^(49030)[2-9]([0-9]{10}$|[0-9]{12,13}$))|' +
                '(^(49033)[5-9]([0-9]{10}$|[0-9]{12,13}$))|(^(49110)[1-2]([0-9]{10}$|[0-9]{12,13}$))|' +
                '(^(49117)[4-9]([0-9]{10}$|[0-9]{12,13}$))|(^(49118)[0-2]([0-9]{10}$|[0-9]{12,13}$))|' +
                '(^(4936)([0-9]{12}$|[0-9]{14,15}$))'), new RegExp('^([0-9]{3}|[0-9]{4})?$'),
            true
        ],
        'VI': [new RegExp('^4[0-9]{12}([0-9]{3})?$'), new RegExp('^[0-9]{3}$'), true],
        'MC': [
            new RegExp('^(?:5[1-5][0-9]{2}|222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720)[0-9]{12}$'),
            new RegExp('^[0-9]{3}$'),
            true
        ],
        'AE': [new RegExp('^3[47][0-9]{13}$'), new RegExp('^[0-9]{4}$'), true],
        'DI': [new RegExp('^(6011(0|[2-4]|74|7[7-9]|8[6-9]|9)|6(4[4-9]|5))\\d*$'), new RegExp('^[0-9]{3}$'), true],
        'JCB': [new RegExp('^35(2[8-9]|[3-8])\\d*$'), new RegExp('^[0-9]{3}$'), true],
        'DN': [new RegExp('^(3(0[0-5]|095|6|[8-9]))\\d*$'), new RegExp('^[0-9]{3}$'), true],
        'UN': [
            new RegExp('^(622(1(2[6-9]|[3-9])|[3-8]|9([[0-1]|2[0-5]))|62[4-6]|628([2-8]))\\d*?$'),
            new RegExp('^[0-9]{3}$'),
            true
        ],
        'MI': [new RegExp('^(5(0|[6-9])|63|67(?!59|6770|6774))\\d*$'), new RegExp('^[0-9]{3}$'), true],
        'MD': [new RegExp('^6759(?!24|38|40|6[3-9]|70|76)|676770|676774\\d*$'), new RegExp('^[0-9]{3}$'), true]
    };

    /**
     * validate credit card number using mod10
     * @param {String} s
     * @return {Boolean}
     */
    function validateCreditCard(s) {
        // remove non-numerics
        var v = '0123456789',
            w = '',
            i, j, k, m, c, a, x;

        for (i = 0; i < s.length; i++) {
            x = s.charAt(i);

            if (v.indexOf(x, 0) !== -1) {
                w += x;
            }
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

        return c % 10 === 0;
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
                if ($(el).is('disabled')) {
                    return $.mage.isEmpty(el.value);
                }
            })
            .length;

        return empty === 0;
    }

    /**
     *
     * @param {float} qty
     * @param {float} qtyIncrements
     * @returns {float}
     */
    function resolveModulo(qty, qtyIncrements) {
        while (qtyIncrements < 1) {
            qty *= 10;
            qtyIncrements *= 10;
        }

        return qty % qtyIncrements;
    }

    /**
     * Collection of validation rules including rules from additional-methods.js
     * @type {Object}
     */
    rules = {
        'max-words': [
            function (value, element, params) {
                return this.optional(element) || $.mage.stripHtml(value).match(/\b\w+\b/g).length <= params;
            },
            $.mage.__('Please enter {0} words or less.')
        ],
        'min-words': [
            function (value, element, params) {
                return this.optional(element) || $.mage.stripHtml(value).match(/\b\w+\b/g).length >= params;
            },
            $.mage.__('Please enter at least {0} words.')
        ],
        'range-words': [
            function (value, element, params) {
                return this.optional(element) ||
                    $.mage.stripHtml(value).match(/\b\w+\b/g).length >= params[0] &&
                    value.match(/bw+b/g).length < params[1];
            },
            $.mage.__('Please enter between {0} and {1} words.')
        ],
        'letters-with-basic-punc': [
            function (value, element) {
                return this.optional(element) || /^[a-z\-.,()'\"\s]+$/i.test(value);
            },
            $.mage.__('Letters or punctuation only please')
        ],
        'alphanumeric': [
            function (value, element) {
                return this.optional(element) || /^\w+$/i.test(value);
            },
            $.mage.__('Letters, numbers, spaces or underscores only please')
        ],
        'letters-only': [
            function (value, element) {
                return this.optional(element) || /^[a-z]+$/i.test(value);
            },
            $.mage.__('Letters only please')
        ],
        'no-whitespace': [
            function (value, element) {
                return this.optional(element) || /^\S+$/i.test(value);
            },
            $.mage.__('No white space please')
        ],
        'no-marginal-whitespace': [
            function (value, element) {
                return this.optional(element) || !/^\s+|\s+$/i.test(value);
            },
            $.mage.__('No marginal white space please')
        ],
        'zip-range': [
            function (value, element) {
                return this.optional(element) || /^90[2-5]-\d{2}-\d{4}$/.test(value);
            },
            $.mage.__('Your ZIP-code must be in the range 902xx-xxxx to 905-xx-xxxx')
        ],
        'integer': [
            function (value, element) {
                return this.optional(element) || /^-?\d+$/.test(value);
            },
            $.mage.__('A positive or negative non-decimal number please')
        ],
        'vinUS': [
            function (v) {
                var i, n, d, f, cd, cdv, LL, VL, FL, rs;

                /* eslint-disable max-depth */
                if (v.length !== 17) {
                    return false;
                }

                LL = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L',
                    'M', 'N', 'P', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
                VL = [1, 2, 3, 4, 5, 6, 7, 8, 1, 2, 3, 4, 5, 7, 9, 2, 3, 4, 5, 6, 7, 8, 9];
                FL = [8, 7, 6, 5, 4, 3, 2, 10, 0, 9, 8, 7, 6, 5, 4, 3, 2];
                rs = 0;

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

                /* eslint-enable max-depth */
                cd = rs % 11;

                if (cd === 10) {
                    cd = 'X';
                }

                if (cd === cdv) {
                    return true;
                }

                return false;
            },
            $.mage.__('The specified vehicle identification number (VIN) is invalid.')
        ],
        'dateITA': [
            function (value, element) {
                var check = false,
                    re = /^\d{1,2}\/\d{1,2}\/\d{4}$/,
                    adata, gg, mm, aaaa, xdata;

                if (re.test(value)) {
                    adata = value.split('/');
                    gg = parseInt(adata[0], 10);
                    mm = parseInt(adata[1], 10);
                    aaaa = parseInt(adata[2], 10);
                    xdata = new Date(aaaa, mm - 1, gg);

                    if (xdata.getFullYear() === aaaa &&
                        xdata.getMonth() === mm - 1 &&
                        xdata.getDate() === gg
                    ) {
                        check = true;
                    } else {
                        check = false;
                    }
                } else {
                    check = false;
                }

                return this.optional(element) || check;
            },
            $.mage.__('Please enter a correct date')
        ],
        'dateNL': [
            function (value, element) {
                return this.optional(element) || /^\d\d?[\.\/-]\d\d?[\.\/-]\d\d\d?\d?$/.test(value);
            },
            'Vul hier een geldige datum in.'
        ],
        'time': [
            function (value, element) {
                return this.optional(element) || /^([01]\d|2[0-3])(:[0-5]\d){0,2}$/.test(value);
            },
            $.mage.__('Please enter a valid time, between 00:00 and 23:59')
        ],
        'time12h': [
            function (value, element) {
                return this.optional(element) || /^((0?[1-9]|1[012])(:[0-5]\d){0,2}(\s[AP]M))$/i.test(value);
            },
            $.mage.__('Please enter a valid time, between 00:00 am and 12:00 pm')
        ],
        'phoneUS': [
            function (phoneNumber, element) {
                phoneNumber = phoneNumber.replace(/\s+/g, '');

                return this.optional(element) || phoneNumber.length > 9 &&
                    phoneNumber.match(/^(1-?)?(\([2-9]\d{2}\)|[2-9]\d{2})-?[2-9]\d{2}-?\d{4}$/);
            },
            $.mage.__('Please specify a valid phone number')
        ],
        'phoneUK': [
            function (phoneNumber, element) {
                return this.optional(element) || phoneNumber.length > 9 &&
                    phoneNumber.match(/^(\(?(0|\+44)[1-9]{1}\d{1,4}?\)?\s?\d{3,4}\s?\d{3,4})$/);
            },
            $.mage.__('Please specify a valid phone number')
        ],
        'mobileUK': [
            function (phoneNumber, element) {
                return this.optional(element) || phoneNumber.length > 9 &&
                    phoneNumber.match(/^((0|\+44)7\d{3}\s?\d{6})$/);
            },
            $.mage.__('Please specify a valid mobile number')
        ],
        'stripped-min-length': [
            function (value, element, param) {
                return value.length >= param;
            },
            $.mage.__('Please enter at least {0} characters')
        ],

        /* detect chars that would require more than 3 bytes */
        'validate-no-utf8mb4-characters': [
            function (value) {
                var validator = this,
                    message = $.mage.__('Please remove invalid characters: {0}.'),
                    matches = value.match(/(?:[\uD800-\uDBFF][\uDC00-\uDFFF])/g),
                    result = matches === null;

                if (!result) {
                    validator.charErrorMessage = message.replace('{0}', matches.join());
                }

                return result;
            }, function () {
                return this.charErrorMessage;
            }
        ],

        /* eslint-disable max-len */
        'email2': [
            function (value, element) {
                return this.optional(element) ||
                    /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)*(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i.test(value);
            },
            $.validator.messages.email
        ],
        'url2': [
            function (value, element) {
                return this.optional(element) || /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)*(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(value);
            },
            $.validator.messages.url
        ],

        /* eslint-enable max-len */
        'credit-card-types': [
            function (value, element, param) {
                var validTypes;

                if (/[^0-9-]+/.test(value)) {
                    return false;
                }
                value = value.replace(/\D/g, '');

                validTypes = 0x0000;

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
            $.mage.__('Please enter a valid credit card number.')
        ],

        /* eslint-disable max-len */
        'ipv4': [
            function (value, element) {
                return this.optional(element) ||
                    /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/i.test(value);
            },
            $.mage.__('Please enter a valid IP v4 address.')
        ],
        'ipv6': [
            function (value, element) {
                return this.optional(element) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
            },
            $.mage.__('Please enter a valid IP v6 address.')
        ],

        /* eslint-enable max-len */
        'pattern': [
            function (value, element, param) {
                return this.optional(element) || param.test(value);
            },
            $.mage.__('Invalid format.')
        ],
        'allow-container-className': [
            function (element) {
                if (element.type === 'radio' || element.type === 'checkbox') {
                    return $(element).hasClass('change-container-classname');
                }
            },
            ''
        ],
        'validate-no-html-tags': [
            function (value) {
                return !/<(\/)?\w+/.test(value);
            },
            $.mage.__('HTML tags are not allowed.')
        ],
        'validate-select': [
            function (value) {
                return value !== 'none' && value != null && value.length !== 0;
            },
            $.mage.__('Please select an option.')
        ],
        'validate-no-empty': [
            function (value) {
                return !$.mage.isEmpty(value);
            },
            $.mage.__('Empty Value.')
        ],
        'validate-alphanum-with-spaces': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^[a-zA-Z0-9 ]+$/.test(v);
            },
            $.mage.__('Please use only letters (a-z or A-Z), numbers (0-9) or spaces only in this field.')
        ],
        'validate-data': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^[A-Za-z]+[A-Za-z0-9_]+$/.test(v);
            },
            $.mage.__('Please use only letters (a-z or A-Z), numbers (0-9) or underscore (_) in this field, and the first character should be a letter.') //eslint-disable-line max-len
        ],
        'validate-street': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^[ \w]{3,}([A-Za-z]\.)?([ \w]*\#\d+)?(\r\n| )[ \w]{3,}/.test(v);
            },
            $.mage.__('Please use only letters (a-z or A-Z), numbers (0-9), spaces and "#" in this field.')
        ],
        'validate-phoneStrict': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^(\()?\d{3}(\))?(-|\s)?\d{3}(-|\s)\d{4}$/.test(v);
            },
            $.mage.__('Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.')
        ],
        'validate-phoneLax': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) ||
                    /^((\d[\-. ]?)?((\(\d{3}\))|\d{3}))?[\-. ]?\d{3}[\-. ]?\d{4}$/.test(v);
            },
            $.mage.__('Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.')
        ],
        'validate-fax': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^(\()?\d{3}(\))?(-|\s)?\d{3}(-|\s)\d{4}$/.test(v);
            },
            $.mage.__('Please enter a valid fax number (Ex: 123-456-7890).')
        ],
        'validate-email': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(v); //eslint-disable-line max-len
            },
            $.mage.__('Please enter a valid email address (Ex: johndoe@domain.com).')
        ],
        'validate-emailSender': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^[\S ]+$/.test(v);
            },
            $.mage.__('Please enter a valid email address (Ex: johndoe@domain.com).')
        ],
        'validate-password': [
            function (v) {
                var pass;

                if (v == null) {
                    return false;
                }
                //strip leading and trailing spaces
                pass = $.trim(v);

                if (!pass.length) {
                    return true;
                }

                return !(pass.length > 0 && pass.length < 6);
            },
            $.mage.__('Please enter 6 or more characters. Leading and trailing spaces will be ignored.')
        ],
        'validate-admin-password': [
            function (v) {
                var pass;

                if (v == null) {
                    return false;
                }
                pass = $.trim(v);
                // strip leading and trailing spaces
                if (pass.length === 0) {
                    return true;
                }

                if (!/[a-z]/i.test(v) || !/[0-9]/.test(v)) {
                    return false;
                }

                if (pass.length < 7) {
                    return false;
                }

                return true;
            },
            $.mage.__('Please enter 7 or more characters, using both numeric and alphabetic.')
        ],
        'validate-customer-password': [
            function (v, elm) {
                var validator = this,
                    counter = 0,
                    passwordMinLength = $(elm).data('password-min-length'),
                    passwordMinCharacterSets = $(elm).data('password-min-character-sets'),
                    pass = $.trim(v),
                    result = pass.length >= passwordMinLength;

                if (result === false) {
                    validator.passwordErrorMessage = $.mage.__('Minimum length of this field must be equal or greater than %1 symbols. Leading and trailing spaces will be ignored.').replace('%1', passwordMinLength); //eslint-disable-line max-len

                    return result;
                }

                if (pass.match(/\d+/)) {
                    counter++;
                }

                if (pass.match(/[a-z]+/)) {
                    counter++;
                }

                if (pass.match(/[A-Z]+/)) {
                    counter++;
                }

                if (pass.match(/[^a-zA-Z0-9]+/)) {
                    counter++;
                }

                if (counter < passwordMinCharacterSets) {
                    result = false;
                    validator.passwordErrorMessage = $.mage.__('Minimum of different classes of characters in password is %1. Classes of characters: Lower Case, Upper Case, Digits, Special Characters.').replace('%1', passwordMinCharacterSets); //eslint-disable-line max-len
                }

                return result;
            }, function () {
                return this.passwordErrorMessage;
            }
        ],
        'validate-url': [
            function (v) {
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }
                v = (v || '').replace(/^\s+/, '').replace(/\s+$/, '');

                return (/^(http|https|ftp):\/\/(([A-Z0-9]([A-Z0-9_-]*[A-Z0-9]|))(\.[A-Z0-9]([A-Z0-9_-]*[A-Z0-9]|))*)(:(\d+))?(\/[A-Z0-9~](([A-Z0-9_~-]|\.)*[A-Z0-9~]|))*\/?(.*)?$/i).test(v); //eslint-disable-line max-len

            },
            $.mage.__('Please enter a valid URL. Protocol is required (http://, https:// or ftp://).')
        ],
        'validate-clean-url': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^(http|https|ftp):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+.(com|org|net|dk|at|us|tv|info|uk|co.uk|biz|se)$)(:(\d+))?\/?/i.test(v) || /^(www)((\.[A-Z0-9][A-Z0-9_-]*)+.(com|org|net|dk|at|us|tv|info|uk|co.uk|biz|se)$)(:(\d+))?\/?/i.test(v); //eslint-disable-line max-len

            },
            $.mage.__('Please enter a valid URL. For example http://www.example.com or www.example.com.')
        ],
        'validate-xml-identifier': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^[A-Z][A-Z0-9_\/-]*$/i.test(v);

            },
            $.mage.__('Please enter a valid XML-identifier (Ex: something_1, block5, id-4).')
        ],
        'validate-ssn': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^\d{3}-?\d{2}-?\d{4}$/.test(v);

            },
            $.mage.__('Please enter a valid social security number (Ex: 123-45-6789).')
        ],
        'validate-zip-us': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /(^\d{5}$)|(^\d{5}-\d{4}$)/.test(v);

            },
            $.mage.__('Please enter a valid zip code (Ex: 90602 or 90602-1234).')
        ],
        'validate-date-au': [
            function (v) {
                var regex, d;

                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }
                regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;

                if ($.mage.isEmpty(v) || !regex.test(v)) {
                    return false;
                }
                d = new Date(v.replace(regex, '$2/$1/$3'));

                return parseInt(RegExp.$2, 10) === 1 + d.getMonth() &&
                    parseInt(RegExp.$1, 10) === d.getDate() &&
                    parseInt(RegExp.$3, 10) === d.getFullYear();

            },
            $.mage.__('Please use this date format: dd/mm/yyyy. For example 17/03/2006 for the 17th of March, 2006.')
        ],
        'validate-currency-dollar': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^\$?\-?([1-9]{1}[0-9]{0,2}(\,[0-9]{3})*(\.[0-9]{0,2})?|[1-9]{1}\d*(\.[0-9]{0,2})?|0(\.[0-9]{0,2})?|(\.[0-9]{1,2})?)$/.test(v); //eslint-disable-line max-len

            },
            $.mage.__('Please enter a valid $ amount. For example $100.00.')
        ],
        'validate-not-negative-number': [
            function (v) {
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }
                v = $.mage.parseNumber(v);

                return !isNaN(v) && v >= 0;

            },
            $.mage.__('Please enter a number 0 or greater in this field.')
        ],
        // validate-not-negative-number should be replaced in all places with this one and then removed
        'validate-zero-or-greater': [
            function (v) {
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }
                v = $.mage.parseNumber(v);

                return !isNaN(v) && v >= 0;

            },
            $.mage.__('Please enter a number 0 or greater in this field.')
        ],
        'validate-greater-than-zero': [
            function (v) {
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }
                v = $.mage.parseNumber(v);

                return !isNaN(v) && v > 0;
            },
            $.mage.__('Please enter a number greater than 0 in this field.')
        ],
        'validate-css-length': [
            function (v) {
                if (v !== '') {
                    return (/^[0-9]*\.*[0-9]+(px|pc|pt|ex|em|mm|cm|in|%)?$/).test(v);
                }

                return true;
            },
            $.mage.__('Please input a valid CSS-length (Ex: 100px, 77pt, 20em, .5ex or 50%).')
        ],
        // Additional methods
        'validate-number': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || !isNaN($.mage.parseNumber(v)) && /^\s*-?\d*(\.\d*)?\s*$/.test(v);
            },
            $.mage.__('Please enter a valid number in this field.')
        ],
        'required-number': [
            function (v) {
                return !!v.length;
            },
            $.mage.__('Please enter a valid number in this field.')
        ],
        'validate-number-range': [
            function (v, elm, param) {
                var numValue, dataAttrRange, classNameRange, result, range, m, classes, ii;

                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }

                numValue = $.mage.parseNumber(v);

                if (isNaN(numValue)) {
                    return false;
                }

                dataAttrRange = /^(-?[\d.,]+)?-(-?[\d.,]+)?$/;
                classNameRange = /^number-range-(-?[\d.,]+)?-(-?[\d.,]+)?$/;
                result = true;
                range = param;

                if (typeof range === 'string') {
                    m = dataAttrRange.exec(range);

                    if (m) {
                        result = result && $.mage.isBetween(numValue, m[1], m[2]);
                    } else {
                        result = false;
                    }
                } else if (elm && elm.className) {
                    classes = elm.className.split(' ');
                    ii = classes.length;

                    while (ii--) {
                        range = classes[ii];
                        m = classNameRange.exec(range);

                        if (m) { //eslint-disable-line max-depth
                            result = result && $.mage.isBetween(numValue, m[1], m[2]);
                            break;
                        }
                    }
                }

                return result;
            },
            $.mage.__('The value is not within the specified range.'),
            true
        ],
        'validate-digits': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || !/[^\d]/.test(v);
            },
            $.mage.__('Please enter a valid number in this field.')
        ],
        'validate-forbidden-extensions': [
            function (v, elem) {
                var forbiddenExtensions = $(elem).attr('data-validation-params'),
                    forbiddenExtensionsArray = forbiddenExtensions.split(','),
                    extensionsArray = v.split(','),
                    result = true;

                this.validateExtensionsMessage = $.mage.__('Forbidden extensions has been used. Avoid usage of ') +
                    forbiddenExtensions;

                $.each(extensionsArray, function (key, extension) {
                    if (forbiddenExtensionsArray.indexOf(extension) !== -1) {
                        result = false;
                    }
                });

                return result;
            }, function () {
                return this.validateExtensionsMessage;
            }
        ],
        'validate-digits-range': [
            function (v, elm, param) {
                var numValue, dataAttrRange, classNameRange, result, range, m, classes, ii;

                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }

                numValue = $.mage.parseNumber(v);

                if (isNaN(numValue)) {
                    return false;
                }

                dataAttrRange = /^(-?\d+)?-(-?\d+)?$/;
                classNameRange = /^digits-range-(-?\d+)?-(-?\d+)?$/;
                result = true;
                range = param;

                if (typeof range === 'string') {
                    m = dataAttrRange.exec(range);

                    if (m) {
                        result = result && $.mage.isBetween(numValue, m[1], m[2]);
                    } else {
                        result = false;
                    }
                } else if (elm && elm.className) {
                    classes = elm.className.split(' ');
                    ii = classes.length;

                    while (ii--) {
                        range = classes[ii];
                        m = classNameRange.exec(range);

                        if (m) { //eslint-disable-line max-depth
                            result = result && $.mage.isBetween(numValue, m[1], m[2]);
                            break;
                        }
                    }
                }

                return result;
            },
            $.mage.__('The value is not within the specified range.'),
            true
        ],
        'validate-range': [
            function (v, elm) {
                var minValue, maxValue, ranges, reRange, result, values,
                    i, name, validRange, minValidRange, maxValidRange;

                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                } else if ($.validator.methods['validate-digits'] && $.validator.methods['validate-digits'](v)) {
                    minValue = maxValue = $.mage.parseNumber(v);
                } else {
                    ranges = /^(-?\d+)?-(-?\d+)?$/.exec(v);

                    if (ranges) {
                        minValue = $.mage.parseNumber(ranges[1]);
                        maxValue = $.mage.parseNumber(ranges[2]);

                        if (minValue > maxValue) { //eslint-disable-line max-depth
                            return false;
                        }
                    } else {
                        return false;
                    }
                }
                reRange = /^range-(-?\d+)?-(-?\d+)?$/;
                result = true;
                values = $(elm).prop('class').split(' ');

                for (i = values.length - 1; i >= 0; i--) {
                    name = values[i];
                    validRange = reRange.exec(name);

                    if (validRange) {
                        minValidRange = $.mage.parseNumber(validRange[1]);
                        maxValidRange = $.mage.parseNumber(validRange[2]);
                        result = result &&
                            (isNaN(minValidRange) || minValue >= minValidRange) &&
                            (isNaN(maxValidRange) || maxValue <= maxValidRange);
                    }
                }

                return result;
            },
            $.mage.__('The value is not within the specified range.')
        ],
        'validate-alpha': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^[a-zA-Z]+$/.test(v);
            },
            $.mage.__('Please use letters only (a-z or A-Z) in this field.')
        ],
        'validate-code': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^[a-zA-Z]+[a-zA-Z0-9_]+$/.test(v);
            },
            $.mage.__('Please use only letters (a-z or A-Z), numbers (0-9) or underscore (_) in this field, and the first character should be a letter.') //eslint-disable-line max-len
        ],
        'validate-alphanum': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^[a-zA-Z0-9]+$/.test(v);
            },
            $.mage.__('Please use only letters (a-z or A-Z) or numbers (0-9) in this field. No spaces or other characters are allowed.') //eslint-disable-line max-len
        ],
        'validate-not-number-first': [
            function (value) {
                return $.mage.isEmptyNoTrim(value) || /^[^0-9-\.].*$/.test(value.trim());
            },
            $.mage.__('First character must be letter.')
        ],
        'validate-date': [
            function (value, params, additionalParams) {
                var test = moment(value, additionalParams.dateFormat);

                return $.mage.isEmptyNoTrim(value) || test.isValid();
            },
            $.mage.__('Please enter a valid date.')

        ],
        'validate-date-range': [
            function (v, elm) {
                var m = /\bdate-range-(\w+)-(\w+)\b/.exec(elm.className),
                    currentYear, normalizedTime, dependentElements;

                if (!m || m[2] === 'to' || $.mage.isEmptyNoTrim(v)) {
                    return true;
                }

                currentYear = new Date().getFullYear() + '';

                /**
                 * @param {String} vd
                 * @return {Number}
                 */
                normalizedTime = function (vd) {
                    vd = vd.split(/[.\/]/);

                    if (vd[2] && vd[2].length < 4) {
                        vd[2] = currentYear.substr(0, vd[2].length) + vd[2];
                    }

                    return new Date(vd.join('/')).getTime();
                };

                dependentElements = $(elm.form).find('.validate-date-range.date-range-' + m[1] + '-to');

                return !dependentElements.length || $.mage.isEmptyNoTrim(dependentElements[0].value) ||
                    normalizedTime(v) <= normalizedTime(dependentElements[0].value);
            },
            $.mage.__('Make sure the To Date is later than or the same as the From Date.')
        ],
        'validate-cpassword': [
            function () {
                var conf = $('#confirmation').length > 0 ? $('#confirmation') : $($('.validate-cpassword')[0]),
                    pass = false,
                    passwordElements, i, passwordElement;

                if ($('#password')) {
                    pass = $('#password');
                }
                passwordElements = $('.validate-password');

                for (i = 0; i < passwordElements.length; i++) {
                    passwordElement = $(passwordElements[i]);

                    if (passwordElement.closest('form').attr('id') === conf.closest('form').attr('id')) {
                        pass = passwordElement;
                    }
                }

                if ($('.validate-admin-password').length) {
                    pass = $($('.validate-admin-password')[0]);
                }

                return pass.val() === conf.val();
            },
            $.mage.__('Please make sure your passwords match.')
        ],
        'validate-identifier': [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/.test(v);
            },
            $.mage.__('Please enter a valid URL Key (Ex: "example-page", "example-page.html" or "anotherlevel/example-page").') //eslint-disable-line max-len
        ],
        'validate-zip-international': [

            /*function(v) {
             // @TODO: Cleanup
             return Validation.get('IsEmpty').test(v) ||
             /(^[A-z0-9]{2,10}([\s]{0,1}|[\-]{0,1})[A-z0-9]{2,10}$)/.test(v);
             }*/
            function () {
                return true;
            },
            $.mage.__('Please enter a valid zip code.')
        ],
        'validate-one-required': [
            function (v, elm) {
                var p = $(elm).parent(),
                    options = p.find('input');

                return options.map(function (el) {
                    return $(el).val();
                }).length > 0;
            },
            $.mage.__('Please select one of the options above.')
        ],
        'validate-state': [
            function (v) {
                return v !== 0 || v === '';
            },
            $.mage.__('Please select State/Province.')
        ],
        'required-file': [
            function (v, elm) {
                var result = !$.mage.isEmptyNoTrim(v),
                    ovId;

                if (!result) {
                    ovId = $('#' + $(elm).attr('id') + '_value');

                    if (ovId.length > 0) {
                        result = !$.mage.isEmptyNoTrim(ovId.val());
                    }
                }

                return result;
            },
            $.mage.__('Please select a file.')
        ],
        'validate-ajax-error': [
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
        'validate-optional-datetime': [
            function (v, elm, param) {
                var dateTimeParts = $('.datetime-picker[id^="options_' + param + '"]'),
                    hasWithValue = false,
                    hasWithNoValue = false,
                    pattern = /day_part$/i,
                    i;

                for (i = 0; i < dateTimeParts.length; i++) {
                    if (!pattern.test($(dateTimeParts[i]).attr('id'))) {
                        if ($(dateTimeParts[i]).val() === 's') { //eslint-disable-line max-depth
                            hasWithValue = true;
                        } else {
                            hasWithNoValue = true;
                        }
                    }
                }

                return hasWithValue ^ hasWithNoValue;
            },
            $.mage.__('The field isn\'t complete.')
        ],
        'validate-required-datetime': [
            function (v, elm, param) {
                var dateTimeParts = $('.datetime-picker[id^="options_' + param + '"]'),
                    i;

                for (i = 0; i < dateTimeParts.length; i++) {
                    if (dateTimeParts[i].value === '') {
                        return false;
                    }
                }

                return true;
            },
            $.mage.__('This is a required field.')
        ],
        'validate-one-required-by-name': [
            function (v, elm, selector) {
                var name = elm.name.replace(/([\\"])/g, '\\$1'),
                    container = this.currentForm;

                selector = selector === true ? 'input[name="' + name + '"]:checked' : selector;

                return !!container.querySelectorAll(selector).length;
            },
            $.mage.__('Please select one of the options.')
        ],
        'less-than-equals-to': [
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
        'greater-than-equals-to': [
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
        'validate-emails': [
            function (value) {
                var validRegexp, emails, i;

                if ($.mage.isEmpty(value)) {
                    return true;
                }
                validRegexp = /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i; //eslint-disable-line max-len
                emails = value.split(/[\s\n\,]+/g);

                for (i = 0; i < emails.length; i++) {
                    if (!validRegexp.test(emails[i].trim())) {
                        return false;
                    }
                }

                return true;
            },
            $.mage.__('Please enter valid email addresses, separated by commas. For example, johndoe@domain.com, johnsmith@domain.com.') //eslint-disable-line max-len
        ],

        'validate-cc-type-select': [

            /**
             * Validate credit card type matches credit card number
             * @param {*} value - select credit card type
             * @param {*} element - element contains the select box for credit card types
             * @param {*} params - selector for credit card number
             * @return {Boolean}
             */
            function (value, element, params) {
                if (value && params && creditCartTypes[value]) {
                    return creditCartTypes[value][0].test($(params).val().replace(/\s+/g, ''));
                }

                return false;
            },
            $.mage.__('Card type does not match credit card number.')
        ],
        'validate-cc-number': [

            /**
             * Validate credit card number based on mod 10.
             *
             * @param {*} value - credit card number
             * @return {Boolean}
             */
            function (value) {
                if (value) {
                    return validateCreditCard(value);
                }

                return false;
            },
            $.mage.__('Please enter a valid credit card number.')
        ],
        'validate-cc-type': [

            /**
             * Validate credit card number is for the correct credit card type.
             *
             * @param {String} value - credit card number
             * @param {*} element - element contains credit card number
             * @param {*} params - selector for credit card type
             * @return {Boolean}
             */
            function (value, element, params) {
                var ccType;

                if (value && params) {
                    ccType = $(params).val();
                    value = value.replace(/\s/g, '').replace(/\-/g, '');

                    if (creditCartTypes[ccType] && creditCartTypes[ccType][0]) {
                        return creditCartTypes[ccType][0].test(value);
                    } else if (creditCartTypes[ccType] && !creditCartTypes[ccType][0]) {
                        return true;
                    }
                }

                return false;
            },
            $.mage.__('Credit card number does not match credit card type.')
        ],
        'validate-cc-exp': [

            /**
             * Validate credit card expiration date, make sure it's within the year and not before current month.
             *
             * @param {*} value - month
             * @param {*} element - element contains month
             * @param {*} params - year selector
             * @return {Boolean}
             */
            function (value, element, params) {
                var isValid = false,
                    month, year, currentTime, currentMonth, currentYear;

                if (value && params) {
                    month = value;
                    year = $(params).val();
                    currentTime = new Date();
                    currentMonth = currentTime.getMonth() + 1;
                    currentYear = currentTime.getFullYear();

                    isValid = !year || year > currentYear || year == currentYear && month >= currentMonth; //eslint-disable-line
                }

                return isValid;
            },
            $.mage.__('Incorrect credit card expiration date.')
        ],
        'validate-cc-cvn': [

            /**
             * Validate credit card cvn based on credit card type.
             *
             * @param {*} value - credit card cvn
             * @param {*} element - element contains credit card cvn
             * @param {*} params - credit card type selector
             * @return {*}
             */
            function (value, element, params) {
                var ccType;

                if (value && params) {
                    ccType = $(params).val();

                    if (creditCartTypes[ccType] && creditCartTypes[ccType][0]) {
                        return creditCartTypes[ccType][1].test(value);
                    }
                }

                return false;
            },
            $.mage.__('Please enter a valid credit card verification number.')
        ],
        'validate-cc-ukss': [

            /**
             * Validate Switch/Solo/Maestro issue number and start date is filled.
             *
             * @param {*} value - input field value
             * @return {*}
             */
            function (value) {
                return value;
            },
            $.mage.__('Please enter issue number or start date for switch/solo card type.')
        ],
        'validate-length': [
            function (v, elm) {
                var reMax = new RegExp(/^maximum-length-[0-9]+$/),
                    reMin = new RegExp(/^minimum-length-[0-9]+$/),
                    validator = this,
                    result = true,
                    length = 0;

                $.each(elm.className.split(' '), function (index, name) {
                    if (name.match(reMax) && result) {
                        length = name.split('-')[2];
                        result = v.length <= length;
                        validator.validateMessage =
                            $.mage.__('Please enter less or equal than %1 symbols.').replace('%1', length);
                    }

                    if (name.match(reMin) && result && !$.mage.isEmpty(v)) {
                        length = name.split('-')[2];
                        result = v.length >= length;
                        validator.validateMessage =
                            $.mage.__('Please enter more or equal than %1 symbols.').replace('%1', length);
                    }
                });

                return result;
            }, function () {
                return this.validateMessage;
            }
        ],
        'required-entry': [
            function (value) {
                return !$.mage.isEmpty(value);
            }, $.mage.__('This is a required field.')
        ],
        'not-negative-amount': [
            function (v) {
                if (v.length) {
                    return (/^\s*\d+([,.]\d+)*\s*%?\s*$/).test(v);
                }

                return true;
            },
            $.mage.__('Please enter positive number in this field.')
        ],
        'validate-per-page-value-list': [
            function (v) {
                var isValid = true,
                    values = v.split(','),
                    i;

                if ($.mage.isEmpty(v)) {
                    return isValid;
                }

                for (i = 0; i < values.length; i++) {
                    if (!/^[0-9]+$/.test(values[i])) {
                        isValid = false;
                    }
                }

                return isValid;
            },
            $.mage.__('Please enter a valid value, ex: 10,20,30')
        ],
        'validate-per-page-value': [
            function (v, elm) {
                var values;

                if ($.mage.isEmpty(v)) {
                    return false;
                }
                values = $('#' + elm.id + '_values').val().split(',');

                return values.indexOf(v) !== -1;
            },
            $.mage.__('Please enter a valid value from list')
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
            $.mage.__('Please enter 6 or more characters. Leading and trailing spaces will be ignored.')
        ],
        'required-if-not-specified': [
            function (value, element, params) {
                var valid = false,
                    alternate = $(params),
                    alternateValue;

                if (alternate.length > 0) {
                    valid = this.check(alternate);
                    // if valid, it may be blank, so check for that
                    if (valid) {
                        alternateValue = alternate.val();

                        if (typeof alternateValue == 'undefined' || alternateValue.length === 0) { //eslint-disable-line
                            valid = false;
                        }
                    }
                }

                if (!valid) {
                    valid = !this.optional(element);
                }

                return valid;
            },
            $.mage.__('This is a required field.')
        ],
        'required-if-all-sku-empty-and-file-not-loaded': [
            function (value, element, params) {
                var valid = false,
                    alternate = $(params.specifiedId),
                    alternateValue;

                if (alternate.length > 0) {
                    valid = this.check(alternate);
                    // if valid, it may be blank, so check for that
                    if (valid) {
                        alternateValue = alternate.val();

                        if (typeof alternateValue == 'undefined' || alternateValue.length === 0) { //eslint-disable-line
                            valid = false;
                        }
                    }
                }

                if (!valid) {
                    valid = !this.optional(element);
                }

                $('input[' + params.dataSku + '=true]').each(function () {
                    if ($(this).val() !== '') {
                        valid = true;
                    }
                });

                return valid;
            },
            $.mage.__('Please enter valid SKU key.')
        ],
        'required-if-specified': [
            function (value, element, params) {
                var valid = true,
                    dependent = $(params),
                    dependentValue;

                if (dependent.length > 0) {
                    valid = this.check(dependent);
                    // if valid, it may be blank, so check for that
                    if (valid) {
                        dependentValue = dependent.val();
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
            $.mage.__('This is a required field.')
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
            $.mage.__('Please enter a valid number.')
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
            $.mage.__('This is required field')
        ],
        'required-text-swatch-entry': [
            tableSingleValidation,
            $.mage.__('Admin is a required field in each row.')
        ],
        'required-visual-swatch-entry': [
            tableSingleValidation,
            $.mage.__('Admin is a required field in each row.')
        ],
        'required-dropdown-attribute-entry': [
            tableSingleValidation,
            $.mage.__('Admin is a required field in each row.')
        ],
        'validate-item-quantity': [
            function (value, element, params) {
                var validator = this,
                    result = false,
                    // obtain values for validation
                    qty = $.mage.parseNumber(value),
                    isMinAllowedValid = typeof params.minAllowed === 'undefined' ||
                        qty >= $.mage.parseNumber(params.minAllowed),
                    isMaxAllowedValid = typeof params.maxAllowed === 'undefined' ||
                        qty <= $.mage.parseNumber(params.maxAllowed),
                    isQtyIncrementsValid = typeof params.qtyIncrements === 'undefined' ||
                        resolveModulo(qty, $.mage.parseNumber(params.qtyIncrements)) === 0.0;

                result = qty > 0;

                if (result === false) {
                    validator.itemQtyErrorMessage = $.mage.__('Please enter a quantity greater than 0.');//eslint-disable-line max-len

                    return result;
                }

                result = isMinAllowedValid;

                if (result === false) {
                    validator.itemQtyErrorMessage = $.mage.__('The fewest you may purchase is %1.').replace('%1', params.minAllowed);//eslint-disable-line max-len

                    return result;
                }

                result = isMaxAllowedValid;

                if (result === false) {
                    validator.itemQtyErrorMessage = $.mage.__('The maximum you may purchase is %1.').replace('%1', params.maxAllowed);//eslint-disable-line max-len

                    return result;
                }

                result = isQtyIncrementsValid;

                if (result === false) {
                    validator.itemQtyErrorMessage = $.mage.__('You can buy this product only in quantities of %1 at a time.').replace('%1', params.qtyIncrements);//eslint-disable-line max-len

                    return result;
                }

                return result;
            }, function () {
                return this.itemQtyErrorMessage;
            }
        ],
        'password-not-equal-to-user-name': [
            function (value, element, params) {
                if (typeof params === 'string') {
                    return value.toLowerCase() !== params.toLowerCase();
                }

                return true;
            },
            $.mage.__('The password can\'t be the same as the email address. Create a new password and try again.')
        ]
    };

    $.each(rules, function (i, rule) {
        rule.unshift(i);
        $.validator.addMethod.apply($.validator, rule);
    });
    $.validator.addClassRules({
        'required-option': {
            required: true
        },
        'required-options-count': {
            required: true
        },
        'validate-both-passwords': {
            'validate-cpassword': true
        }
    });
    $.validator.messages = $.extend($.validator.messages, {
        required: $.mage.__('This is a required field.'),
        remote: $.mage.__('Please fix this field.'),
        email: $.mage.__('Please enter a valid email address.'),
        url: $.mage.__('Please enter a valid URL.'),
        date: $.mage.__('Please enter a valid date.'),
        dateISO: $.mage.__('Please enter a valid date (ISO).'),
        number: $.mage.__('Please enter a valid number.'),
        digits: $.mage.__('Please enter only digits.'),
        creditcard: $.mage.__('Please enter a valid credit card number.'),
        equalTo: $.mage.__('Please enter the same value again.'),
        maxlength: $.validator.format($.mage.__('Please enter no more than {0} characters.')),
        minlength: $.validator.format($.mage.__('Please enter at least {0} characters.')),
        rangelength: $.validator.format($.mage.__('Please enter a value between {0} and {1} characters long.')),
        range: $.validator.format($.mage.__('Please enter a value between {0} and {1}.')),
        max: $.validator.format($.mage.__('Please enter a value less than or equal to {0}.')),
        min: $.validator.format($.mage.__('Please enter a value greater than or equal to {0}.'))
    });

    if ($.metadata) {
        // Setting the type as html5 to enable data-validate attribute
        $.metadata.setType('html5');
    }

    showLabel = $.validator.prototype.showLabel;
    $.extend(true, $.validator.prototype, {
        /**
         * @param {*} element
         * @param {*} message
         */
        showLabel: function (element, message) {
            var label, elem;

            showLabel.call(this, element, message);

            // ARIA (adding aria-invalid & aria-describedby)
            label = this.errorsFor(element);
            elem = $(element);

            if (!label.attr('id')) {
                label.attr('id', this.idOrName(element) + '-error');
            }
            elem.attr('aria-invalid', 'true')
                .attr('aria-describedby', label.attr('id'));
        }
    });

    /**
     * Validate form field without instantiating validate plug-in.
     *
     * @param {Element|String} element - DOM element or selector
     * @return {Boolean} validation result
     */
    $.validator.validateElement = function (element) {
        var form, validator, valid, classes;

        element = $(element);
        form = element.get(0).form;
        validator = form ? $(form).data('validator') : null;

        if (validator) {
            return validator.element(element.get(0));
        }
        valid = true;
        classes = element.prop('class').split(' ');
        $.each(classes, $.proxy(function (i, className) {
            if (this.methods[className] && !this.methods[className](element.val(), element.get(0))) {
                valid = false;

                return valid;
            }
        }, this));

        return valid;
    };

    originValidateDelegate = $.fn.validateDelegate;

    /**
     * @return {*}
     */
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
     * @param {Object} config
     * @returns {*}
     */
    $.validator.validateSingleElement = function (element, config) {
        var errors = {},
            valid = true,
            validateConfig = {
                errorElement: 'label',
                ignore: '.ignore-validate',
                hideError: false
            },
            form, validator, classes, elementValue;

        $.extend(validateConfig, config);
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
            elementValue = element.val();

            if (element.is(':checkbox') || element.is(':radio')) {
                elementValue = element.is(':checked') || null;
            }

            if (this.methods[className] && !this.methods[className](elementValue, element.get(0))) {
                valid = false;
                errors[element.get(0).name] = this.messages[className];
                validator.invalid[element.get(0).name] = true;

                if (!validateConfig.hideError) {
                    validator.showErrors(errors);
                }

                return valid;
            }
        }, this));

        return valid;
    };

    $.widget('mage.validation', {
        options: {
            meta: 'validate',
            onfocusout: false,
            onkeyup: false,
            onclick: false,
            ignoreTitle: true,
            errorClass: 'mage-error',
            errorElement: 'div',

            /**
             * @param {*} error
             * @param {*} element
             */
            errorPlacement: function (error, element) {
                var errorPlacement = element,
                    fieldWrapper;

                // logic for date-picker error placement
                if (element.hasClass('_has-datepicker')) {
                    errorPlacement = element.siblings('button');
                }
                // logic for field wrapper
                fieldWrapper = element.closest('.addon');

                if (fieldWrapper.length) {
                    errorPlacement = fieldWrapper.after(error);
                }
                //logic for checkboxes/radio
                if (element.is(':checkbox') || element.is(':radio')) {
                    errorPlacement = element.parents('.control').children().last();

                    //fallback if group does not have .control parent
                    if (!errorPlacement.length) {
                        errorPlacement = element.siblings('label').last();
                    }
                }
                //logic for control with tooltip
                if (element.siblings('.tooltip').length) {
                    errorPlacement = element.siblings('.tooltip');
                }
                //logic for select with tooltip in after element
                if (element.next().find('.tooltip').length) {
                    errorPlacement = element.next();
                }
                errorPlacement.after(error);
            }
        },

        /**
         * Check if form pass validation rules without submit.
         *
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
         * Validation creation.
         *
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
         * Validation listening.
         *
         * @protected
         */
        _listenFormValidate: function () {
            $('form').on('invalid-form.validate', this.listenFormValidateHandler);
        },

        /**
         * Handle form validation. Focus on first invalid form field.
         *
         * @param {jQuery.Event} event
         * @param {Object} validation
         */
        listenFormValidateHandler: function (event, validation) {
            var firstActive = $(validation.errorList[0].element || []),
                lastActive = $(validation.findLastActive() ||
                    validation.errorList.length && validation.errorList[0].element || []),
                windowHeight = $(window).height(),
                parent, successList;

            if (lastActive.is(':hidden')) {
                parent = lastActive.parent();
                $('html, body').animate({
                    scrollTop: parent.offset().top - windowHeight / 2
                });
            }

            // ARIA (removing aria attributes if success)
            successList = validation.successList;

            if (successList.length) {
                $.each(successList, function () {
                    $(this)
                        .removeAttr('aria-describedby')
                        .removeAttr('aria-invalid');
                });
            }

            if (firstActive.length) {
                $('body').stop().animate({
                    scrollTop: firstActive.offset().top - windowHeight / 2
                });
                firstActive.focus();
            }
        }
    });

    return $.mage.validation;
}));
