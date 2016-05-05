/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * jshint browser:true
 */
define([
    'jquery',
    'Magento_Customer/js/zxcvbn',
    'mage/translate'
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
                score = zxcvbn(password).score,
                className = this._getClassName(score, isEmpty);

            this._displayStrength(className, score, isEmpty);
            //update error messages
            $.validator.validateSingleElement(this.options.cache.input);
        },

        /**
         * Display strength
         * @param {String} className
         * @param {Number} score
         * @param {Boolean} isEmpty
         * @private
         */
        _displayStrength: function (className, score, isEmpty) {
            var strengthLabel = '';

            if (isEmpty) {
                strengthLabel = $t('No Password');
            } else {
                switch (score) {
                    case 0:
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
         * @param {int} score
         * @param {Boolean} isEmpty
         * @returns {String}
         * @private
         */
        _getClassName: function (score, isEmpty) {
            return this.options.defaultClassName + (isEmpty ? 'no-pwd' : score);
        }
    });

    return $.mage.passwordStrengthIndicator;
});
