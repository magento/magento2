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
                displayScore,
                isValid;

            // Display score is based on combination of whether password is empty, valid, and zxcvbn strength
            if (isEmpty) {
                displayScore = 0;
            } else {
                isValid  = $.validator.validateSingleElement(this.options.cache.input);
                displayScore = isValid ? zxcvbnScore : 1;
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
                className = 'password-';

            switch (displayScore) {
                case 0:
                    strengthLabel = $t('No Password');
                    className += 'none';
                    break;

                case 1:
                    strengthLabel = $t('Weak');
                    className += 'weak';
                    break;

                case 2:
                    strengthLabel = $t('Medium');
                    className += 'medium';
                    break;

                case 3:
                    strengthLabel = $t('Strong');
                    className += 'strong';
                    break;

                case 4:
                    strengthLabel = $t('Very Strong');
                    className += 'very-strong';
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
