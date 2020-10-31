/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery/jquery.cookie',
    'jquery/jquery.storageapi.min'
], function ($) {
    'use strict';

    /**
     *
     * @param {Object} storage
     * @private
     */
    function _extend(storage) {
        $.extend(storage, {
            _secure: window.cookiesConfig ? window.cookiesConfig.secure : false,

            /**
             * Set value under name
             * @param {String} name
             * @param {String} value
             * @param {Object} [options]
             */
            setItem: function (name, value, options) {
                var _default = {
                    expires: this._expires,
                    path: this._path,
                    domain: this._domain,
                    secure: this._secure
                };

                $.cookie(this._prefix + name, value, $.extend(_default, options || {}));
            },

            /**
             * Set default options
             * @param {Object} c
             * @returns {storage}
             */
            setConf: function (c) {
                if (c.path) {
                    this._path = c.path;
                }

                if (c.domain) {
                    this._domain = c.domain;
                }

                if (c.expires) {
                    this._expires = c.expires;
                }

                if (typeof c.secure !== 'undefined') {
                    this._secure = c.secure;
                }

                return this;
            }
        });
    }

    if (window.cookieStorage) {
        _extend(window.cookieStorage);
    }
});
