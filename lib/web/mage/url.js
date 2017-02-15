/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable strict */
define([], function () {
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
