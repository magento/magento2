/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/translate'
], function ($, $t) {
    'use strict';

    return function (imageDetailsUrl, imageIds) {
        var deferred = $.Deferred(),
            message;

        $.ajax({
            type: 'GET',
            url: imageDetailsUrl,
            dataType: 'json',
            showLoader: true,
            data: {
                'ids': imageIds
            },
            context: this,

            /**
             * Resolve with image details if success, reject with response message othervise
             *
             * @param {Object} response
             */
            success: function (response) {
                if (response.success) {
                    deferred.resolve(response.imageDetails);

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
                    message = $t('Could not retrieve image details.');
                } else {
                    message = response.responseJSON.message;
                }
                deferred.reject(message);
            }
        });

        return deferred.promise();
    };
});
