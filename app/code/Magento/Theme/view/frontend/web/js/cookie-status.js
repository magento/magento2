define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('mage.cookieStatus', {
        /**
         * Init object
         * @private
         */
        _init: function () {
            if (!navigator.cookieEnabled) {
                require(['Magento_Ui/js/modal/modal', 'mage/translate'], function (modal) {
                    var options = {
                        type: 'popup',
                        responsive: true,
                        innerScroll: true,
                        autoOpen: true,
                        buttons: [{
                            text: $.mage.__('Close'),
                            class: 'cookie-status',

                            /**
                             * Callback for click event
                             */
                            click: function () {
                                this.closeModal();
                            }
                        }]
                    };

                    modal(options, $('#cookie-status'));
                });
            }
        }
    });

    return $.mage.cookieStatus;
});
