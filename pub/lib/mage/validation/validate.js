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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/*jshint regexdash:true */
jQuery.validator.addMethod("allowContainerClassName", function (element) {
  if ( element.type == 'radio' || element.type == 'checkbox' ) {
    return $(element).hasClass('change-container-classname');
  }
}, '');

/**
 * Equivalent of  validate-no-html-tags
 */

jQuery.validator.addMethod("validateNoHtmlTags", function (value) {
  return !/<(\/)?\w+/.test(value);
}, 'HTML tags are not allowed');

/**
 * Equivalent of  validate-select
 */
jQuery.validator.addMethod("validateSelect", function (value) {
  return ((value !== "none") && (value != null) && (value.length !== 0));
}, 'Please select an option');

jQuery.validator.addMethod("isEmpty", function (value) {
  return  (value === '' || (value == null) || (value.length === 0) || /^\s+$/.test(value));
}, 'Empty Value');

(function () {

  function isEmpty(value) {
    // remove html tags and space chars
    return  (value === '' || (value == null) || (value.length === 0) || /^\s+$/.test(value));
  }

  function parseNumber(value) {
    if ( typeof value != 'string' ) {
      return parseFloat(v);
    }

    var isDot = value.indexOf('.');
    var isComa = value.indexOf(',');

    if ( isDot != -1 && isComa != -1 ) {
      if ( isComa > isDot ) {
        value = value.replace('.', '').replace(',', '.');
      }
      else {
        value = value.replace(',', '');
      }
    }
    else if ( isComa != -1 ) {
      value = value.replace(',', '.');
    }

    return parseFloat(value);
  }

  /**
   * Equivalent of  validate-alphanum-with-spaces
   */
  jQuery.validator.addMethod("validateAlphanumWithSpaces", function (v) {
    return !isEmpty(v) && /^[a-zA-Z0-9 ]+$/.test(v);
  }, 'Please use only letters (a-z or A-Z), numbers (0-9) or spaces only in this field');

  /**
   * Equivalent of  validate-street
   */
  jQuery.validator.addMethod("validateStreet", function (v) {
    return !isEmpty(v) && /^[ \w]{3,}([A-Za-z]\.)?([ \w]*\#\d+)?(\r\n| )[ \w]{3,}/.test(v);
  }, 'Please use only letters (a-z or A-Z) or numbers (0-9) or spaces and # only in this field');

  /**
   * Equivalent of  validate-phoneStrict
   */
  jQuery.validator.addMethod("validatePhoneStrict", function (v) {
    return !isEmpty(v) && /^(\()?\d{3}(\))?(-|\s)?\d{3}(-|\s)\d{4}$/.test(v);
  }, 'Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.');

  /**
   * Equivalent of  validate-phoneLax
   */
  jQuery.validator.addMethod("validatePhoneLax", function (v) {
    return  !isEmpty(v) && /^((\d[-. ]?)?((\(\d{3}\))|\d{3}))?[-. ]?\d{3}[-. ]?\d{4}$/.test(v);
  }, 'Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.');

  /**
   * Equivalent of  validate-fax
   */
  jQuery.validator.addMethod("validateFax", function (v) {
    return  !isEmpty(v) && /^(\()?\d{3}(\))?(-|\s)?\d{3}(-|\s)\d{4}$/.test(v);
  }, 'Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.');

  /**
   * Equivalent of   validate-email
   */
  jQuery.validator.addMethod("validateEmail", function (v) {
    return  !isEmpty(v) && /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(v);
  }, 'Please enter a valid email address. For example johndoe@domain.com.');

  /**
   * Equivalent of   validate-emailSender
   */
  jQuery.validator.addMethod("validateEmailSender", function (v) {
    return !isEmpty(v) && /^[\S ]+$/.test(v);
  }, 'Please enter a valid email address. For example johndoe@domain.com.');

  /**
   * Equivalent of   validate-password
   */
  jQuery.validator.addMethod("validatePassword", function (v) {
    if ( isEmpty(v) ) {
      return false;
    }
    var pass = $.trim(v);
    /*strip leading and trailing spaces*/
    return !(pass.length > 0 && pass.length < 6);
  }, 'Please enter 6 or more characters. Leading or trailing spaces will be ignored.');

  /**
   * Equivalent of   validate-admin-password
   */
  jQuery.validator.addMethod("validateAdminPassword", function (v) {
    if ( v == null ) {
      return false;
    }
    var pass = $.trim(v);
    /*strip leading and trailing spaces*/
    if ( 0 === pass.length ) {
      return true;
    }
    if ( !(/[a-z]/i.test(v)) || !(/[0-9]/.test(v)) ) {
      return false;
    }
    if (pass.length < 7){
      return false;
    }
    return true;
  }, 'Please enter 7 or more characters. Password should contain both numeric and alphabetic characters.');

  /**
   * Equivalent of   validate-url
   */
  jQuery.validator.addMethod("validateUrl", function (v) {
    if ( isEmpty(v) ) {
      return false;
    }
    v = (v || '').replace(/^\s+/, '').replace(/\s+$/, '');
    return /^(http|https|ftp):\/\/(([A-Z0-9]([A-Z0-9_-]*[A-Z0-9]|))(\.[A-Z0-9]([A-Z0-9_-]*[A-Z0-9]|))*)(:(\d+))?(\/[A-Z0-9~](([A-Z0-9_~-]|\.)*[A-Z0-9~]|))*\/?(.*)?$/i.test(v);

  }, 'Please enter a valid URL. Protocol is required (http://, https:// or ftp://).');

  /**
   * Equivalent of   validate-clean-url
   */
  jQuery.validator.addMethod("validateCleanUrl", function (v) {
    return  !isEmpty(v) && /^(http|https|ftp):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+.(com|org|net|dk|at|us|tv|info|uk|co.uk|biz|se)$)(:(\d+))?\/?/i.test(v) || /^(www)((\.[A-Z0-9][A-Z0-9_-]*)+.(com|org|net|dk|at|us|tv|info|uk|co.uk|biz|se)$)(:(\d+))?\/?/i.test(v);

  }, 'Please enter a valid URL. For example http://www.example.com or www.example.com');

  /**
   * Equivalent of   validate-xml-identifier
   */
  jQuery.validator.addMethod("validateXmlIdentifier", function (v) {
    return !isEmpty(v) && /^[A-Z][A-Z0-9_\/-]*$/i.test(v);

  }, 'Please enter a valid URL. For example http://www.example.com or www.example.com');

  /**
   * Equivalent of   validate-ssn
   */
  jQuery.validator.addMethod("validateSsn", function (v) {
    return !isEmpty(v) && /^\d{3}-?\d{2}-?\d{4}$/.test(v);

  }, 'Please enter a valid social security number. For example 123-45-6789.');

  /**
   * Equivalent of   validate-zip
   */
  jQuery.validator.addMethod("validateZip", function (v) {
    return !isEmpty(v) && /(^\d{5}$)|(^\d{5}-\d{4}$)/.test(v);

  }, 'Please enter a valid zip code. For example 90602 or 90602-1234.');

  /**
   * Equivalent of       validate-date-au
   */
  jQuery.validator.addMethod("validateDateAu", function (v) {
    var regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
    if ( isEmpty(v) || !regex.test(v) ) return false;
    var d = new Date(v.replace(regex, '$2/$1/$3'));
    return ( parseInt(RegExp.$2, 10) == (1 + d.getMonth()) ) &&
      (parseInt(RegExp.$1, 10) == d.getDate()) &&
      (parseInt(RegExp.$3, 10) == d.getFullYear() );

  }, 'Please use this date format: dd/mm/yyyy. For example 17/03/2006 for the 17th of March, 2006.');

  /**
   * Equivalent of   validate-currency-dollar
   */
  jQuery.validator.addMethod("validateCurrencyDollar", function (v) {
    return !isEmpty(v) && /^\$?\-?([1-9]{1}[0-9]{0,2}(\,[0-9]{3})*(\.[0-9]{0,2})?|[1-9]{1}\d*(\.[0-9]{0,2})?|0(\.[0-9]{0,2})?|(\.[0-9]{1,2})?)$/.test(v);

  }, 'Please enter a valid $ amount. For example $100.00.');

  /**
   * Equivalent of   validate-not-negative-number
   */
  jQuery.validator.addMethod("validateNotNegativeNumber", function (v) {
    if ( isEmpty(v) ) {
      return false;
    }
    v = parseNumber(v);
    return !isNaN(v) && v >= 0;

  }, 'Please select one of the above options.');

  /**
   * Equivalent of   validate-greater-than-zero
   */

  jQuery.validator.addMethod("validateGreaterThanZero", function (v) {
    if ( isEmpty(v) ) {
      return false;
    }
    v = parseNumber(v);
    return !isNaN(v) && v > 0;
  }, "Please enter a number greater than 0 in this field");

  /**
   * Equivalent of   validate-css-length
   */

  jQuery.validator.addMethod("validateCssLength", function (v) {
    if ( isEmpty(v) ) {
      return false;
    }
    v = parseNumber(v);
    return !isNaN(v) && v > 0;
  }, "Please enter a number greater than 0 in this field");

})();

/**
 Not implemented
 ====================
 validate-date-range
 validate-cpassword
 validate-both-passwords
 validate-one-required
 validate-one-required-by-name
 validate-state
 validate-new-password
 validate-cc-number


 */

