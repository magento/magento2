/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/translate'
], function ($, $t) {
    'use strict';

    return function (saveImageDetailsUrl, data) {
        var deferred = $.Deferred(),
            message;

        $.ajax({
            type: 'POST',
            url: saveImageDetailsUrl,
            dataType: 'json',
            showLoader: true,
            data: data,

            /**
             * Resolve with image details if success, reject with response message otherwise
             *
             * @param {Object} response
             */
            success: function (response) {
                if (response.success) {
                    deferred.resolve(response.message);

                    return;
                }

                deferred.reject(response.message);
            },

            /**
             * Extract the message and reject
             *
             * @param {Object} response
             */
            error: function (response) {
                if (typeof response.responseJSON === 'undefined' ||
                    typeof response.responseJSON.message === 'undefined'
                ) {
                    message = $t('Could not save image details.');
                } else {
                    message = response.responseJSON.message;
                }
                deferred.reject(message);
            }
        });

        return deferred.promise();
    };
});
