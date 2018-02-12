/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    /**
     * Return url parameters.
     * @returns {Object}
     */
    function urlParameters() {
        var params = {},
            queries = window.location.search.substring(1).split('&'),
            temp,
            i,
            l;

        for (i = 0, l = queries.length; i < l; i++) {
            temp = queries[i].split('=');
            params[temp[0]] = temp[1];
        }

        return params;
    }

    return function (data) {
        $.ajax({
            method: 'GET',
            url: data.url,
            data: {
                'q': urlParameters().q
            }
        });
    };
});
