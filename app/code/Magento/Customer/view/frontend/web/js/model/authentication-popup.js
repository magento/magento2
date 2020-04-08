/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return {
        modalWindow: null,
        modalOptions: {
            'type': 'popup',
            'modalClass': 'popup-authentication',
            'focus': '[name=username]',
            'responsive': true,
            'innerScroll': true,
            'trigger': '.proceed-to-checkout',
            'buttons': []
        },

        /**
         * Create popUp window for provided element
         *
         * @param {HTMLElement} element
         */
        createPopUp: function (element) {
            this.modalWindow = element;
        },

        /** Show login popup window */
        showModal: function () {
            var $modalWindow = $(this.modalWindow),
                modalOptions = this.options;

            require(['Magento_Ui/js/modal/modal'], function (modal) {
                if (!$modalWindow.data('modal')) {
                    modal(modalOptions, $modalWindow);
                }

                $modalWindow.modal('openModal').trigger('contentUpdated');
            });
        }
    };
});
