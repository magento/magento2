/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

(function (root, factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'mage/apply/main'
        ], factory);
    } else {
        factory(root.jQuery);
    }
}(this, function ($, mage) {
    'use strict';

    /**
     * Main namespace for Magento extensions
     * @type {Object}
     */
    $.mage = $.mage || {};

    /**
     * Plugin mage, initialize components on elements
     * @param {String} name - Components' path.
     * @param {Object} config - Components' config.
     * @returns {JQuery} Chainable.
     */
    $.fn.mage = function (name, config) {
        config = config || {};

        this.each(function (index, el) {
            mage.applyFor(el, config, name);
        });

        return this;
    };

    $.extend($.mage, {
        /**
         * Handle all components declared via data attribute
         * @return {Object} $.mage
         */
        init: function () {
            mage.apply();

            return this;
        },

        /**
         * Method handling redirects and page refresh
         * @param {String} url - redirect URL
         * @param {(undefined|String)} type - 'assign', 'reload', 'replace'
         * @param {(undefined|Number)} timeout - timeout in milliseconds before processing the redirect or reload
         * @param {(undefined|Boolean)} forced - true|false used for 'reload' only
         */
        redirect: function (url, type, timeout, forced) {
            var _redirect;

            forced  = !!forced;
            timeout = timeout || 0;
            type    = type || 'assign';

            /**
             * @private
             */
            _redirect = function () {
                window.location[type](type === 'reload' ? forced : url);
            };

            timeout ? setTimeout(_redirect, timeout) : _redirect();
        },

        /**
         * Checks if provided string is a valid selector.
         * @param {String} selector - Selector to check.
         * @returns {Boolean}
         */
        isValidSelector: function (selector) {
            try {
                document.querySelector(selector);

                return true;
            } catch (e) {
                return false;
            }
        }
    });

    /**
     * Init components inside of dynamically updated elements
     */
    $('body').on('contentUpdated', function () {
        if (mage) {
            mage.apply();
        }
    });

    return $.mage;
}));
