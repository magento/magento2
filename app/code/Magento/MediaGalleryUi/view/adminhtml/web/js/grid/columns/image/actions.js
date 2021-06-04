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
    'mage/translate',
    'Magento_Ui/js/lib/view/utils/async'
], function ($, _, Component, deleteImageWithDetailConfirmation, image, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_MediaGalleryUi/grid/columns/image/actions',
            mediaGalleryImageDetailsName: 'mediaGalleryImageDetails',
            mediaGalleryEditDetailsName: 'mediaGalleryEditDetails',
            allowedActions: [],
            actionsList: [
                {
                    name: 'image-details',
                    title: $t('View Details'),
                    classes: 'action-menu-item',
                    handler: 'viewImageDetails'
                },
                {
                    name: 'edit',
                    title: $t('Edit'),
                    classes: 'action-menu-item',
                    handler: 'editImageDetails'
                },
                {
                    name: 'delete',
                    title: $t('Delete'),
                    classes: 'action-menu-item media-gallery-delete-assets',
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

            this.actionsList = this.actionsList.filter(function (item) {
                return this.allowedActions.includes(item.name);
            }.bind(this));

            if (!this.allowedActions.includes('delete')) {
                $.async('.media-gallery-delete-assets', function () {
                    $('.media-gallery-delete-assets').off('click').addClass('action-disabled');
                });
            }

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
