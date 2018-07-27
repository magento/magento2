/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'Magento_Ui/js/modal/modal'
    ],
    function ($, modal) {
        'use strict';

        return {
            modalWindow: null,

            /** Create popUp window for provided element */
            createPopUp: function (element) {
                this.modalWindow = element;
                var options = {
                    'type': 'popup',
                    'modalClass': 'popup-authentication',
                    'responsive': true,
                    'innerScroll': true,
                    'trigger': '.proceed-to-checkout',
                    'buttons': []
                };
                modal(options, $(this.modalWindow));
            },

            /** Show login popup window */
            showModal: function () {
                $(this.modalWindow).modal('openModal').trigger('contentUpdated');
            }
        }
    }
);
