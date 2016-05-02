/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * jshint browser:true
 */
/*eslint no-unused-vars: 0*/
define([
    'jquery',
    'Magento_Customer/js/zxcvbn',
    'mage/translate',
    'mage/validation'
], function ($, zxcvbn, $t, validation) {
    'use strict';

    $.widget('mage.passwordStrengthIndicator', {
        options: {
            defaultClassName: 'password-strength-meter-',
            passwordStrengthMeterId: 'password-strength-meter-container',
            passwordStrengthMeterLabelId: 'password-strength-meter-label'
        },

        /**
         * Widget initialization
         * @private
         */
        _create: function () {
            this._bind();
        },

        /**
         * Event binding, will monitor scroll and resize events (resize events left for backward compat)
         * @private
         */
        _bind: function () {
            this._on({
                'change input[type="password"]': this._calculateStrength,
                'keyup input[type="password"]': this._calculateStrength,
                'paste input[type="password"]': this._calculateStrength
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
            $.validator.validateSingleElement(this.element.find('input[type="password"]'));
        },

        /**
         * Display strength
         * @param {String} className
         * @param {Number} score
         * @param {Boolean} isEmpty
         * @private
         */
        _displayStrength: function (className, score, isEmpty) {
            var strengthContainer = this.element.find('#' + this.options.passwordStrengthMeterId),
                strengthLabel = '';

            strengthContainer.removeClass();
            strengthContainer.addClass(className);

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

            this.element.find('#' + this.options.passwordStrengthMeterLabelId).text(strengthLabel);
        },

        /**
         * Get password value
         * @returns {*}
         * @private
         */
        _getPassword: function () {
            return this.element.find('input[type="password"]').val();
        },

        /**
         * Get class name for score
         * @param {int} score
         * @param {Boolean} isEmpty
         * @returns {String}
         * @private
         */
        _getClassName: function (score, isEmpty) {
            var suffix = isEmpty ? 'no-pwd' : score;
            return this.options.defaultClassName + suffix;
        }
    });

    return $.mage.passwordStrengthIndicator;
});
