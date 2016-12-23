/**
 * Copyright © 2016 Magento. All rights reserved.
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
         * @returns {Deferred}
         */
        get: function (url, global, contentType) {
            global = global === undefined ? true : global;
            contentType = contentType || 'application/json';

            return $.ajax({
                url: urlBuilder.build(url),
                type: 'GET',
                global: global,
                contentType: contentType
            });
        },

        /**
         * Perform asynchronous POST request to server.
         * @param {String} url
         * @param {String} data
         * @param {Boolean} global
         * @param {String} contentType
         * @returns {Deferred}
         */
        post: function (url, data, global, contentType) {
            global = global === undefined ? true : global;
            contentType = contentType || 'application/json';

            return $.ajax({
                url: urlBuilder.build(url),
                type: 'POST',
                data: data,
                global: global,
                contentType: contentType
            });
        },

        /**
         * Perform asynchronous PUT request to server.
         * @param {String} url
         * @param {String} data
         * @param {Boolean} global
         * @param {String} contentType
         * @returns {Deferred}
         */
        put: function (url, data, global, contentType) {
            global = global === undefined ? true : global;
            contentType = contentType || 'application/json';

            return $.ajax({
                url: urlBuilder.build(url),
                type: 'PUT',
                data: data,
                global: global,
                contentType: contentType
            });
        },

        /**
         * Perform asynchronous DELETE request to server.
         * @param {String} url
         * @param {Boolean} global
         * @param {String} contentType
         * @returns {Deferred}
         */
        delete: function (url, global, contentType) {
            global = global === undefined ? true : global;
            contentType = contentType || 'application/json';

            return $.ajax({
                url: urlBuilder.build(url),
                type: 'DELETE',
                global: global,
                contentType: contentType
            });
        }
    };
});
