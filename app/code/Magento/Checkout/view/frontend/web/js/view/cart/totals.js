/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/shipping-service'
], function ($, Component, totalsService, shippingService) {
    'use strict';

    return Component.extend({
        isLoading: totalsService.isLoading,

        /**
         * @override
         */
        initialize: function () {
            this._super();
            totalsService.totals.subscribe(function () {
                if (navigator.userAgent.indexOf('MSIE') !== -1 || navigator.appVersion.indexOf('Trident/') > 0) {
                     var evt = document.createEvent('UIEvents');
                     evt.initUIEvent('resize', true, false, window, 0);
                     window.dispatchEvent(evt);
                } else {
                     window.dispatchEvent(new Event('resize'));
                }
            });
            shippingService.getShippingRates().subscribe(function () {
                if (navigator.userAgent.indexOf('MSIE') !== -1 || navigator.appVersion.indexOf('Trident/') > 0) {
                     var evt = document.createEvent('UIEvents');
                     evt.initUIEvent('resize', true, false, window, 0);
                     window.dispatchEvent(evt);
                } else {
                     window.dispatchEvent(new Event('resize'));
                }
            });
        }
    });
});
