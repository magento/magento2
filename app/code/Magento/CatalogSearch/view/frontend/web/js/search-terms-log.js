/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'mageUtils'
], function ($, utils) {
    'use strict';

    return function (data) {
        $.ajax({
            method: 'GET',
            url: data.url,
            data: {
                'q': utils.getUrlParameters(window.location.href).q
            }
        });
    };
});
