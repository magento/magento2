/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * jshint browser:true
 */
define([
    'jquery',
    'Magento_Customer/js/zxcvbn'
], function ($, zxcvbn) {
    'use strict';

    $.widget('mage.passwordStrengthIndicator', {
        options: {
            defaultClassName: 'password-strength-meter-',
            passwordSrengthMeterId: 'password-strength-meter'
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
                score = zxcvbn(password).score,
                className = this._getClassName(score);

            this._displayStrength(className);
        },

        /**
         * Display strength
         * @param {String} className
         * @private
         */
        _displayStrength: function (className) {
            this.element.find('#' + this.options.passwordSrengthMeterId).removeClass();
            this.element.find('#' + this.options.passwordSrengthMeterId).addClass(className);
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
         * @returns {String}
         * @private
         */
        _getClassName: function (score) {
            return this.options.defaultClassName + score;
        }
    });

    return $.mage.passwordStrengthIndicator;
});
