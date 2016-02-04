/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*eslint-disable no-undef*/

define(
    ['jquery'],
    function ($) {
        'use strict';

        /**
         * @param {String} requestUrl
         * @param {String} redirectUrl
         */
        function initSecuritySessionCheckTimer(requestUrl, redirectUrl) {
            setInterval(
                function () {
                    $.ajax({
                        type: 'GET',
                        url: requestUrl,

                        /**
                         * @param {Object} response
                         */
                        success: function (response) {
                            if (response instanceof Object) {
                                if (!response.isActive) {
                                    setLocation(redirectUrl);
                                }
                            }
                        }
                    });
                },
                10000
            );
        }

        return function (config) {
            initSecuritySessionCheckTimer(config.requestUrl, config.redirectUrl);
        };
    }
);
