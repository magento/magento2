/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * jshint browser:true
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
            defaultClassName: 'password-strength-meter-',
            passwordSelector: '[type=password]',
            passwordStrengthMeterSelector: '[data-role=password-strength-meter]',
            passwordStrengthMeterLabelSelector: '[data-role=password-strength-meter-label]'
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
         * Event binding, will monitor scroll and resize events (resize events left for backward compat)
         * @private
         */
        _bind: function () {
            this._on(this.options.cache.input, {
                'change': this._calculateStrength,
                'keyup': this._calculateStrength,
                'paste': this._calculateStrength
            });
        },

        /**
         * Calculate password strength
         * @private
         */
        _calculateStrength: function () {
            var password = this._getPassword(),
                isEmpty = password.length === 0,
                zxcvbnScore = zxcvbn(password).score,
                isValid = $.validator.validateSingleElement(this.options.cache.input),
                displayScore = zxcvbnScore || 1;

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
                className = this._getClassName(displayScore);

            switch (displayScore) {
                case 0:
                    strengthLabel = $t('No Password');
                    break;

                case 1:
                    strengthLabel = $t('Weak');
                    break;

                case 2:
                    strengthLabel = $t('Medium');
                    break;

                case 3:
                    strengthLabel = $t('Strong');
                    break;

                case 4:
                    strengthLabel = $t('Very Strong');
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
        },

        /**
         * Get class name for score
         * @param {int} displayScore
         * @returns {String}
         * @private
         */
        _getClassName: function (displayScore) {
            return this.options.defaultClassName + displayScore;
        }
    });

    return $.mage.passwordStrengthIndicator;
});
