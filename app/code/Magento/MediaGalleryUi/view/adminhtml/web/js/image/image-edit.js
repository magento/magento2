/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'uiComponent',
    'uiLayout',
    'Magento_Ui/js/lib/key-codes',
    'Magento_MediaGalleryUi/js/action/getDetails',
    'mage/validation'
], function ($, _, Component, layout, keyCodes, getDetails) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_MediaGalleryUi/image/image-edit',
            modalSelector: '.media-gallery-edit-image-details-modal',
            imageEditDetailsUrl: '/media_gallery/image/details',
            saveDetailsUrl: '/media_gallery/image/saveDetails',
            images: [],
            image: null,
            keywordOptions: [],
            selectedKeywords: [],
            newKeyword: '',
            newKeywordSelector: '#keyword',
            modules: {
                mediaGridMessages: '${ $.mediaGridMessages }',
                keywordsSelect: '${ $.name }_keywords'
            },
            viewConfig: [
                {
                    component: 'Magento_Ui/js/form/element/ui-select',
                    name: '${ $.name }_keywords',
                    template: 'ui/grid/filters/elements/ui-select',
                    disableLabel: true
                }
            ],
            exports: {
                keywordOptions: '${ $.name }_keywords:options'
            },
            links: {
                selectedKeywords: '${ $.name }_keywords:value'
            }
        },

        /**
         * Initialize the component
         *
         * @returns {Object}
         */
        initialize: function () {
            this._super().initView();

            return this;
        },

        /**
         * Add a new keyword to select
         */
        addKeyword: function () {
            var options = this.keywordOptions(),
                selected = this.selectedKeywords(),
                newKeywordField = $(this.newKeywordSelector);

            newKeywordField.validation();

            if (!newKeywordField.validation('isValid') || this.newKeyword() === '') {
                return;
            }

            options.push(this.getOptionForKeyword(this.newKeyword()));
            selected.push(this.newKeyword());
            this.newKeyword('');

            this.keywordOptions(options);
            this.selectedKeywords(selected);
        },

        /**
         * Create an option object based on keyword string
         *
         * @param {String} keyword
         * @returns {Object}
         */
        getOptionForKeyword: function (keyword) {
            return {
                'is_active': 1,
                level: 1,
                value: keyword,
                label: keyword
            };
        },

        /**
         * Convert array of keywords to options format
         *
         * @param {Array} tags
         */
        setKeywordOptions: function (tags) {
            var options = [];

            tags.forEach(function (tag) {
                options.push(this.getOptionForKeyword(tag));
            }.bind(this));

            this.keywordOptions(options);
            this.selectedKeywords(tags);
        },

        /**
         * Initialize child components
         *
         * @returns {Object}
         */
        initView: function () {
            layout(this.viewConfig);

            return this;
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
                    'keywordOptions',
                    'selectedKeywords',
                    'newKeyword'
                ]);

            return this;
        },

        /**
         * Get image details by ID
         *
         * @param {String} imageId
         */
        showEditDetailsPanel: function (imageId) {
            if (_.isUndefined(this.images[imageId])) {
                getDetails(this.imageEditDetailsUrl, [imageId]).then(function (imageDetails) {
                    this.images[imageId] = imageDetails[imageId];
                    this.image(this.images[imageId]);
                    this.openEditImageDetailsModal();
                }.bind(this)).fail(function (error) {
                    this.addMediaGridMessage('error', error);
                }.bind(this));

                return;
            }

            if (this.image() && this.image().id === imageId) {
                this.openEditImageDetailsModal();

                return;
            }

            this.image(this.images[imageId]);
            this.openEditImageDetailsModal();
        },

        /**
         * Open edit image details popup
         */
        openEditImageDetailsModal: function () {
            var modalElement = $(this.modalSelector);

            if (!modalElement.length || _.isUndefined(modalElement.modal)) {
                return;
            }

            this.setKeywordOptions(this.image().tags);
            this.newKeyword('');

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
         * Handle Enter key event to save image details
         *
         * @param {Object} data
         * @param {jQuery.Event} event
         * @returns {Boolean}
         */
        handleEnterKey: function (data, event) {
            var modalElement = $(this.modalSelector),
                key = keyCodes[event.keyCode];

            if (key === 'enterKey') {
                event.preventDefault();
                modalElement.find('.page-action-buttons button.save').trigger('click');
            }

            return true;
        },

        /**
         * Remove cached image details in edit form
         *
         * @param {String} id
         */
        removeCached: function (id) {
            delete this.images[id];
        }
    });
});
