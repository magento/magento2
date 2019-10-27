define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/modal'
], function($, $tr, modal){
    'use strict';

    $.widget('mage.cookieStatus', {
        /**
         * Init object
         * @private
         */
        _init: function () {

            if(!navigator.cookieEnabled) {
                console.log('popup');

                const options = {
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
                };

                modal(options, $('#cookie-status'));
            }
        }
    });
    
    return $.mage.cookieStatus;
});