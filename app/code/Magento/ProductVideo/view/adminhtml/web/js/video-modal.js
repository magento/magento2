/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'productGallery',
    'jquery/ui',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'mage/backend/tree-suggest',
    'mage/backend/validation'
], function ($, productGallery) {
    'use strict';

    $.widget('mage.productGallery', productGallery, {

        /**
         * * Fired when widget initialization start
         * @private
         */
        _create: function () {
            this._bind();
        },

        /**
         * Bind events
         * @private
         */
        _bind: function () {
            $(this.element).on('click', this.showModal.bind(this));
            $('.gallery.ui-sortable').on('openDialog', $.proxy(this._onOpenDialog, this));
        },

        /**
         * Open dialog for external video
         * @private
         */
        _onOpenDialog: function (e, imageData) {

            if (imageData['media_type'] !== 'external-video') {
                return;
            }
            this.showModal();
        },

        /**
         * Fired on trigger "openModal"
         */
        showModal: function () {

            $('#new-video').modal('openModal');
        }
    });

    return $.mage.productGallery;
});
