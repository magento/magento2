/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Checkout/js/model/url-builder'
], function ($, urlBuilder) {
    'use strict';

    return $.extend(urlBuilder, {
        storeCode: window.giftOptionsConfig.storeCode
    });
});
