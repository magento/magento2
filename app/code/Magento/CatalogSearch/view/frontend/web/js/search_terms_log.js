/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    /**
     * Return url param.
     * @param {String} name
     * @returns {String}
     */
    function urlParam(name) {
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);

        return results[1] || 0;
    }

    return function (data) {
        var url = data.url;

        $.ajax({
            method: 'GET',
            url: url,
            data: {
                'q': urlParam('q')
            },
            cache: false
        });
    };
});
