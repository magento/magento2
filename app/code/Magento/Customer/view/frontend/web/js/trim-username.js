/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('mage.trimUsername', {
        options: {
            cache: {},
            formSelector: 'form',
            emailSelector: 'input[type="email"]'
        },

        /**
         * Widget initialization
         * @private
         */
        _create: function () {
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
            if (this.options.cache.email.length) {
                this._on(this.options.cache.email, {
                    'change': this._trimUsername,
                    'keyup': this._trimUsername,
                    'paste': this._trimUsername
                });
            }
        },

        /**
         * Trim username
         * @private
         */
        _trimUsername: function () {
            var username = this._getUsername().trim();

            this.options.cache.email.val(username);
        },

        /**
         * Get username value
         * @returns {*}
         * @private
         */
        _getUsername: function () {
            return this.options.cache.email.val();
        }
    });

    return $.mage.trimUsername;
});
