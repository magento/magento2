/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return function (data) {
        var url = data.url;
        var query = data.query;

        $.ajax({
            method: 'GET',
            url: url,
            data: {
                'q': query
            },
            cache: false
        }).done();
    };
});
