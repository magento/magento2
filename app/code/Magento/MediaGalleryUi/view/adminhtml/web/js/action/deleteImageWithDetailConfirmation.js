/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'Magento_MediaGalleryUi/js/action/getDetails',
    'Magento_MediaGalleryUi/js/action/deleteImages',
    'mage/translate'
], function ($, _, getDetails, deleteImages, $t) {
    'use strict';

    return {

        /**
         * Get information about image use
         *
         * @param {Array} recordsIds
         * @param {String} imageDetailsUrl
         * @param {String} deleteImageUrl
         */
        deleteImageAction: function (recordsIds, imageDetailsUrl, deleteImageUrl) {
            var imagesCount = Object.keys(recordsIds).length,
                confirmationContent = $t('%1 Are you sure you want to delete "%2" image%3?')
                .replace('%2', Object.keys(recordsIds).length).replace('%3', imagesCount > 1 ? 's' : ''),
                deferred = $.Deferred();

            getDetails(imageDetailsUrl, recordsIds)
                .then(function (imageDetails) {
                        confirmationContent = confirmationContent.replace(
                            '%1',
                            this.getRecordRelatedContentMessage(imageDetails)
                        );
                    }.bind(this)).fail(function () {
                confirmationContent = confirmationContent.replace('%1', '');
            }).always(function () {
                deleteImages(recordsIds, deleteImageUrl, confirmationContent).then(function (status) {
                    deferred.resolve(status);
                }).fail(function (error) {
                    deferred.reject(error);
                });
            });

            return deferred.promise();
        },

        /**
         * Get information about image use
         *
         * @param {Object|String} images
         * @return {String}
         */
        getRecordRelatedContentMessage: function (images) {
            var usedInMessage = $t('The selected assets are used in the content of the following entities: '),
                usedIn = [];

            $.each(images, function (key, image) {
                $.each(image.details, function (sectionIndex, section) {
                    if (section.title === 'Used In' && _.isObject(section) && !_.isEmpty(section.value)) {
                        $.each(section.value, function (entityTypeIndex, entityTypeData) {
                            usedIn.push(entityTypeData.name + '(' + entityTypeData.number + ')');
                        });
                    }
                });
            });

            if (_.isEmpty(usedIn)) {
                return '';
            }

            return usedInMessage + usedIn.join(', ') + '.';
        }
    };
});
