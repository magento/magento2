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
            queryString = window.location.search,
            queries,
            temp,
            i,
            l;

        queryString = queryString.substring(1);
        queries = queryString.split('&');

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
