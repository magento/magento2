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
    $.validator.addMethod("allowContainerClassName", function (element) {
        if (element.type === 'radio' || element.type === 'checkbox') {
            return $(element).hasClass('change-container-classname');
        }
    }, '');

    $.validator.addMethod("validateNoHtmlTags", function (value) {
        return !/<(\/)?\w+/.test(value);
    }, $.mage.__('HTML tags are not allowed'));

    $.validator.addMethod("validateSelect", function (value) {
        return ((value !== "none") && (value != null) && (value.length !== 0));
    }, $.mage.__('Please select an option'));

    $.validator.addMethod("isEmpty", function (value) {
        return  (value === '' || (value == null) || (value.length === 0) || /^\s+$/.test(value));
    }, $.mage.__('Empty Value'));

    //(function () {
    function isEmpty(value) {
        return  (value === '' || (value == null) || (value.length === 0) || /^\s+$/.test(value));
    }

    function isEmptyNoTrim(value) {
        return  (value === '' || (value == null) || (value.length === 0));
    }

    function parseNumber(value) {
        if (typeof value !== 'string') {
            return parseFloat(value);
        }
        var isDot = value.indexOf('.');
        var isComa = value.indexOf(',');
        if (isDot !== -1 && isComa !== -1) {
            if (isComa > isDot) {
                value = value.replace('.', '').replace(',', '.');
            }
            else {
                value = value.replace(',', '');
            }
        }
        else if (isComa !== -1) {
            value = value.replace(',', '.');
        }
        return parseFloat(value);
    }

    $.validator.addMethod("validateAlphanumWithSpaces", function (v) {
        return isEmptyNoTrim(v) || /^[a-zA-Z0-9 ]+$/.test(v);
    }, $.mage.__('Please use only letters (a-z or A-Z), numbers (0-9) or spaces only in this field'));

    $.validator.addMethod("validateData", function (v) {
        return isEmptyNoTrim(v) || /^[A-Za-z]+[A-Za-z0-9_]+$/.test(v);
    }, $.mage.__('Please use only letters (a-z or A-Z), numbers (0-9) or underscore(_) in this field, first character should be a letter.'));

    $.validator.addMethod("validateStreet", function (v) {
        return isEmptyNoTrim(v) || /^[ \w]{3,}([A-Za-z]\.)?([ \w]*\#\d+)?(\r\n| )[ \w]{3,}/.test(v);
    }, $.mage.__('Please use only letters (a-z or A-Z) or numbers (0-9) or spaces and # only in this field'));

    $.validator.addMethod("validatePhoneStrict", function (v) {
        return isEmptyNoTrim(v) || /^(\()?\d{3}(\))?(-|\s)?\d{3}(-|\s)\d{4}$/.test(v);
    }, $.mage.__('Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.'));

    $.validator.addMethod("validatePhoneLax", function (v) {
        return isEmptyNoTrim(v) || /^((\d[\-. ]?)?((\(\d{3}\))|\d{3}))?[\-. ]?\d{3}[\-. ]?\d{4}$/.test(v);
    }, $.mage.__('Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.'));

    $.validator.addMethod("validateFax", function (v) {
        return isEmptyNoTrim(v) || /^(\()?\d{3}(\))?(-|\s)?\d{3}(-|\s)\d{4}$/.test(v);
    }, $.mage.__('Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.'));

    $.validator.addMethod("validateEmail", function (v) {
        return isEmptyNoTrim(v) || /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(v);
    }, $.mage.__('Please enter a valid email address. For example johndoe@domain.com.'));

    $.validator.addMethod("validateEmailSender", function (v) {
        return isEmptyNoTrim(v) || /^[\S ]+$/.test(v);
    }, $.mage.__('Please enter a valid email address. For example johndoe@domain.com.'));

    $.validator.addMethod("validatePassword", function (v) {
        if (v == null) {
            return false;
        }
        var pass = $.trim(v);
        /*strip leading and trailing spaces*/
        if (0 === pass.length) {
            return true;
        }
        /*strip leading and trailing spaces*/
        return !(pass.length > 0 && pass.length < 6);
    }, $.mage.__('Please enter 6 or more characters. Leading or trailing spaces will be ignored.'));

    $.validator.addMethod("validateAdminPassword", function (v) {
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
    }, $.mage.__('Please enter 7 or more characters. Password should contain both numeric and alphabetic characters.'));

    $.validator.addMethod("validateUrl", function (v) {
        if (isEmptyNoTrim(v)) {
            return true;
        }
        v = (v || '').replace(/^\s+/, '').replace(/\s+$/, '');
        return (/^(http|https|ftp):\/\/(([A-Z0-9]([A-Z0-9_-]*[A-Z0-9]|))(\.[A-Z0-9]([A-Z0-9_-]*[A-Z0-9]|))*)(:(\d+))?(\/[A-Z0-9~](([A-Z0-9_~-]|\.)*[A-Z0-9~]|))*\/?(.*)?$/i).test(v);

    }, $.mage.__('Please enter a valid URL. Protocol is required (http://, https:// or ftp://).'));

    $.validator.addMethod("validateCleanUrl", function (v) {
        return isEmptyNoTrim(v) || /^(http|https|ftp):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+.(com|org|net|dk|at|us|tv|info|uk|co.uk|biz|se)$)(:(\d+))?\/?/i.test(v) || /^(www)((\.[A-Z0-9][A-Z0-9_-]*)+.(com|org|net|dk|at|us|tv|info|uk|co.uk|biz|se)$)(:(\d+))?\/?/i.test(v);

    }, $.mage.__('Please enter a valid URL. For example http://www.example.com or www.example.com'));

    $.validator.addMethod("validateXmlIdentifier", function (v) {
        return isEmptyNoTrim(v) || /^[A-Z][A-Z0-9_\/-]*$/i.test(v);

    }, $.mage.__('Please enter a valid URL. For example http://www.example.com or www.example.com'));

    $.validator.addMethod("validateSsn", function (v) {
        return isEmptyNoTrim(v) || /^\d{3}-?\d{2}-?\d{4}$/.test(v);

    }, $.mage.__('Please enter a valid social security number. For example 123-45-6789.'));

    $.validator.addMethod("validateZip", function (v) {
        return isEmptyNoTrim(v) || /(^\d{5}$)|(^\d{5}-\d{4}$)/.test(v);

    }, $.mage.__('Please enter a valid zip code. For example 90602 or 90602-1234.'));

    $.validator.addMethod("validateDateAu", function (v) {
        if (isEmptyNoTrim(v)) return true;
        var regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
        if (isEmpty(v) || !regex.test(v)) return false;
        var d = new Date(v.replace(regex, '$2/$1/$3'));
        return ( parseInt(RegExp.$2, 10) === (1 + d.getMonth()) ) &&
            (parseInt(RegExp.$1, 10) === d.getDate()) &&
            (parseInt(RegExp.$3, 10) === d.getFullYear() );

    }, $.mage.__('Please use this date format: dd/mm/yyyy. For example 17/03/2006 for the 17th of March, 2006.'));

    $.validator.addMethod("validateCurrencyDollar", function (v) {
        return isEmptyNoTrim(v) || /^\$?\-?([1-9]{1}[0-9]{0,2}(\,[0-9]{3})*(\.[0-9]{0,2})?|[1-9]{1}\d*(\.[0-9]{0,2})?|0(\.[0-9]{0,2})?|(\.[0-9]{1,2})?)$/.test(v);

    }, $.mage.__('Please enter a valid $ amount. For example $100.00.'));

    $.validator.addMethod("validateNotNegativeNumber", function (v) {
        if (isEmptyNoTrim(v)) {
            return true;
        }
        v = parseNumber(v);
        return !isNaN(v) && v >= 0;

    }, $.mage.__('Please select one of the above options.'));

    $.validator.addMethod("validateGreaterThanZero", function (v) {
        if (isEmptyNoTrim(v)) {
            return true;
        }
        v = parseNumber(v);
        return !isNaN(v) && v > 0;
    }, $.mage.__('Please enter a number greater than 0 in this field'));

    $.validator.addMethod("validateCssLength", function (v) {
        if (isEmptyNoTrim(v)) {
            return true;
        }
        v = parseNumber(v);
        return !isNaN(v) && v > 0;
    }, $.mage.__("Please enter a number greater than 0 in this field"));
    // })($);

    $.extend($.validator.messages, {
        required: $.mage.__("This is a required field."),
        remote: $.mage.__("Please fix this field."),
        email: $.mage.__("Please enter a valid email address."),
        url: $.mage.__("Please enter a valid URL."),
        date: $.mage.__("Please enter a valid date."),
        dateISO: $.mage.__("Please enter a valid date (ISO)."),
        number: $.mage.__("Please enter a valid number."),
        digits: $.mage.__("Please enter only digits."),
        creditcard: $.mage.__("Please enter a valid credit card number."),
        equalTo: $.mage.__("Please make sure your passwords match."),
        accept: $.mage.__("Please enter a value with a valid extension."),
        maxlength: $.validator.format($.mage.__("Please enter no more than {0} characters.")),
        minlength: $.validator.format($.mage.__("Please enter at least {0} characters.")),
        rangelength: $.validator.format($.mage.__("Please enter a value between {0} and {1} characters long.")),
        range: $.validator.format($.mage.__("Please enter a value between {0} and {1}.")),
        max: $.validator.format($.mage.__("Please enter a value less than or equal to {0}.")),
        min: $.validator.format($.mage.__("Please enter a value greater than or equal to {0}."))
    });

// Setting the type as html5 to enable data-validate
    $.metadata.setType("html5");

    /*
     $ plugin for validator
     eg:$("#formId").mage().validate()
     */
    $.fn.mage = function () {
        var jq = this;
        return {
            validate: function (options) {
                var defaultOptions = $.extend({
                    meta: "validate",
                    onfocusout: false,
                    onkeyup: false,
                    onclick: false,
                    ignoreTitle: true,
                    errorClass: 'mage-error',
                    errorElement: 'div'
                }, options);
                return jq.each(function () {
                    $(this).validate(defaultOptions);
                    $(this).mageEventFormValidate();
                });
            }
        };
    };

})(jQuery);

/**
 Not implemented
 ====================
 validate-date-range
 validate-both-passwords
 validate-one-required
 validate-one-required-by-name
 validate-state
 validate-new-password
 validate-cc-number
 */

