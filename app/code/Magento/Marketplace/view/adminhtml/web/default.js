/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
