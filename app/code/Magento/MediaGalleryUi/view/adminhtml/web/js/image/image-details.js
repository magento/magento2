/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'uiComponent',
    'Magento_MediaGalleryUi/js/action/getDetails'
], function ($, _, Component, getDetails) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_MediaGalleryUi/image/image-details',
            modalSelector: '',
            modalWindowSelector: '',
            imageDetailsUrl: '/media_gallery/image/details',
            images: [],
            tagListLimit: 7,
            showAllTags: false,
            image: null,
            modules: {
                mediaGridMessages: '${ $.mediaGridMessages }'
            }
        },

        /**
         * Init observable variables
         *
         * @return {Object}
         */
        initObservable: function () {
            this._super()
                .observe([
                    'image',
                    'showAllTags'
                ]);

            return this;
        },

        /**
         * Show image details by ID
         *
         * @param {String} imageId
         */
        showImageDetailsById: function (imageId) {
            if (_.isUndefined(this.images[imageId])) {
                getDetails(this.imageDetailsUrl, [imageId]).then(function (imageDetails) {
                    this.images[imageId] = imageDetails[imageId];
                    this.image(this.images[imageId]);
                    this.openImageDetailsModal();
                }.bind(this)).fail(function (error) {
                    this.addMediaGridMessage('error', error);
                }.bind(this));

                return;
            }

            if (this.image() && this.image().id === imageId) {
                this.openImageDetailsModal();

                return;
            }

            this.image(this.images[imageId]);
            this.openImageDetailsModal();
        },

        /**
         * Open image details popup
         */
        openImageDetailsModal: function () {
            var modalElement = $(this.modalSelector);

            if (!modalElement.length || _.isUndefined(modalElement.modal)) {
                return;
            }

            this.showAllTags(false);
            modalElement.modal('openModal');
        },

        /**
         * Close image details popup
         */
        closeImageDetailsModal: function () {
            var modalElement = $(this.modalSelector);

            if (!modalElement.length || _.isUndefined(modalElement.modal)) {
                return;
            }

            modalElement.modal('closeModal');
        },

        /**
         * Add media grid message
         *
         * @param {String} code
         * @param {String} message
         */
        addMediaGridMessage: function (code, message) {
            this.mediaGridMessages().add(code, message);
            this.mediaGridMessages().scheduleCleanup();
        },

        /**
         * Get tag text
         *
         * @param {String} tagText
         * @param {Number} tagIndex
         * @return {String}
         */
        getTagText: function (tagText, tagIndex) {
            return tagText + (this.image().tags.length - 1 === tagIndex ? '' : ',');
        },

        /**
         * Show all image tags
         */
        showMoreImageTags: function () {
            this.showAllTags(true);
        },

        /**
         * Is value an object
         *
         * @param {*} value
         * @returns {Boolean}
         */
        isArray: function (value) {
            return _.isArray(value);
        },

        /**
         * Is value not empty
         *
         * @param {*} value
         * @returns {Boolean}
         */
        notEmpty: function (value) {
            return value.length > 0;
        },

        /**
         * Get name and number text for used in link
         *
         * @param {Object} item
         * @returns {String}
         */
        getUsedInText: function (item) {
            return item.name +  '(' + item.number + ')';
        },

        /**
         * Get filter url
         *
         * @param {String} link
         */
        getFilterUrl: function (link) {
            return link + '?filters[asset_id]=[' + this.image().id + ']';
        },

        /**
         * Check if details modal is active
         * @return {Boolean}
         */
        isActive: function () {
            return $(this.modalWindowSelector).hasClass('_show');
        },

        /**
         * Remove image details
         *
         * @param {String} id
         */
        removeCached: function (id) {
            delete this.images[id];
        }
    });
});
