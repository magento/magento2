/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'productGallery',
    'jquery/ui',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'mage/backend/tree-suggest',
    'mage/backend/validation',
    'newVideoDialog'
], function ($, productGallery) {
    'use strict';

    $.widget('mage.productGallery', productGallery, {


        /**
         * Bind events
         * @private
         */
        _bind: function () {
            this._super();
            this.element.prev().find('[data-role="add-video-button"]').on('click', this.showModal.bind(this));
            this.element.on('openDialog', '.gallery.ui-sortable', $.proxy(this._onOpenDialog, this));
        },

        _create: function () {
            this._super();
            this.videoDialog = this.element.find('#new-video');
            this.videoDialog.mage('newVideoDialog', this.videoDialog.data('modalInfo'));
        },
        /**
         * Open dialog for external video
         * @private
         */
        _onOpenDialog: function (e, imageData) {

            if (imageData['media_type'] !== 'external-video') {
                this._superApply(arguments);
            } else {
                this.showModal();
            }
        },

        /**
         * Fired on trigger "openModal"
         */
        showModal: function () {
            this.videoDialog.modal('openModal');
        }
    });

    return $.mage.productGallery;
});
