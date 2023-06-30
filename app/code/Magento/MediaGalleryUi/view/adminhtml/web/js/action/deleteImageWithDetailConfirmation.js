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
            var confirmationContent = $t('%1Are you sure you want to delete "%2" image(s)?')
                .replace('%2', Object.keys(recordsIds).length),
                deferred = $.Deferred();

            getDetails(imageDetailsUrl, recordsIds).then(function (images) {
                        confirmationContent = confirmationContent.replace(
                            '%1',
                            this.getRecordRelatedContentMessage(images) + ' '
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
                usedIn = {};

            $.each(images, function (key, image) {
                $.each(image.details, function (sectionIndex, section) {
                    if (section.title === 'Used In' && _.isObject(section) && !_.isEmpty(section.value)) {
                        $.each(section.value, function (entityTypeIndex, entityTypeData) {
                            usedIn[entityTypeData.name] = entityTypeData.name in usedIn ?
                                usedIn[entityTypeData.name] + entityTypeData.number :
                                entityTypeData.number;
                        });
                    }
                });
            });

            if (_.isEmpty(usedIn)) {
                return '';
            }

            return usedInMessage + this.usedInObjectToString(usedIn);
        },

        /**
         * Fromats usedIn object to string
         *
         * @param {Object} usedIn
         * @return {String}
         */
        usedInObjectToString: function (usedIn) {
            var entities = [];

            $.each(usedIn, function (entityName, number) {
                entities.push(entityName + '(' + number + ')');
            });

            return entities.join(', ') + '.';
        }
    };
});
