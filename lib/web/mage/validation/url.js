/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([], function () {
    'use strict';

    return {

        /**
         * Redirects to the url if it is considered safe
         *
         * @param {String} path - url to be redirected to
         */
        redirect: function (path) {
            path = this.sanitize(path);

            if (this.validate(path)) {
                window.location.href = path;
            }
        },

        /**
         * Validates url
         *
         * @param {Object} path - url to be validated
         * @returns {Boolean}
         */
        validate: function (path) {
            var hostname = window.location.hostname;

            if (path.indexOf(hostname) === -1 ||
                path.indexOf('javascript:') !== -1 ||
                path.indexOf('vbscript:') !== -1) {
                return false;
            }

            return true;
        },

        /**
         * Sanitize url, replacing disallowed chars
         *
         * @param {Sring} path - url to be normalized
         * @returns {String}
         */
        sanitize: function (path) {
            return path.replace('[^-A-Za-z0-9+&@#/%?=~_|!:,.;\(\)]', '');
        }
    };
});
