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
    'use strict';

    $.widget('mage.productGallery',
        $.mage.productGallery,
        {

            /**
             * Fired when windget initialization start
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
            },

            /**
             * Fired on trigger "openModal"
             */
            showModal: function () {
                var videoimgRoleInput = $('.video_image_role');

                $('#new-video').modal('openModal');
                videoimgRoleInput.prop('disabled', false);
                $('#new_video_form')[0].reset();

                if ($('.image.item').length < 1) {
                    videoimgRoleInput.prop('checked', true);
                }
            }
        }
    );

    return $.mage.productGallery;
});
