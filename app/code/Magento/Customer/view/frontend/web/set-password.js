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
/*jshint browser:true jquery:true expr:true*/
define([
    "jquery",
    "jquery/ui"
], function($){

    $.widget('mage.setPassword', {
        options: {
        },

        _create: function() {
            this.element.on('change', $.proxy(function(event) {
                $(event.target).is(':checked') ? this._showPassword() : this._hidePassword();
            }, this));
            this.options.showOnDefault && this._showPassword();
        },

        /**
         * Hide password input fields
         * @private
         */
        _hidePassword: function() {
            $(this.options.currentPasswordId).removeAttr('data-validate')
                .parents(this.options.passwordContainer).hide();
            $(this.options.passwordId).removeAttr('data-validate');
            $(this.options.confirmationId).removeAttr('data-validate');
        },

        /**
         * Show password input fields
         * @private
         */
        _showPassword: function() {
            $(this.options.currentPasswordId).attr('data-validate', '{required:true}')
                .parents(this.options.passwordContainer).show();
            $(this.options.passwordId).attr('data-validate', "{required:true, 'validate-password':true}");
            $(this.options.confirmationId).attr('data-validate',
                '{required:true, equalTo:"' + this.options.passwordId + '"}');
        }
    });
});