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
 * @category    validation - dob rule
 * @package     mage
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint jquery:true*/
(function($) {
    "use strict";
    $.validator.addMethod(
        'validate-dob',
        function (val, element, params) {
            $('.customer-dob').find('.' + this.settings.errorClass).removeClass(this.settings.errorClass);
            var dayVal = $(params[0]).find('input:text').val(),
                monthVal = $(params[1]).find('input:text').val(),
                yearVal = $(params[2]).find('input:text').val(),
                dobLength = dayVal.length + monthVal.length + yearVal.length;
            if (params[3] && dobLength === 0) {
                this.dobErrorMessage = 'This is a required field.';
                return false;
            }
            if (!params[3] && dobLength === 0) {
                return true;
            }
            var day = parseInt(dayVal, 10) || 0,
                month = parseInt(monthVal, 10) || 0,
                year = parseInt(yearVal, 10) || 0,
                curYear = (new Date()).getFullYear();
            if (!day || !month || !year) {
                this.dobErrorMessage = 'Please enter a valid full date.';
                return false;
            }
            if (month < 1 || month > 12) {
                this.dobErrorMessage = 'Please enter a valid month (1-12).';
                return false;
            }
            if (year < 1900 || year > curYear) {
                this.dobErrorMessage = $.mage.__('Please enter a valid year (1900-%d).').replace('%d', curYear);
                return false;
            }
            var validateDayInMonth = new Date(year, month, 0).getDate();
            if (day < 1 || day > validateDayInMonth) {
                this.dobErrorMessage = $.mage.__('Please enter a valid day (1-%d).').replace('%d', validateDayInMonth);
                return false;
            }
            day = day % 10 === day ? '0' + day : day;
            month = month % 10 === month ? '0' + month : month;
            $(element).val(month + '/' + day + '/' + year);
            return true;
        },
        function(){
            return this.dobErrorMessage;
        }
    );
})(jQuery);
