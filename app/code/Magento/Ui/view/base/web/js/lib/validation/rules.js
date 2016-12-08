/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    './utils',
    'moment',
    'jquery/validate',
    'jquery/ui',
    'mage/translate'
], function ($, _, utils, moment) {
    'use strict';

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
     * Collection of validation rules including rules from additional-methods.js
     * @type {Object}
     */
    return _.mapObject({
        'min_text_length': [
            function (value, params) {
                return _.isUndefined(value) || value.length === 0 || value.length >= +params;
            },
            $.mage.__('Please enter more or equal than {0} symbols.')
        ],
        'max_text_length': [
            function (value, params) {
                return !_.isUndefined(value) && value.length <= +params;
            },
            $.mage.__('Please enter less or equal than {0} symbols.')
        ],
        'max-words': [
            function (value, params) {
                return utils.stripHtml(value).match(/\b\w+\b/g).length < params;
            },
            $.mage.__('Please enter {0} words or less.')
        ],
        'min-words': [
            function (value, params) {
                return utils.stripHtml(value).match(/\b\w+\b/g).length >= params;
            },
            $.mage.__('Please enter at least {0} words.')
        ],
        'range-words': [
            function (value, params) {
                return utils.stripHtml(value).match(/\b\w+\b/g).length >= params[0] &&
                    value.match(/bw+b/g).length < params[1];
            },
            $.mage.__('Please enter between {0} and {1} words.')
        ],
        'letters-with-basic-punc': [
            function (value) {
                return /^[a-z\-.,()\u0027\u0022\s]+$/i.test(value);
            },
            $.mage.__('Letters or punctuation only please')
        ],
        'alphanumeric': [
            function (value) {
                return /^\w+$/i.test(value);
            },
            $.mage.__('Letters, numbers, spaces or underscores only please')
        ],
        'letters-only': [
            function (value) {
                return /^[a-z]+$/i.test(value);
            },
            $.mage.__('Letters only please')
        ],
        'no-whitespace': [
            function (value) {
                return /^\S+$/i.test(value);
            },
            $.mage.__('No white space please')
        ],
        'zip-range': [
            function (value) {
                return /^90[2-5]-\d{2}-\d{4}$/.test(value);
            },
            $.mage.__('Your ZIP-code must be in the range 902xx-xxxx to 905-xx-xxxx')
        ],
        'integer': [
            function (value) {
                return /^-?\d+$/.test(value);
            },
            $.mage.__('A positive or negative non-decimal number please')
        ],
        'vinUS': [
            function (value) {
                if (value.length !== 17) {
                    return false;
                }
                var i, n, d, f, cd, cdv,//eslint-disable-line vars-on-top
                    LL = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'],//eslint-disable-line max-len
                    VL = [1, 2, 3, 4, 5, 6, 7, 8, 1, 2, 3, 4, 5, 7, 9, 2, 3, 4, 5, 6, 7, 8, 9],
                    FL = [8, 7, 6, 5, 4, 3, 2, 10, 0, 9, 8, 7, 6, 5, 4, 3, 2],
                    rs = 0;

                for (i = 0; i < 17; i++) {
                    f = FL[i];
                    d = value.slice(i, i + 1);

                    if (i === 8) {
                        cdv = d;
                    }

                    if (!isNaN(d)) {
                        d *= f;
                    } else {
                        for (n = 0; n < LL.length; n++) {//eslint-disable-line max-depth
                            if (d.toUpperCase() === LL[n]) {//eslint-disable-line max-depth
                                d = VL[n];
                                d *= f;

                                if (isNaN(cdv) && n === 8) {//eslint-disable-line max-depth
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
            function (value) {
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

                return check;
            },
            $.mage.__('Please enter a correct date')
        ],
        'dateNL': [
            function (value) {
                return /^\d\d?[\.\/-]\d\d?[\.\/-]\d\d\d?\d?$/.test(value);
            },
            $.mage.__('Vul hier een geldige datum in.')
        ],
        'time': [
            function (value) {
                return /^([01]\d|2[0-3])(:[0-5]\d){0,2}$/.test(value);
            },
            $.mage.__('Please enter a valid time, between 00:00 and 23:59')
        ],
        'time12h': [
            function (value) {
                return /^((0?[1-9]|1[012])(:[0-5]\d){0,2}(\ [AP]M))$/i.test(value);
            },
            $.mage.__('Please enter a valid time, between 00:00 am and 12:00 pm')
        ],
        'phoneUS': [
            function (value) {
                value = value.replace(/\s+/g, '');

                return value.length > 9 && value.match(/^(1-?)?(\([2-9]\d{2}\)|[2-9]\d{2})-?[2-9]\d{2}-?\d{4}$/);
            },
            $.mage.__('Please specify a valid phone number')
        ],
        'phoneUK': [
            function (value) {
                return value.length > 9 && value.match(/^(\(?(0|\+44)[1-9]{1}\d{1,4}?\)?\s?\d{3,4}\s?\d{3,4})$/);
            },
            $.mage.__('Please specify a valid phone number')
        ],
        'mobileUK': [
            function (value) {
                return value.length > 9 && value.match(/^((0|\+44)7(5|6|7|8|9){1}\d{2}\s?\d{6})$/);
            },
            $.mage.__('Please specify a valid mobile number')
        ],
        'stripped-min-length': [
            function (value, param) {
                return $(value).text().length >= param;
            },
            $.mage.__('Please enter at least {0} characters')
        ],
        'email2': [
            function (value) {
                return /^((([a-z]|\d|[!#\$%&\u0027\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&\u0027\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\u0022)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\u0022)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)*(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i.test(value);//eslint-disable-line max-len
            },
            $.validator.messages.email
        ],
        'url2': [
            function (value) {
                return /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&\u0027\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)*(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&\u0027\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&\u0027\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&\u0027\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&\u0027\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(value);//eslint-disable-line max-len
            },
            $.validator.messages.url
        ],
        'credit-card-types': [
            function (value, param) {
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
        'ipv4': [
            function (value) {
                return /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/i.test(value);//eslint-disable-line max-len
            },
            $.mage.__('Please enter a valid IP v4 address.')
        ],
        'ipv6': [
            function (value) {
                return /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);//eslint-disable-line max-len
            },
            $.mage.__('Please enter a valid IP v6 address.')
        ],
        'pattern': [
            function (value, param) {
                return param.test(value);
            },
            $.mage.__('Invalid format.')
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
                return !utils.isEmpty(value);
            },
            $.mage.__('Empty Value.')
        ],
        'validate-alphanum-with-spaces': [
            function (value) {
                return utils.isEmptyNoTrim(value) || /^[a-zA-Z0-9 ]+$/.test(value);
            },
            $.mage.__('Please use only letters (a-z or A-Z), numbers (0-9) or spaces only in this field.')
        ],
        'validate-data': [
            function (value) {
                return utils.isEmptyNoTrim(value) || /^[A-Za-z]+[A-Za-z0-9_]+$/.test(value);
            },
            $.mage.__('Please use only letters (a-z or A-Z), numbers (0-9) or underscore (_) in this field, and the first character should be a letter.')//eslint-disable-line max-len
        ],
        'validate-street': [
            function (value) {
                return utils.isEmptyNoTrim(value) ||
                    /^[ \w]{3,}([A-Za-z]\.)?([ \w]*\#\d+)?(\r\n| )[ \w]{3,}/.test(value);
            },
            $.mage.__('Please use only letters (a-z or A-Z), numbers (0-9), spaces and "#" in this field.')
        ],
        'validate-phoneStrict': [
            function (value) {
                return utils.isEmptyNoTrim(value) || /^(\()?\d{3}(\))?(-|\s)?\d{3}(-|\s)\d{4}$/.test(value);
            },
            $.mage.__('Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.')
        ],
        'validate-phoneLax': [
            function (value) {
                return utils.isEmptyNoTrim(value) ||
                    /^((\d[\-. ]?)?((\(\d{3}\))|\d{3}))?[\-. ]?\d{3}[\-. ]?\d{4}$/.test(value);
            },
            $.mage.__('Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.')
        ],
        'validate-fax': [
            function (value) {
                return utils.isEmptyNoTrim(value) || /^(\()?\d{3}(\))?(-|\s)?\d{3}(-|\s)\d{4}$/.test(value);
            },
            $.mage.__('Please enter a valid fax number (Ex: 123-456-7890).')
        ],
        'validate-email': [
            function (value) {
                return utils.isEmptyNoTrim(value) || /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(value);//eslint-disable-line max-len
            },
            $.mage.__('Please enter a valid email address (Ex: johndoe@domain.com).')
        ],
        'validate-emailSender': [
            function (value) {
                return utils.isEmptyNoTrim(value) || /^[\S ]+$/.test(value);
            },
            $.mage.__('Please enter a valid email address (Ex: johndoe@domain.com).')
        ],
        'validate-password': [
            function (value) {
                var pass;

                if (value == null) {
                    return false;
                }

                pass = $.trim(value);

                if (!pass.length) {
                    return true;
                }

                return !(pass.length > 0 && pass.length < 6);
            },
            $.mage.__('Please enter 6 or more characters. Leading and trailing spaces will be ignored.')
        ],
        'validate-admin-password': [
            function (value) {
                var pass;

                if (value == null) {
                    return false;
                }

                pass = $.trim(value);

                if (pass.length === 0) {
                    return true;
                }

                if (!/[a-z]/i.test(value) || !/[0-9]/.test(value)) {
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
                    validator.passwordErrorMessage = $.mage.__('Minimum length of this field must be equal or greater than %1 symbols. Leading and trailing spaces will be ignored.').replace('%1', passwordMinLength);//eslint-disable-line max-len

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
                    validator.passwordErrorMessage = $.mage.__('Minimum of different classes of characters in password is %1. Classes of characters: Lower Case, Upper Case, Digits, Special Characters.').replace('%1', passwordMinCharacterSets);//eslint-disable-line max-len
                }

                return result;
            }, function () {
                return this.passwordErrorMessage;
            }
        ],
        'validate-url': [
            function (value) {
                if (utils.isEmptyNoTrim(value)) {
                    return true;
                }
                value = (value || '').replace(/^\s+/, '').replace(/\s+$/, '');

                return (/^(http|https|ftp):\/\/(([A-Z0-9]([A-Z0-9_-]*[A-Z0-9]|))(\.[A-Z0-9]([A-Z0-9_-]*[A-Z0-9]|))*)(:(\d+))?(\/[A-Z0-9~](([A-Z0-9_~-]|\.)*[A-Z0-9~]|))*\/?(.*)?$/i).test(value);//eslint-disable-line max-len

            },
            $.mage.__('Please enter a valid URL. Protocol is required (http://, https:// or ftp://).')
        ],
        'validate-clean-url': [
            function (value) {
                return utils.isEmptyNoTrim(value) || /^(http|https|ftp):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+.(com|org|net|dk|at|us|tv|info|uk|co.uk|biz|se)$)(:(\d+))?\/?/i.test(value) || /^(www)((\.[A-Z0-9][A-Z0-9_-]*)+.(com|org|net|dk|at|us|tv|info|uk|co.uk|biz|se)$)(:(\d+))?\/?/i.test(value);//eslint-disable-line max-len

            },
            $.mage.__('Please enter a valid URL. For example http://www.example.com or www.example.com.')
        ],
        'validate-xml-identifier': [
            function (value) {
                return utils.isEmptyNoTrim(value) || /^[A-Z][A-Z0-9_\/-]*$/i.test(value);

            },
            $.mage.__('Please enter a valid XML-identifier (Ex: something_1, block5, id-4).')
        ],
        'validate-ssn': [
            function (value) {
                return utils.isEmptyNoTrim(value) || /^\d{3}-?\d{2}-?\d{4}$/.test(value);

            },
            $.mage.__('Please enter a valid social security number (Ex: 123-45-6789).')
        ],
        'validate-zip-us': [
            function (value) {
                return utils.isEmptyNoTrim(value) || /(^\d{5}$)|(^\d{5}-\d{4}$)/.test(value);

            },
            $.mage.__('Please enter a valid zip code (Ex: 90602 or 90602-1234).')
        ],
        'validate-date-au': [
            function (value) {
                var regex = /^(\d{2})\/(\d{2})\/(\d{4})$/,
                    d;

                if (utils.isEmptyNoTrim(value)) {
                    return true;
                }

                if (utils.isEmpty(value) || !regex.test(value)) {
                    return false;
                }
                d = new Date(value.replace(regex, '$2/$1/$3'));

                return parseInt(RegExp.$2, 10) === 1 + d.getMonth() &&
                    parseInt(RegExp.$1, 10) === d.getDate() &&
                    parseInt(RegExp.$3, 10) === d.getFullYear();

            },
            $.mage.__('Please use this date format: dd/mm/yyyy. For example 17/03/2006 for the 17th of March, 2006.')
        ],
        'validate-currency-dollar': [
            function (value) {
                return utils.isEmptyNoTrim(value) || /^\$?\-?([1-9]{1}[0-9]{0,2}(\,[0-9]{3})*(\.[0-9]{0,2})?|[1-9]{1}\d*(\.[0-9]{0,2})?|0(\.[0-9]{0,2})?|(\.[0-9]{1,2})?)$/.test(value);//eslint-disable-line max-len

            },
            $.mage.__('Please enter a valid $ amount. For example $100.00.')
        ],
        'validate-not-negative-number': [
            function (value) {
                if (utils.isEmptyNoTrim(value)) {
                    return true;
                }
                value = utils.parseNumber(value);

                return !isNaN(value) && value >= 0;

            },
            $.mage.__('Please enter a number 0 or greater in this field.')
        ],
        // validate-not-negative-number should be replaced in all places with this one and then removed
        'validate-zero-or-greater': [
            function (value) {
                if (utils.isEmptyNoTrim(value)) {
                    return true;
                }
                value = utils.parseNumber(value);

                return !isNaN(value) && value >= 0;

            },
            $.mage.__('Please enter a number 0 or greater in this field.')
        ],
        'validate-greater-than-zero': [
            function (value) {
                if (utils.isEmptyNoTrim(value)) {
                    return true;
                }
                value = utils.parseNumber(value);

                return !isNaN(value) && value > 0;
            },
            $.mage.__('Please enter a number greater than 0 in this field.')
        ],
        'validate-css-length': [
            function (value) {
                if (value !== '') {
                    return (/^[0-9]*\.*[0-9]+(px|pc|pt|ex|em|mm|cm|in|%)?$/).test(value);
                }

                return true;
            },
            $.mage.__('Please input a valid CSS-length (Ex: 100px, 77pt, 20em, .5ex or 50%).')
        ],
        'validate-number': [
            function (value) {
                return utils.isEmptyNoTrim(value) ||
                    !isNaN(utils.parseNumber(value)) && /^\s*-?\d*(\.\d*)?\s*$/.test(value);
            },
            $.mage.__('Please enter a valid number in this field.')
        ],
        'validate-integer': [
            function (value) {
                return utils.isEmptyNoTrim(value) || !isNaN(utils.parseNumber(value)) && /^\s*-?\d*\s*$/.test(value);
            },
            $.mage.__('Please enter a valid integer in this field.')
        ],
        'validate-number-range': [
            function (value, param) {
                var numValue, dataAttrRange, result, range, m;

                if (utils.isEmptyNoTrim(value)) {
                    return true;
                }

                numValue = utils.parseNumber(value);

                if (isNaN(numValue)) {
                    return false;
                }

                dataAttrRange = /^(-?[\d.,]+)?-(-?[\d.,]+)?$/;
                result = true;
                range = param;

                if (range) {
                    m = dataAttrRange.exec(range);

                    if (m) {
                        result = result && utils.isBetween(numValue, m[1], m[2]);
                    }
                }

                return result;
            },
            $.mage.__('The value is not within the specified range.')
        ],
        'validate-digits': [
            function (value) {
                return utils.isEmptyNoTrim(value) || !/[^\d]/.test(value);
            },
            $.mage.__('Please enter a valid number in this field.')
        ],
        'validate-digits-range': [
            function (value, param) {
                var numValue, dataAttrRange, result, range, m;

                if (utils.isEmptyNoTrim(value)) {
                    return true;
                }

                numValue = utils.parseNumber(value);

                if (isNaN(numValue)) {
                    return false;
                }

                dataAttrRange = /^(-?\d+)?-(-?\d+)?$/;
                result = true;
                range = param;

                if (range) {
                    m = dataAttrRange.exec(range);

                    if (m) {
                        result = result && utils.isBetween(numValue, m[1], m[2]);
                    }
                }

                return result;
            },
            $.mage.__('The value is not within the specified range.')
        ],
        'validate-range': [
            function (value) {
                var minValue, maxValue, ranges;

                if (utils.isEmptyNoTrim(value)) {
                    return true;
                } else if ($.validator.methods['validate-digits'] && $.validator.methods['validate-digits'](value)) {
                    minValue = maxValue = utils.parseNumber(value);
                } else {
                    ranges = /^(-?\d+)?-(-?\d+)?$/.exec(value);

                    if (ranges) {
                        minValue = utils.parseNumber(ranges[1]);
                        maxValue = utils.parseNumber(ranges[2]);

                        if (minValue > maxValue) {//eslint-disable-line max-depth
                            return false;
                        }
                    } else {
                        return false;
                    }
                }
            },
            $.mage.__('The value is not within the specified range.')
        ],
        'validate-alpha': [
            function (value) {
                return utils.isEmptyNoTrim(value) || /^[a-zA-Z]+$/.test(value);
            },
            $.mage.__('Please use letters only (a-z or A-Z) in this field.')
        ],
        'validate-code': [
            function (value) {
                return utils.isEmptyNoTrim(value) || /^[a-z]+[a-z0-9_]+$/.test(value);
            },
            $.mage.__('Please use only letters (a-z), numbers (0-9) or underscore (_) in this field, and the first character should be a letter.')//eslint-disable-line max-len
        ],
        'validate-alphanum': [
            function (value) {
                return utils.isEmptyNoTrim(value) || /^[a-zA-Z0-9]+$/.test(value);
            },
            $.mage.__('Please use only letters (a-z or A-Z) or numbers (0-9) in this field. No spaces or other characters are allowed.')//eslint-disable-line max-len
        ],
        'validate-date': [
            function (value, params, additionalParams) {
                var test = moment(value, additionalParams.dateFormat);

                return utils.isEmptyNoTrim(value) || test.isValid();
            }, $.mage.__('Please enter a valid date.')

        ],
        'validate-identifier': [
            function (value) {
                return utils.isEmptyNoTrim(value) || /^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/.test(value);
            },
            $.mage.__('Please enter a valid URL Key (Ex: "example-page", "example-page.html" or "anotherlevel/example-page").')//eslint-disable-line max-len
        ],
        'validate-zip-international': [

            /*function(v) {
            // @TODO: Cleanup
            return Validation.get('IsEmpty').test(v) || /(^[A-z0-9]{2,10}([\s]{0,1}|[\-]{0,1})[A-z0-9]{2,10}$)/.test(v);
            }*/
            function () {
                return true;
            },
            $.mage.__('Please enter a valid zip code.')
        ],
        'validate-state': [
            function (value) {
                return value !== 0 || value === '';
            },
            $.mage.__('Please select State/Province.')
        ],
        'less-than-equals-to': [
            function (value, params) {
                if ($.isNumeric(params) && $.isNumeric(value)) {
                    return parseFloat(value) <= parseFloat(params);
                }

                return true;
            },
            $.mage.__('Please enter a value less than or equal to {0}.')
        ],
        'greater-than-equals-to': [
            function (value, params) {
                if ($.isNumeric(params) && $.isNumeric(value)) {
                    return parseFloat(value) >= parseFloat(params);
                }

                return true;
            },
            $.mage.__('Please enter a value greater than or equal to {0}.')
        ],
        'validate-emails': [
            function (value) {
                var validRegexp, emails, i;

                if (utils.isEmpty(value)) {
                    return true;
                }
                validRegexp = /^[a-z0-9\._-]{1,30}@([a-z0-9_-]{1,30}\.){1,5}[a-z]{2,4}$/i;
                emails = value.split(/[\s\n\,]+/g);

                for (i = 0; i < emails.length; i++) {
                    if (!validRegexp.test(emails[i].strip())) {
                        return false;
                    }
                }

                return true;
            },
            $.mage.__('Please enter valid email addresses, separated by commas. For example, johndoe@domain.com, johnsmith@domain.com.')//eslint-disable-line max-len
        ],
        'validate-cc-number': [

            /**
             * Validate credit card number based on mod 10
             * @param {String} value - credit card number
             * @return {Boolean}
             */
            function (value) {
                if (value) {
                    return validateCreditCard(value);
                }

                return false;
            }, $.mage.__('Please enter a valid credit card number.')
        ],
        'validate-cc-ukss': [

            /**
             * Validate Switch/Solo/Maestro issue number and start date is filled
             * @param {String} value - input field value
             * @return {*}
             */
            function (value) {
                return value;
            }, $.mage.__('Please enter issue number or start date for switch/solo card type.')
        ],
        'required-entry': [
            function (value) {
                return !utils.isEmpty(value);
            }, $.mage.__('This is a required field.')
        ],
        'checked': [
            function (value) {
                return value;
            }, $.mage.__('This is a required field.')
        ],
        'not-negative-amount': [
            function (value) {
                if (value.length) {
                    return (/^\s*\d+([,.]\d+)*\s*%?\s*$/).test(value);
                }

                return true;
            },
            $.mage.__('Please enter positive number in this field.')
        ],
        'validate-per-page-value-list': [
            function (value) {
                var isValid = !utils.isEmpty(value),
                    values = value.split(','),
                    i;

                for (i = 0; i < values.length; i++) {
                    if (!/^[0-9]+$/.test(values[i])) {
                        isValid = false;
                    }
                }

                return isValid;
            },
            $.mage.__('Please enter a valid value, ex: 10,20,30')
        ],
        'validate-new-password': [
            function (value) {
                if ($.validator.methods['validate-password'] && !$.validator.methods['validate-password'](value)) {
                    return false;
                }

                if (utils.isEmpty(value) && value !== '') {
                    return false;
                }

                return true;
            },
            $.mage.__('Please enter 6 or more characters. Leading and trailing spaces will be ignored.')
        ],
        'validate-item-quantity': [
            function (value, params) {
                // obtain values for validation
                var qty = utils.parseNumber(value),
                    isMinAllowedValid = typeof params.minAllowed === 'undefined' ||
                        qty >= utils.parseNumber(params.minAllowed),
                    isMaxAllowedValid = typeof params.maxAllowed === 'undefined' ||
                        qty <= utils.parseNumber(params.maxAllowed),
                    isQtyIncrementsValid = typeof params.qtyIncrements === 'undefined' ||
                        qty % utils.parseNumber(params.qtyIncrements) === 0;

                return isMaxAllowedValid && isMinAllowedValid && isQtyIncrementsValid && qty > 0;
            },
            ''
        ],
        'equalTo': [
            function (value, param) {
                return value === $(param).val();
            },
            $.validator.messages.equalTo
        ],
        'validate-file-type': [
            function (name, types) {
                var extension = name.split('.').pop().toLowerCase();

                if (types && typeof types === 'string') {
                    types = types.split(' ');
                }

                return !types || !types.length || ~types.indexOf(extension);
            },
            $.mage.__('We don\'t recognize or support this file extension type.')
        ],
        'validate-max-size': [
            function (size, maxSize) {
                return maxSize === false || size < maxSize;
            },
            $.mage.__('File you are trying to upload exceeds maximum file size limit.')
        ],
        'validate-if-tag-script-exist': [
            function (value) {
                return !value || (/<script\b[^>]*>([\s\S]*?)<\/script>$/ig).test(value);
            },
            $.mage.__('Please use tag SCRIPT with SRC attribute or with proper content to include JavaScript to the document.')//eslint-disable-line max-len
        ],
        'date_range_min': [
            function (value, minValue, params) {
                return moment.utc(value, params.dateFormat).unix() >= minValue;
            },
            $.mage.__('The date is not within the specified range.')
        ],
        'date_range_max': [
            function (value, maxValue, params) {
                return moment.utc(value, params.dateFormat).unix() <= maxValue;
            },
            $.mage.__('The date is not within the specified range.')
        ]
    }, function (data) {
        return {
            handler: data[0],
            message: data[1]
        };
    });
});
