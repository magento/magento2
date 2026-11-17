/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'js-storage/js.storage'
], function ($, storage) {
    'use strict';

    var cookiesConfig;

    if (window.cookieStorage) {
        cookiesConfig = window.cookiesConfig || {};

        $.extend(window.cookieStorage, {
            _secure: !!cookiesConfig.secure,
            _samesite: cookiesConfig.samesite ? cookiesConfig.samesite : 'lax',

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
                    secure: this._secure,
                    samesite: this._samesite
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

                if (typeof c.samesite !== 'undefined') {
                    this._samesite = c.samesite;
                }

                return this;
            }
        });
    }

    $.alwaysUseJsonInStorage = $.alwaysUseJsonInStorage || storage.alwaysUseJsonInStorage;
    $.cookieStorage = $.cookieStorage || storage.cookieStorage;
    $.initNamespaceStorage = $.initNamespaceStorage || function (namespace) {
        var namespaceStorage = storage.initNamespaceStorage(namespace);

        ['set', 'remove'].forEach(function (method) {
            var originalMethod = namespaceStorage.localStorage[method];

            namespaceStorage.localStorage[method] = function () {
                var namespaceData = storage.localStorage.get(namespace);

                if (namespaceData === null || typeof namespaceData === 'undefined') {
                    storage.localStorage.set(namespace, {});
                }

                return originalMethod.apply(this, arguments);
            };
        });

        return namespaceStorage;
    };
    $.localStorage = $.localStorage || storage.localStorage;
    $.namespaceStorages = $.namespaceStorages || storage.namespaceStorages;
    $.removeAllStorages = $.removeAllStorages || storage.removeAllStorages;
    $.sessionStorage = $.sessionStorage || storage.sessionStorage;
});
