/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
