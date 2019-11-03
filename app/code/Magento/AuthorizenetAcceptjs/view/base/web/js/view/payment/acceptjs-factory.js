/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return function (environment) {
        var deferred = $.Deferred(),
            dependency = 'acceptjs';

        if (environment === 'sandbox') {
            dependency = 'acceptjssandbox';
        }

        require([dependency], function (accept) {
                var $body = $('body');

                /*
                 * Acceptjs doesn't safely load dependent files which leads to a race condition when trying to use
                 * the sdk right away.
                 * @see https://community.developer.authorize.net/t5/Integration-and-Testing/
                 * Dynamically-loading-Accept-js-E-WC-03-Accept-js-is-not-loaded/td-p/63283
                 */
                $body.on('handshake.acceptjs', function () {
                    deferred.resolve(accept);
                    $body.off('handshake.acceptjs');
                });
            },
            deferred.reject
        );

        return deferred.promise();
    };
});
