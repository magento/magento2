/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery'
], function ($) {
    'use strict';

    return function (configFromPage) {
        var url = configFromPage.url;

        $.ajax({
            method: 'GET',
            url: url
        }).done(function (data) {
            $('div[data-role=partners-block]').html(data);
        });
    };
});
