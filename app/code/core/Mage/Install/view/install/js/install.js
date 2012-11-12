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
 * @category    mage install
 * @package     mage
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true */
(function($) {
    $.widget('mage.begin', {
        /**
         * Beginning phase of the installation process. Check the box to agree to Terms and
         * Conditions, License, etc. and then click the Continue button.
         */
        _create: function() {
            this.element.on('click', $.proxy(function(e) {
                var btn = $(this.options.submitButtonSelector);
                if (e.target.checked) {
                    btn.removeClass('mage-disabled').addClass('mage-enabled')
                        .removeAttr('disabled');
                } else {
                    btn.removeClass('mage-enabled').addClass('mage-disabled')
                        .attr('disabled', 'disabled');
                }
            }, this));
        }
    });

    $.widget('mage.configure', {
        /**
         * Configuration phase. Prompt for hostname, database information, and options,
         * such as whether to enable SSL, referred to as secure options.
         */
        _create: function() {
            if (this.options.url) {
                this.element.on('click', $.proxy(function() {
                    location.href = this.options.url;
                }, this));
            } else {
                this.element.mage().validate();
                $(this.options.useSecureSelector).on('click', $.proxy(function(e) {
                    return e.target.checked ?
                        $(this.options.useSecureOptionsSelector).show() :
                        $(this.options.useSecureOptionsSelector).hide();
                }, this));
            }
        }
    });

    $.widget('mage.createAdmin', {
        /**
         * Create backend administrator login form validation. Enter user's name, email,
         * admin username, and password. Validate the form.
         */
        _create: function() {
            this.element.mage().validate({errorClass: 'mage-error', errorElement: 'div'});
        }
    });

    $.widget('mage.setLocale', {
        /**
         * Generate a new URL whenever a different locale is selected and refresh the
         * page to that new locale based URL.
         */
        _create: function() {
            this.element.on('change', $.proxy(function() {
                location.href = this.options.url + 'locale/' + this.element.val() +
                    '/?timezone=' + $('#timezone').val() + '&amp;currency=' + $('#currency').val();
            }, this));
        }
    });
})(jQuery);
