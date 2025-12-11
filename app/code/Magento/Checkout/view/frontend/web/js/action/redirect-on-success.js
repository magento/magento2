/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * @api
 */
define(
    [
        'mage/url',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (url, fullScreenLoader) {
        'use strict';

        return {
            redirectUrl: window.checkoutConfig.defaultSuccessPageUrl,

            /**
             * Provide redirect to page
             */
            execute: function () {
                fullScreenLoader.startLoader();
                this.redirectToSuccessPage();
            },

            /**
             * Redirect to success page.
             */
            redirectToSuccessPage: function () {
                window.location.replace(url.build(this.redirectUrl));
            }
        };
    }
);
