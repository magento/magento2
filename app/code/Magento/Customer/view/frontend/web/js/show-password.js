/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function($) {
    'use strict';

    $.widget('mage.showPassword', {
        options: {
            passwordSelector: '',
            showPasswordSelector: '[data-role=show-password]',
            passwordInputType: 'password',
            textInputType: 'text'
        },

        /**
         * Widget initialization
         * @private
         */
        _create: function () {
            this._bind();
        },

        /**
         * Event binding, will monitor click event on show password.
         * @private
         */
        _bind: function () {
            this._on(this.options.showPasswordSelector, {
                'click': this._showPassword
            });
        },

        /**
         * Show/Hide password
         * @private
         */
        _showPassword: function () {
            if ($(this.options.passwordSelector).attr("type") == this.options.passwordInputType) {
                $(this.options.passwordSelector).attr("type", this.options.textInputType);
            } else {
                $(this.options.passwordSelector).attr("type", this.options.passwordInputType);
            }
        }
    });
});