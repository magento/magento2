/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'Magento_Ui/js/modal/modal',
        'mage/translate'
    ],
    function ($, modal, $t) {
        'use strict';
        return {
            modalWindow: null,

            /** Create popUp window for provided element */
            createModal: function(element) {
                this.modalWindow = element;
                var options = {
                    'type': 'popup',
                    'modalClass': 'agreements-modal',
                    'responsive': true,
                    'innerScroll': true,
                    'trigger': '.show-modal',
                    'buttons': [
                        {
                            text: $t('Close'),
                            class: 'action secondary action-hide-popup',
                            click: function() {
                                this.closeModal();
                            }
                        }
                    ]
                };
                modal(options, $(this.modalWindow));
            },

            /** Show login popup window */
            showModal: function() {
                $(this.modalWindow).modal('openModal');
            }
        }
    }
);
