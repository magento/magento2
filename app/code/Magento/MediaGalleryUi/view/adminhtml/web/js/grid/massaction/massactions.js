/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'uiComponent',
    'Magento_MediaGalleryUi/js/action/deleteImageWithDetailConfirmation',
    'uiLayout',
    'underscore',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, Component, DeleteImages, Layout, _, uiAlert, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            deleteButtonSelector: '#delete_selected_massaction',
            deleteImagesSelector: '#delete_massaction',
            mediaGalleryImageDetailsName: 'mediaGalleryImageDetails',
            modules: {
                massactionView: '${ $.name }_view',
                imageModel: '${ $.imageModelName }',
                mediaGalleryImageDetails: '${ $.mediaGalleryImageDetailsName }'
            },
            viewConfig: [
                {
                    component: 'Magento_MediaGalleryUi/js/grid/massaction/massactionView',
                    name: '${ $.name }_view'
                }
            ],
            imports: {
                imageItems: '${ $.mediaGalleryProvider }:data.items'
            },
            listens: {
                imageItems: 'checkButtonVisibility'
            },
            exports: {
                massActionMode: '${ $.name }_view:massActionMode'
            }
        },

        /**
         * Initializes media gallery massaction component.
         *
         * @returns {Sticky} Chainable.
         */
        initialize: function () {
            this._super().observe([
                'massActionMode'
            ]);
            this.initView();
            this.initEvents();

            return this;
        },

        /**
         * Initialize child components
         *
         * @returns {Object}
         */
        initView: function () {
            Layout(this.viewConfig);

            return this;
        },

        /**
         * Initilize massactions events for media gallery grid.
         */
        initEvents: function () {
            $(window).on('massAction.MediaGallery', function () {
                if (this.massActionMode()) {
                    return;
                }
                this.imageModel().selected(null);
                this.massActionMode(true);
                this.switchMode();
            }.bind(this));

            $(window).on('terminateMassAction.MediaGallery', function () {
                if (!this.massActionMode()) {
                    return;
                }

                this.massActionMode(false);
                this.switchMode();
                this.imageModel().updateSelected();
            }.bind(this));
        },

        /**
         * Return total selected items.
         */
        getSelectedCount: function () {
            if (this.massActionMode() && !_.isNull(this.imageModel().selected())) {
                return Object.keys(this.imageModel().selected()).length;
            }

            return 0;
        },

        /**
         * If images records less than one, disable "delete images" button
         */
        checkButtonVisibility: function () {
            if (this.imageItems.length < 1) {
                $(this.deleteImagesSelector).addClass('disabled');
            } else {
                $(this.deleteImagesSelector).removeClass('disabled');
            }
        },

        /**
         * Switch massaction per current event.
         */
        switchMode: function () {
            this.massactionView().switchView();
            this.handleDeleteAction();
        },

        /**
         * Change Default  behavior of delete image to bulk deletion.
         */
        handleDeleteAction: function () {
            if (this.massActionMode()) {
                $(this.deleteButtonSelector).on('massDelete.MediaGallery', function () {
                    if (this.getSelectedCount() < 1) {
                        uiAlert({
                            content: $t('You need to select at least one image')
                        });

                    } else {
                        DeleteImages.deleteImageAction(
                            this.imageModel().selected(),
                            this.mediaGalleryImageDetails().imageDetailsUrl,
                            this.imageModel().deleteImageUrl
                        ).then(function (response) {
                            if (response.status === 'canceled') {
                                return;
                            }
                            $(window).trigger('terminateMassAction.MediaGallery');
                        });
                    }
                }.bind(this));
            }
        }
    });
});
