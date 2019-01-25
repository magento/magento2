/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return function () {
        var deferred = $.Deferred(),
            dependency = 'acceptjs';

        if (window.checkoutConfig.payment['authorizenet_acceptjs'].environment === 'sandbox') {
            dependency = 'acceptjssandbox';
        }

        require([dependency], function (accept) {
            deferred.resolve(accept);
        });

        return deferred.promise();
    };
});
