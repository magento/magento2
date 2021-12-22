/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'mage/url',
    'Magento_MediaGalleryUi/js/grid/messages',
    'Magento_Ui/js/modal/confirm',
    'mage/translate'
], function ($, _, urlBuilder, messages, confirmation, $t) {
    'use strict';

    return function (ids, deleteUrl, confirmationContent) {
        var deferred = $.Deferred(),
               title = $t('Delete assets'),
               cancelText = $t('Cancel'),
               deleteImageText = $t('Delete');

        /**
         * Send deletion request with redords ids
         *
         * @param {Array} recordIds
         * @param {String} serviceUrl
         */
        function sendRequest(recordIds, serviceUrl) {

            $.ajax({
                type: 'POST',
                url: serviceUrl,
                dataType: 'json',
                showLoader: true,
                data: {
                    'form_key': window.FORM_KEY,
                    'ids': recordIds
                },
                context: this,

                /**
                 * Success handler for deleting image
                 *
                 * @param {Object} response
                 */
                success: function (response) {
                    var message = !_.isUndefined(response.message) ? response.message : null;

                    if (!response.success) {
                        message = message || $t('There was an error on attempt to delete the images.');
                        $(window).trigger('fileDeleted.enhancedMediaGallery', {
                            reload: false,
                            message: message,
                            code: 'error'
                        });

                        deferred.reject(message);
                    }

                    message = message || $t('You have successfully removed the images.');
                    $(window).trigger('fileDeleted.enhancedMediaGallery', {
                        reload: true,
                        message: message,
                        code: 'success'
                    });
                    deferred.resolve(message);
                },

                /**
                 * Error handler for deleting image
                 *
                 * @param {Object} response
                 */
                error: function (response) {
                    var message;

                    if (typeof response.responseJSON === 'undefined' ||
                        typeof response.responseJSON.message === 'undefined'
                    ) {
                        message = $t('There was an error on attempt to delete the image.');
                    } else {
                        message = response.responseJSON.message;
                    }

                    $(window).trigger('fileDeleted.enhancedMediaGallery', {
                        reload: false,
                        message: message,
                        code: 'error'
                    });
                    deferred.reject(message);
                }
            });
        }

        confirmation({
            title: title,
            modalClass: 'media-gallery-delete-image-action',
            content: confirmationContent,
            buttons: [
                {
                    text: cancelText,
                    class: 'action-secondary action-dismiss',

                    /**
                     * Close modal
                     */
                    click: function () {
                        this.closeModal();
                        deferred.resolve({
                            status: 'canceled'
                        });
                    }
                },
                {
                    text: deleteImageText,
                    class: 'action-primary action-accept',

                    /**
                     * Delete Image and close modal
                     */
                    click: function () {
                        sendRequest(ids, deleteUrl);
                        this.closeModal();
                    }
                }
            ]
        });

        return deferred.promise();
    };
});
