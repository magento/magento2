/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([], function () {
    'use strict';

    var baseUrl = '';

    return {
        /**
         * @param {String} url
         */
        setBaseUrl: function (url) {
            baseUrl = url;
        },

        /**
         * @param {String} path
         * @return {*}
         */
        build: function (path) {
            if (path.indexOf(baseUrl) !== -1) {
                return path;
            }

            return baseUrl + path;
        }
    };
});
