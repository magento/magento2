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
/*jshint browser:true jquery:true */
define([
    "jquery",
    "mage/mage",
    "mage/validation/validation"
], function($) {

    $.extend(true, $.mage, {
        install: {
            /**
             * Beginning phase of the installation process. Check the box to agree to Terms and
             * Conditions, License, etc. and then click the Continue button.
             * @param {string} agreeBox Selector for the agree checkbox
             * @param {string} submitButton Selector for the submit button
             */
            begin: function(agreeBox, submitButton) {
                $(agreeBox).on('click', function(e) {
                    var btn = $(submitButton);
                    if (e.target.checked) {
                        btn.removeClass('mage-disabled').addClass('mage-enabled')
                            .removeAttr('disabled');
                    } else {
                        btn.removeClass('mage-enabled').addClass('mage-disabled')
                            .attr('disabled', 'disabled');
                    }
                });
            },

            /**
             * Configuration phase. Prompt for hostname, database information, and options,
             * such as whether to enable SSL, referred to as secure options.
             * @param {string} form Selector for the configuration form
             * @param {string} useSecure Selector for the 'Use Secure (SSL)' checkbox
             * @param {string} useSecureOptions Selector for the secure (SSL) options content
             */
            configureForm: function(form, useSecure, useSecureOptions) {
                $(form).validation();
                $(useSecure).on('click', function(e) {
                    return e.target.checked ?
                        $(useSecureOptions).show() : $(useSecureOptions).hide();
                });
            },

            /**
             * Configure phase. Happens when an error occurs and intervention is needed. Prompts
             * the user to fix the issue and click a Continue button to proceed.
             * @param {string} continueButton Selector for the continue button
             * @param {string} url The URL to proceed to next after clicking the button
             */
            configureContinue: function(continueButton, url) {
                $(continueButton).on('click', function() {
                    location.href = url;
                });
            },

            /**
             * Create backend administrator login form validation. Enter user's name, email,
             * admin username, and password. Validate the form.
             * @param {string} form Selector for the administrator form
             */
            createAdmin: function(form) {
                $(form).validation();
            },

            /**
             * Generate a new URL whenever a different locale is selected and refresh the
             * page to that new locale based URL.
             * @param {string} localeField Selector for the locale input field
             * @param {string} url Partial URL used to construct full URL to change the locale
             */
            changeLocale: function(localeField, url) {
                $(localeField).on('change', function() {
                    location.href = url + 'locale/' + $(localeField).val() + '/?timezone=' +
                        $('#timezone').val() + '&amp;currency=' + $('#currency').val();
                });
            }
        }
    });

});