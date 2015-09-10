/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'jquery/ui',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'mage/backend/tree-suggest',
    'mage/backend/validation'
], function ($) {

    $.widget('mage.productGallery',

        $.mage.productGallery,
        {
            _create: function() {
                this._bind();
            },

            _bind: function() {
                $(this.element).on('click', this.showModal.bind(this));
                $('.gallery.ui-sortable').on('openDialog', $.proxy(this._onOpenDialog, this));
            },

            _onOpenDialog: function(e, imageData)  {
                if(imageData.media_type != 'external-video') {
                    return;
                }
                this.showModal();
            },

            showModal: function(imageData)
            {
                $('#new-video').modal('openModal');
                $('.video_image_role').prop('disabled', false);
                if ($('.image.item').length < 1) {
                    $('.video_image_role').prop('checked', true);
                }
            }
        }
    );

    return $.mage.productGallery;
});
