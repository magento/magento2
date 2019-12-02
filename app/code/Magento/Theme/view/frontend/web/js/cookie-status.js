define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
], function($, modal){
    'use strict';

    $.widget('mage.cookieStatus', {
        options: {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            autoOpen: true,
            buttons: [{
                text: $.mage.__('Close'),
                class: 'cookie-status',
                click: function () {
                    this.closeModal();
                }
            }]
        },

        /**
         * Init object
         * @private
         */
        _init: function () {

            if(!navigator.cookieEnabled) {
                modal(this.options, $('#cookie-status'));
            }
        }
    });
    
    return $.mage.cookieStatus;
});