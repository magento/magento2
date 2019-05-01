/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.changeEmailPassword', {
        options: {
            changeEmailSelector: '[data-role=change-email]',
            changePasswordSelector: '[data-role=change-password]',
            mainContainerSelector: '[data-container=change-email-password]',
            titleSelector: '[data-title=change-email-password]',
            emailContainerSelector: '[data-container=change-email]',
            newPasswordContainerSelector: '[data-container=new-password]',
            confirmPasswordContainerSelector: '[data-container=confirm-password]',
            currentPasswordSelector: '[data-input=current-password]',
            emailSelector: '[data-input=change-email]',
            newPasswordSelector: '[data-input=new-password]',
            confirmPasswordSelector: '[data-input=confirm-password]'
        },

        /**
         * Create widget
         * @private
         */
        _create: function () {
            this.element.on('change', $.proxy(function () {
                this._checkChoice();
            }, this));

            this._checkChoice();
            this._bind();
        },

        /**
         * Event binding, will monitor change, keyup and paste events.
         * @private
         */
        _bind: function () {
            this._on($(this.options.emailSelector), {
                'change': this._updatePasswordFieldWithEmailValue,
                'keyup': this._updatePasswordFieldWithEmailValue,
                'paste': this._updatePasswordFieldWithEmailValue
            });
        },

        /**
         * Check choice
         * @private
         */
        _checkChoice: function () {
            if ($(this.options.changeEmailSelector).is(':checked') &&
                $(this.options.changePasswordSelector).is(':checked')) {
                this._showAll();
            } else if ($(this.options.changeEmailSelector).is(':checked')) {
                this._showEmail();
            } else if ($(this.options.changePasswordSelector).is(':checked')) {
                this._showPassword();
            } else {
                this._hideAll();
            }
        },

        /**
         * Show email and password input fields
         * @private
         */
        _showAll: function () {
            $(this.options.titleSelector).html(this.options.titleChangeEmailAndPassword);

            $(this.options.mainContainerSelector).show();
            $(this.options.emailContainerSelector).show();
            $(this.options.newPasswordContainerSelector).show();
            $(this.options.confirmPasswordContainerSelector).show();

            $(this.options.currentPasswordSelector).attr('data-validate', '{required:true}').prop('disabled', false);
            $(this.options.emailSelector).attr('data-validate', '{required:true}').prop('disabled', false);
            this._updatePasswordFieldWithEmailValue();
            $(this.options.confirmPasswordSelector).attr(
                'data-validate',
                '{required:true, equalTo:"' + this.options.newPasswordSelector + '"}'
            ).prop('disabled', false);
        },

        /**
         * Hide email and password input fields
         * @private
         */
        _hideAll: function () {
            $(this.options.mainContainerSelector).hide();
            $(this.options.emailContainerSelector).hide();
            $(this.options.newPasswordContainerSelector).hide();
            $(this.options.confirmPasswordContainerSelector).hide();

            $(this.options.currentPasswordSelector).removeAttr('data-validate').prop('disabled', true);
            $(this.options.emailSelector).removeAttr('data-validate').prop('disabled', true);
            $(this.options.newPasswordSelector).removeAttr('data-validate').prop('disabled', true);
            $(this.options.confirmPasswordSelector).removeAttr('data-validate').prop('disabled', true);
        },

        /**
         * Show email input fields
         * @private
         */
        _showEmail: function () {
            this._showAll();
            $(this.options.titleSelector).html(this.options.titleChangeEmail);

            $(this.options.newPasswordContainerSelector).hide();
            $(this.options.confirmPasswordContainerSelector).hide();

            $(this.options.newPasswordSelector).removeAttr('data-validate').prop('disabled', true);
            $(this.options.confirmPasswordSelector).removeAttr('data-validate').prop('disabled', true);
        },

        /**
         * Show password input fields
         * @private
         */
        _showPassword: function () {
            this._showAll();
            $(this.options.titleSelector).html(this.options.titleChangePassword);

            $(this.options.emailContainerSelector).hide();

            $(this.options.emailSelector).removeAttr('data-validate').prop('disabled', true);
        },

        /**
         * Update password validation rules with email input field value
         * @private
         */
        _updatePasswordFieldWithEmailValue: function () {
            $(this.options.newPasswordSelector).attr(
                'data-validate',
                '{required:true, ' +
                '\'validate-customer-password\':true, ' +
                '\'password-not-equal-to-user-name\':\'' + $(this.options.emailSelector).val() + '\'}'
            ).prop('disabled', false);
        }
    });

    return $.mage.changeEmailPassword;
});
