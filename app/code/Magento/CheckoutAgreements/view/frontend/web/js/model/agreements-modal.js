/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, modal, $t) {
    'use strict';

    return {
        modalWindow: null,

        /**
         * Create popUp window for provided element.
         *
         * @param {HTMLElement} element
         */
        createModal: function (element) {
            var options;

            this.modalWindow = element;
            options = {
                'type': 'popup',
                'modalClass': 'agreements-modal',
                'responsive': true,
                'innerScroll': true,
                'trigger': '.show-modal',
                'buttons': [
                    {
                        text: $t('Close'),
                        class: 'action secondary action-hide-popup',

                        /** @inheritdoc */
                        click: function () {
                            this.closeModal();
                        }
                    }
                ]
            };
            modal(options, $(this.modalWindow));
        },

        /** Show login popup window */
        showModal: function () {
            $(this.modalWindow).modal('openModal');
        }
    };
});
