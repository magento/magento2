/**
 * Copyright � 2015 Magento. All rights reserved.
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

            _bind: function()
            {
                $(this.element).on('click', this.showModal.bind(this));
            },

            showModal: function(e)
            {
                $('#new-video').modal('openModal');
                $('.video_image_role').prop('disabled', false);
                $('#new_video_form')[0].reset();
                if ($('.image.item').length < 1) {
                    $('.video_image_role').prop('checked', true);
                }
            }
        }
    );

    return $.mage.productGallery;
});
