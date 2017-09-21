/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['jquery'], function ($) {
    'use strict';

    return {
        method: 'rest',
        storeCode: window.checkoutConfig.storeCode,
        version: 'V1',
        serviceUrl: ':method/:storeCode/:version',

        /**
         * @param {String} url
         * @param {Object} params
         * @return {*}
         */
        createUrl: function (url, params) {
            var completeUrl = this.serviceUrl + url;

            return this.bindParams(completeUrl, params);
        },

        /**
         * @param {String} url
         * @param {Object} params
         * @return {*}
         */
        bindParams: function (url, params) {
            var urlParts;

            params.method = this.method;
            params.storeCode = this.storeCode;
            params.version = this.version;

            urlParts = url.split('/');
            urlParts = urlParts.filter(Boolean);

            $.each(urlParts, function (key, part) {
                part = part.replace(':', '');

                if (params[part] != undefined) { //eslint-disable-line eqeqeq
                    urlParts[key] = params[part];
                }
            });

            return urlParts.join('/');
        }
    };
});
