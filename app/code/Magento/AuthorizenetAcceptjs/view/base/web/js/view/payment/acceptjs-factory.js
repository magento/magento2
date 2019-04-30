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

        require([dependency], function () {
                var $body = $('body');

                /*
                 * Acceptjs doesn't safely load dependent files which leads to a race condition when trying to use
                 * the sdk right away.
                 * @see https://community.developer.authorize.net/t5/Integration-and-Testing/
                 * Dynamically-loading-Accept-js-E-WC-03-Accept-js-is-not-loaded/td-p/63283
                 */
                $body.on('handshake.acceptjs', function () {
                    /*
                     * Accept.js doesn't return the library when loading
                     * and requirejs "shim" can't be used because it only works with the "paths" config option
                     * and we can't use "paths" because require will try to load ".min.js" in production
                     * and that doesn't work because it doesn't exist
                     * and we can't add a query string to force a URL because accept.js will reject it
                     * and we can't include it locally because they check in the script before loading more scripts
                     * So, we use the global version as "shim" would
                     */
                    deferred.resolve(window.Accept);
                    $body.off('handshake.acceptjs');
                });
            },
            deferred.reject
        );

        return deferred.promise();
    };
});
