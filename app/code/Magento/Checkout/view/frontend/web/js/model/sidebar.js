/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([], function () {
    'use strict';

    return {
        popUp: false,

        /**
         * @param {Object} popUp
         */
        setPopup: function (popUp) {
            this.popUp = popUp;
        },

        /**
         * Show popup.
         */
        show: function () {
            if (this.popUp) {
                this.popUp.modal('openModal');
            }
        },

        /**
         * Hide popup.
         */
        hide: function () {
            if (this.popUp) {
                this.popUp.modal('closeModal');
            }
        }
    };
});
