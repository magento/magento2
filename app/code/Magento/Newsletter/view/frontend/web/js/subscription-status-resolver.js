/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/storage',
    'mage/url'
], function ($, storage, urlBuilder) {
    'use strict';

    return function (email, deferred) {
        return $.getJSON(
            urlBuilder.build('newsletter/ajax/status'),
            {
                email: email
            }
        ).done(function (response) {
            deferred.resolve(response.subscribed);
        }).fail(function () {
            deferred.reject();
        });
    };
});
