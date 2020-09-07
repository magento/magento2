/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'uiComponent',
    'Magento_MediaGalleryUi/js/action/deleteImageWithDetailConfirmation',
    'Magento_MediaGalleryUi/js/grid/columns/image/insertImageAction',
    'mage/translate'
], function ($, _, Component, deleteImageWithDetailConfirmation, image, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_MediaGalleryUi/grid/columns/image/actions',
            mediaGalleryImageDetailsName: 'mediaGalleryImageDetails',
            mediaGalleryEditDetailsName: 'mediaGalleryEditDetails',
            actionsList: [
                {
                    name: 'image-details',
                    title: $t('View Details'),
                    handler: 'viewImageDetails'
                },
                {
                    name: 'edit',
                    title: $t('Edit'),
                    handler: 'editImageDetails'
                },
                {
                    name: 'delete',
                    title: $t('Delete'),
                    handler: 'deleteImageAction'
                }
            ],
            modules: {
                imageModel: '${ $.imageModelName }',
                mediaGalleryImageDetails: '${ $.mediaGalleryImageDetailsName }',
                mediaGalleryEditDetails: '${ $.mediaGalleryEditDetailsName }'
            }
        },

        /**
         * Initialize the component
         *
         * @returns {Object}
         */
        initialize: function () {
            this._super();
            this.initEvents();

            return this;
        },

        /**
         * Initialize image action events
         */
        initEvents: function () {
            $(this.imageModel().addSelectedBtnSelector).click(function () {
                image.insertImage(
                    this.imageModel().getSelected(),
                    {
                        onInsertUrl: this.imageModel().onInsertUrl,
                        storeId: this.imageModel().storeId
                    }
                );
            }.bind(this));
            $(this.imageModel().deleteSelectedBtnSelector).click(function () {
                this.deleteImageAction(this.imageModel().selected());
            }.bind(this));

        },

        /**
         * Delete image action
         *
         * @param {Object} record
         */
        deleteImageAction: function (record) {
            var imageDetailsUrl = this.mediaGalleryImageDetails().imageDetailsUrl,
                deleteImageUrl = this.imageModel().deleteImageUrl;

            deleteImageWithDetailConfirmation.deleteImageAction([record.id], imageDetailsUrl, deleteImageUrl);
        },

        /**
         * View image details
         *
         * @param {Object} record
         */
        viewImageDetails: function (record) {
            var recordId = this.imageModel().getId(record);

            this.mediaGalleryImageDetails().showImageDetailsById(recordId);
        },

        /**
         * Edit image details
         *
         * @param {Object} record
         */
        editImageDetails: function (record) {
            var recordId = this.imageModel().getId(record);

            this.mediaGalleryEditDetails().showEditDetailsPanel(recordId);
        }
    });
});
