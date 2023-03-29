/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['jquery', 'mage/url'], function ($, urlBuilder) {
    'use strict';

    return {
        /**
         * Perform asynchronous GET request to server.
         * @param {String} url
         * @param {Boolean} global
         * @param {String} contentType
         * @param {Object} headers
         * @returns {Deferred}
         */
        get: function (url, global, contentType, headers) {
            headers = headers || {};
            global = global === undefined ? true : global;
            contentType = contentType || 'application/json';

            return $.ajax({
                url: urlBuilder.build(url),
                type: 'GET',
                global: global,
                contentType: contentType,
                headers: headers
            });
        },

        /**
         * Perform asynchronous POST request to server.
         * @param {String} url
         * @param {String} data
         * @param {Boolean} global
         * @param {String} contentType
         * @param {Object} headers
         * @param {Boolean} async
         * @returns {Deferred}
         */
        post: function (url, data, global, contentType, headers, async) {
            headers = headers || {};
            global = global === undefined ? true : global;
            contentType = contentType || 'application/json';
            async = async === undefined ? true : async;

            return $.ajax({
                url: urlBuilder.build(url),
                type: 'POST',
                data: data,
                global: global,
                contentType: contentType,
                headers: headers,
                async: async
            });
        },

        /**
         * Perform asynchronous PUT request to server.
         * @param {String} url
         * @param {String} data
         * @param {Boolean} global
         * @param {String} contentType
         * @param {Object} headers
         * @returns {Deferred}
         */
        put: function (url, data, global, contentType, headers) {
            var ajaxSettings = {};

            headers = headers || {};
            global = global === undefined ? true : global;
            contentType = contentType || 'application/json';
            ajaxSettings.url = urlBuilder.build(url);
            ajaxSettings.type = 'PUT';
            ajaxSettings.data = data;
            ajaxSettings.global = global;
            ajaxSettings.contentType = contentType;
            ajaxSettings.headers = headers;

            return $.ajax(ajaxSettings);
        },

        /**
         * Perform asynchronous DELETE request to server.
         * @param {String} url
         * @param {Boolean} global
         * @param {String} contentType
         * @param {Object} headers
         * @returns {Deferred}
         */
        delete: function (url, global, contentType, headers) {
            headers = headers || {};
            global = global === undefined ? true : global;
            contentType = contentType || 'application/json';

            return $.ajax({
                url: urlBuilder.build(url),
                type: 'DELETE',
                global: global,
                contentType: contentType,
                headers: headers
            });
        }
    };
});
