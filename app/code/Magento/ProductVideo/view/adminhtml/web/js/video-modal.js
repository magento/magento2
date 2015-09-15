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
                var events = {
                    'mouseup [data-role=delete-button]': function (event) {
                        event.preventDefault();
                        var $imageContainer = $(event.currentTarget).closest(this.options.imageSelector);
                        this.element.find('[data-role=dialog]').trigger('close');
                        this.element.trigger('removeItem', $imageContainer.data('imageData'));
                    },
                }
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
            }
        }
    );

    return $.mage.productGallery;
});
