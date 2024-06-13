/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
