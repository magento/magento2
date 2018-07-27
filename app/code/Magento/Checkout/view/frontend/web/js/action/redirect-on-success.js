/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'mage/url'
    ],
    function (url) {
        'use strict';

        return {
            redirectUrl: window.checkoutConfig.defaultSuccessPageUrl,

            /**
             * Provide redirect to page
             */
            execute: function () {
                window.location.replace(url.build(this.redirectUrl));
            }
        };
    }
);
