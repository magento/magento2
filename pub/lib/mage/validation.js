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
 * @category    validation
 * @package     mage
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint regexdash:true eqnull:true browser:true jquery:true*/
(function ($) {
    $.extend(true, $, {
        //@TODO: Move methods 'isEmpty', 'isEmptyNoTrim', 'parseNumber' in file with utility functions
        mage: {
            /**
             * Check if string is empty with trim
             * @param {string}
             */
            isEmpty: function(value) {
                return (value === '' || (value == null) || (value.length === 0) || /^\s+$/.test(value));
            },

            /**
             * Check if string is empty no trim
             * @param {string}
             */
            isEmptyNoTrim: function(value) {
                return (value === '' || (value == null) || (value.length === 0));
            },

            /**
             * Parse price string
             * @param {string}
             */
            parseNumber: function(value) {
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
            }
        }
    });

    /**
     * Collection of validation rules
     * @type {Object}
     */
    var rules = {
        "allow-container-className": [
            function(element) {
                if (element.type === 'radio' || element.type === 'checkbox') {
                    return $(element).hasClass('change-container-classname');
                }
            },
            ''
        ],
        "validate-no-html-tags": [
            function(value) {
                return !/<(\/)?\w+/.test(value);
            },
            'HTML tags are not allowed'
        ],
        "validate-select": [
            function(value) {
                return ((value !== "none") && (value != null) && (value.length !== 0));
            },
            'Please select an option'
        ],
        "is-empty": [
            $.mage.isEmpty,
            'Empty Value'
        ],
        "validate-alphanum-with-spaces": [
            function(v) {
                return $.mage.isEmptyNoTrim(v) || /^[a-zA-Z0-9 ]+$/.test(v);
            },
            'Please use only letters (a-z or A-Z), numbers (0-9) or spaces only in this field'
        ],
        "validate-data": [
            function(v) {
                return $.mage.isEmptyNoTrim(v) || /^[A-Za-z]+[A-Za-z0-9_]+$/.test(v);
            },
            'Please use only letters (a-z or A-Z), numbers (0-9) or underscore(_) in this field, first character should be a letter.'
        ],
        "validate-street": [
            function(v) {
                return $.mage.isEmptyNoTrim(v) || /^[ \w]{3,}([A-Za-z]\.)?([ \w]*\#\d+)?(\r\n| )[ \w]{3,}/.test(v);
            },
            'Please use only letters (a-z or A-Z) or numbers (0-9) or spaces and # only in this field'
        ],
        "validate-phoneStrict": [
            function(v) {
                return $.mage.isEmptyNoTrim(v) || /^(\()?\d{3}(\))?(-|\s)?\d{3}(-|\s)\d{4}$/.test(v);
            },
            'Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.'
        ],
        "validate-phoneLax": [
            function(v) {
                return $.mage.isEmptyNoTrim(v) || /^((\d[\-. ]?)?((\(\d{3}\))|\d{3}))?[\-. ]?\d{3}[\-. ]?\d{4}$/.test(v);
            },
            'Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.'
        ],
        "validate-fax": [
            function(v) {
                return $.mage.isEmptyNoTrim(v) || /^(\()?\d{3}(\))?(-|\s)?\d{3}(-|\s)\d{4}$/.test(v);
            },
            'Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.'
        ],
        "validate-email": [
            function(v) {
                return $.mage.isEmptyNoTrim(v) || /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(v);
            },
            'Please enter a valid email address. For example johndoe@domain.com.'
        ],
        "validate-emailSender": [
            function(v) {
                return $.mage.isEmptyNoTrim(v) || /^[\S ]+$/.test(v);
            },
            'Please enter a valid email address. For example johndoe@domain.com.'
        ],
        "validate-password": [
            function(v) {
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
            'Please enter 6 or more characters. Leading or trailing spaces will be ignored.'
        ],
        "validate-admin-password": [
            function(v) {
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
            'Please enter 7 or more characters. Password should contain both numeric and alphabetic characters.'
        ],
        "validate-url": [
            function(v) {
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }
                v = (v || '').replace(/^\s+/, '').replace(/\s+$/, '');
                return (/^(http|https|ftp):\/\/(([A-Z0-9]([A-Z0-9_-]*[A-Z0-9]|))(\.[A-Z0-9]([A-Z0-9_-]*[A-Z0-9]|))*)(:(\d+))?(\/[A-Z0-9~](([A-Z0-9_~-]|\.)*[A-Z0-9~]|))*\/?(.*)?$/i).test(v);

            },
            'Please enter a valid URL. Protocol is required (http://, https:// or ftp://).'
        ],
        "validate-clean-url": [
            function(v) {
                return $.mage.isEmptyNoTrim(v) || /^(http|https|ftp):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+.(com|org|net|dk|at|us|tv|info|uk|co.uk|biz|se)$)(:(\d+))?\/?/i.test(v) || /^(www)((\.[A-Z0-9][A-Z0-9_-]*)+.(com|org|net|dk|at|us|tv|info|uk|co.uk|biz|se)$)(:(\d+))?\/?/i.test(v);

            },
            'Please enter a valid URL. For example http://www.example.com or www.example.com'
        ],
        "validate-xml-identifier": [
            function(v) {
                return $.mage.isEmptyNoTrim(v) || /^[A-Z][A-Z0-9_\/-]*$/i.test(v);

            },
            'Please enter a valid URL. For example http://www.example.com or www.example.com'
        ],
        "validate-ssn": [
            function(v) {
                return $.mage.isEmptyNoTrim(v) || /^\d{3}-?\d{2}-?\d{4}$/.test(v);

            },
            'Please enter a valid social security number. For example 123-45-6789.'
        ],
        "validate-zip": [
            function(v) {
                return $.mage.isEmptyNoTrim(v) || /(^\d{5}$)|(^\d{5}-\d{4}$)/.test(v);

            },
            'Please enter a valid zip code. For example 90602 or 90602-1234.'
        ],
        "validate-date-au": [
            function(v) {
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
            function(v) {
                return $.mage.isEmptyNoTrim(v) || /^\$?\-?([1-9]{1}[0-9]{0,2}(\,[0-9]{3})*(\.[0-9]{0,2})?|[1-9]{1}\d*(\.[0-9]{0,2})?|0(\.[0-9]{0,2})?|(\.[0-9]{1,2})?)$/.test(v);

            },
            'Please enter a valid $ amount. For example $100.00.'
        ],
        "validate-not-negative-number": [
            function(v) {
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }
                v = $.mage.parseNumber(v);
                return !isNaN(v) && v >= 0;

            },
            'Please select one of the above options.'
        ],
        "validate-greater-than-zero": [
            function(v) {
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }
                v = $.mage.parseNumber(v);
                return !isNaN(v) && v > 0;
            },
            'Please enter a number greater than 0 in this field'
        ],
        "validate-css-length": [
            function(v) {
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }
                v = $.mage.parseNumber(v);
                return !isNaN(v) && v > 0;
            },
            'Please enter a number greater than 0 in this field'
        ],
        /** @description Additional methods */
        "validate-number": [
            function(v) {
                return $.mage.isEmptyNoTrim(v) || (!isNaN($.mage.parseNumber(v)) && /^\s*-?\d*(\.\d*)?\s*$/.test(v));
            },
            'Please enter a valid number in this field.'
        ],
        "validate-number-range": [
            function(v, elm) {
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }

                var numValue = $.mage.parseNumber(v);
                if (isNaN(numValue)) {
                    return false;
                }

                var reRange = /^number-range-(-?[\d.,]+)?-(-?[\d.,]+)?$/,
                    result = true;

                var values = elm.className.split(" ");

                for (var i = values.length - 1; i >= 0; i--) {
                    var name = values[i];
                    var m = reRange.exec(name);
                    if (m) {
                        result = result &&
                            (m[1] == null || m[1] === '' || numValue >= $.mage.parseNumber(m[1])) &&
                            (m[2] == null || m[2] === '' || numValue <= $.mage.parseNumber(m[2]));
                    }
                }

                return result;
            },
            'The value is not within the specified range.'
        ],
        "validate-digits": [
            function(v) {
                return $.mage.isEmptyNoTrim(v) || !/[^\d]/.test(v);
            },
            'Please enter a valid number in this field.'
        ],
        "validate-digits-range": [
            function(v, elm) {
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }

                var numValue = $.mage.parseNumber(v);
                if (isNaN(numValue)) {
                    return false;
                }

                var reRange = /^digits-range-(-?\d+)?-(-?\d+)?$/,
                    result = true;

                var values = elm.className.split(" ");

                for (var i = values.length - 1; i >= 0; i--) {
                    var name = values[i];
                    var m = reRange.exec(name);
                    if (m) {
                        result = result &&
                            (m[1] == null || m[1] === '' || numValue >= $.mage.parseNumber(m[1])) &&
                            (m[2] == null || m[2] === '' || numValue <= $.mage.parseNumber(m[2]));
                    }
                }

                return result;
            },
            'Please enter a valid number in this field.'
        ],
        /*
        'validate-range': [
            function(v, elm) {
                var minValue, maxValue;
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                // @TODO: Replace Validation.get with appropriate variant
                } else if (Validation.get('validate-digits').test(v)) {
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

                var values = elm.className.split(" ");

                for (var i = values.length - 1; i >= 0; i--) {
                    var name = values[i];
                    var validRange = reRange.exec(name);
                    if (validRange) {
                        var minValidRange = $.mage.parseNumber(validRange[1]);
                        var maxValidRange = $.mage.parseNumber(validRange[2]);
                        result = result
                            && (isNaN(minValidRange) || minValue >= minValidRange)
                            && (isNaN(maxValidRange) || maxValue <= maxValidRange);
                    }
                };
                return result;
            },
            'The value is not within the specified range.'
        ],
        */
        "validate-alpha": [
            function(v) {
                return $.mage.isEmptyNoTrim(v) || /^[a-zA-Z]+$/.test(v);
            },
            'Please use letters only (a-z or A-Z) in this field.'
        ],
        "validate-code": [
            function(v) {
                return $.mage.isEmptyNoTrim(v) || /^[a-z]+[a-z0-9_]+$/.test(v);
            },
            'Please use only letters (a-z), numbers (0-9) or underscore(_) in this field, first character should be a letter.'
        ],
        "validate-alphanum": [
            function(v) {
                return $.mage.isEmptyNoTrim(v) || /^[a-zA-Z0-9]+$/.test(v);
            },
            'Please use only letters (a-z or A-Z) or numbers (0-9) only in this field. No spaces or other characters are allowed.'
        ],
        "validate-date": [
            function(v) {
                var test = new Date(v);
                return $.mage.isEmptyNoTrim(v) || !isNaN(test);
            }
        ],
        "validate-date-range": [
            function(v, elm) {
                var m = /\bdate-range-(\w+)-(\w+)\b/.exec(elm.className);
                if (!m || m[2] === 'to' || $.mage.isEmptyNoTrim(v)) {
                    return true;
                }

                var currentYear = new Date().getFullYear() + '';
                var normalizedTime = function(v) {
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
            'The From Date value should be less than or equal to the To Date value.'
        ],
        "validate-cpassword": [
            function() {
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
        /*
        "validate-both-passwords": [
            function(v, input) {
                var dependentInput = $(input.form[input.name == 'password' ? 'confirmation' : 'password']),
                    isEqualValues  = input.value == dependentInput.value;

                if (isEqualValues && dependentInput.hasClass('validation-failed')) {
                    // @TODO: Move test method to new validation
                    Validation.test(this.className, dependentInput);
                }

                return dependentInput.value == '' || isEqualValues;
            },
            'Please make sure your passwords match.'
        ]
        */
        "validate-identifier": [
            function(v) {
                return $.mage.isEmptyNoTrim(v) || /^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/.test(v);
            },
            'Please enter a valid URL Key. For example "example-page", "example-page.html" or "anotherlevel/example-page".'
        ],
        "validate-zip-international": [
            /*function(v) {
                // @TODO: Cleanup
                return Validation.get('IsEmpty').test(v) || /(^[A-z0-9]{2,10}([\s]{0,1}|[\-]{0,1})[A-z0-9]{2,10}$)/.test(v);
            }*/
            function() {
                return true;
            },
            'Please enter a valid zip code.'
        ],
        "validate-one-required": [
            function(v,elm) {
                var p = $(elm).parent();
                var options = p.find('input');
                return options.map(function(elm) {
                    return $(elm).val();
                }).length > 0;
            }
        ],
        "validate-state": [
            function(v) {
                return (v !== 0 || v === '');
            },
            'Please select State/Province.'
        ],
        "required-file": [
            function(v, elm) {
                 var result = !$.mage.isEmptyNoTrim(v);
                 if (!result) {
                     var ovId = $(elm).attr('id') + '_value';
                     if ($(ovId)) {
                         result = !$.mage.isEmptyNoTrim($(ovId).val());
                     }
                 }
                 return result;
             },
            'Please select a file'
        ],
        'validate-super-product-attributes': [
            function(v) {
                return (v !== "no-attributes");
            },
            'Please select one or more attributes.'
        ],
        "validate-ajax-error": [
            function(v, element) {
                element = $(element);
                element.on('change.ajaxError', function() {
                    element.removeClass('validate-ajax-error');
                    element.off('change.ajaxError');
                });
                return !element.hasClass('validate-ajax-error');
            },
            ''
        ]
    };

    $.each(rules, function(i, rule) {
        rule.unshift(i);
        $.validator.addMethod.apply($.validator, rule);
    });
    $.validator.addClassRules("required-entry", {
        required: true
    });
    $.validator.addClassRules("required-option", {
        required: true
    });

    var showLabel = $.validator.prototype.showLabel;
    $.extend(true, $.validator.prototype, {
        showLabel: function(element, message) {
            showLabel.call(this, element, $.mage.__(message));
        }
    });

    $.widget("mage.validation", {
        options: {
            meta: "validate",
            onfocusout: false,
            onkeyup: false,
            onclick: false,
            ignoreTitle: true,
            errorClass: 'mage-error',
            errorElement: 'div'
        },
        /**
         * Validation creation
         * @protected
         */
        _create: function(){
            this.validate = this.element.validate(this.options);
        }
    });
})(jQuery);

/**
 Not implemented
 ====================
 validate-both-passwords
 validate-one-required-by-name
 validate-new-password
 validate-cc-number
 */

