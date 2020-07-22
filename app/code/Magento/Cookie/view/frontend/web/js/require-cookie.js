/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'jquery-ui-modules/widget',
    'mage/mage',
    'mage/translate'
], function ($, alert) {
    'use strict';

    $.widget('mage.requireCookie', {
        options: {
            event: 'click',
            noCookieUrl: 'enable-cookies',
            triggers: ['.action.login', '.action.submit'],
            isRedirectCmsPage: true
        },

        /**
         * Constructor
         * @private
         */
        _create: function () {
            this._bind();
        },

        /**
         * This method binds elements found in this widget.
         * @private
         */
        _bind: function () {
            var events = {};

            $.each(this.options.triggers, function (index, value) {
                events['click ' + value] = '_checkCookie';
            });
            this._on(events);
        },

        /**
         * This method set the url for the redirect.
         * @param {jQuery.Event} event
         * @private
         */
        _checkCookie: function (event) {
            if (navigator.cookieEnabled) {
                return;
            }

            event.preventDefault();

            if (this.options.isRedirectCmsPage) {
                window.location = this.options.noCookieUrl;
            } else {
                alert({
                    content: $.mage.__('Cookies are disabled in your browser.')
                });
            }
        }
    });

    return $.mage.requireCookie;
});
