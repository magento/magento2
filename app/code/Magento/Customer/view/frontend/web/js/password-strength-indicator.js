/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Customer/js/zxcvbn',
    'mage/translate',
    'mage/validation'
], function ($, zxcvbn, $t) {
    'use strict';

    $.widget('mage.passwordStrengthIndicator', {
        options: {
            cache: {},
            passwordSelector: '[type=password]',
            passwordStrengthMeterSelector: '[data-role=password-strength-meter]',
            passwordStrengthMeterLabelSelector: '[data-role=password-strength-meter-label]',
            formSelector: 'form',
            emailSelector: 'input[type="email"]'
        },

        /**
         * Widget initialization
         * @private
         */
        _create: function () {
            this.options.cache.input = $(this.options.passwordSelector, this.element);
            this.options.cache.meter = $(this.options.passwordStrengthMeterSelector, this.element);
            this.options.cache.label = $(this.options.passwordStrengthMeterLabelSelector, this.element);

            // We need to look outside the module for backward compatibility, since someone can already use the module.
            // @todo Narrow this selector in 2.3 so it doesn't accidentally finds the the email field from the
            // newsletter email field or any other "email" field.
            this.options.cache.email = $(this.options.formSelector).find(this.options.emailSelector);
            this._bind();
        },

        /**
         * Event binding, will monitor change, keyup and paste events.
         * @private
         */
        _bind: function () {
            this._on(this.options.cache.input, {
                'change': this._calculateStrength,
                'keyup': this._calculateStrength,
                'paste': this._calculateStrength
            });

            if (this.options.cache.email.length) {
                this._on(this.options.cache.email, {
                    'change': this._calculateStrength,
                    'keyup': this._calculateStrength,
                    'paste': this._calculateStrength
                });
            }
        },

        /**
         * Calculate password strength
         * @private
         */
        _calculateStrength: function () {
            var password = this._getPassword(),
                isEmpty = password.length === 0,
                zxcvbnScore,
                displayScore,
                isValid;

            // Display score is based on combination of whether password is empty, valid, and zxcvbn strength
            if (isEmpty) {
                displayScore = 0;
            } else {
                this.options.cache.input.rules('add', {
                    'password-not-equal-to-user-name': this.options.cache.email.val()
                });

                // We should only perform this check in case there is an email field on screen
                if (this.options.cache.email.length &&
                    password.toLowerCase() === this.options.cache.email.val().toLowerCase()) {
                    displayScore = 1;
                } else {
                    isValid = $.validator.validateSingleElement(this.options.cache.input);
                    zxcvbnScore = zxcvbn(password).score;
                    displayScore = isValid ? zxcvbnScore : 1;
                }
            }

            // Update label
            this._displayStrength(displayScore);
        },

        /**
         * Display strength
         * @param {Number} displayScore
         * @private
         */
        _displayStrength: function (displayScore) {
            var strengthLabel = '',
                className;

            switch (displayScore) {
                case 0:
                    strengthLabel = $t('No Password');
                    className = 'password-none';
                    break;

                case 1:
                    strengthLabel = $t('Weak');
                    className = 'password-weak';
                    break;

                case 2:
                    strengthLabel = $t('Medium');
                    className = 'password-medium';
                    break;

                case 3:
                    strengthLabel = $t('Strong');
                    className = 'password-strong';
                    break;

                case 4:
                    strengthLabel = $t('Very Strong');
                    className = 'password-very-strong';
                    break;
            }

            this.options.cache.meter
                .removeClass()
                .addClass(className);
            this.options.cache.label.text(strengthLabel);
        },

        /**
         * Get password value
         * @returns {*}
         * @private
         */
        _getPassword: function () {
            return this.options.cache.input.val();
        }
    });

    return $.mage.passwordStrengthIndicator;
});
