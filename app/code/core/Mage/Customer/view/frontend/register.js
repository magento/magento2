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
 * @category    customer frontend register
 * @package     mage
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
(function ($) {
    var registerInit = {};
    $.mage.event.trigger("mage.register.initialize", registerInit);

    if (registerInit.autocomplete === 'off') {
        $(registerInit.formSelector).find('input:text').attr('autocomplete', 'off');
    }
    // Custom validation for DOB field
    $.mage.event.observe('mage.form.afterValidation', function(evernt, oldForm) {
        var dobElement = $(registerInit.dobSelector),
            passRequired = true,
            day = parseInt(dobElement.find(registerInit.dobDaySelector).first().val(), 10)   || 0,
            month = parseInt(dobElement.find(registerInit.dobMonthSelector).first().val(), 10) || 0,
            year = parseInt(dobElement.find(registerInit.dobYearSelector).first().val(), 10)  || 0,
            curYear = (new Date()).getFullYear();
        if (dobElement.length === 0) {
            return;
        }
        function showError(msg) {
            dobElement.children('.validation-advice').html($.mage.__(msg)).show();
            dobElement.find('.validate-custom').addClass('mage-error').after(function (){
                return '<div id="advice-validate-custom-%s" class="validation-advice"></div>'.replace('%s', $(this).attr('id'));
            });
            return false;
        }
        dobElement.find('.validation-advice').hide();
        dobElement.find('.mage-error').removeClass('mage-error');
        // Check if DOB field is required
        if (dobElement.siblings('.required').length > 0) {
            dobElement.find('input.validate-custom').each(function() {
                var $this = $(this);
                passRequired = $this.val().length !== 0;
            });
            if (!passRequired) {
                oldForm.status = showError('This is a required field.');
                return;
            }
        }
        if (!day || !month || !year) {
            oldForm.status = showError('Please enter a valid full date.');
            return;
        }
        if (month < 1 || month > 12) {
            oldForm.status = showError('Please enter a valid month (1-12).');
            return;
        }
        if (year < 1900 || year > curYear) {
            oldForm.status = showError('Please enter a valid year (1900-%d).'.replace('%d', curYear));
            return;
        }
        if ($.inArray(month, [1, 3, 5, 7, 8, 10, 12]) >= 0 && (day < 1 || day > 31)) {
            oldForm.status = showError('Please enter a valid day (1-31).');
            return;
        }
        if ($.inArray(month, [4, 6, 9, 11]) >= 0 && (day < 1 || day > 30)) {
            oldForm.status = showError('Please enter a valid day (1-30).');
            return;
        }
        if (month === 2 && year % 4 === 0 && (day < 1 || day > 29)){
            oldForm.status = showError('Please enter a valid day (1-29).');
            return;
        }
        if (month === 2 && year % 4 !== 0 && (day < 1 || day > 28)){
            oldForm.status = showError('Please enter a valid day (1-28).');
            return;
        }
        // Format validate day for form submit
        day = day % 10 === day ? '0' + day : day;
        month = month % 10 === month ? '0' + month : month;
        $(registerInit.dobInputSelector).val(month + '/' + day + '/' + year);
    });
    $(registerInit.formSelector).mage().validate();
})(jQuery);
