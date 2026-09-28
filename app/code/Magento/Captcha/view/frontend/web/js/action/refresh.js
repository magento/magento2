/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'jquery', 'mage/url'
], function ($, urlBuilder) {
    'use strict';

    return function (refreshUrl, formId, imageSource) {
        return $.ajax({
            url: urlBuilder.build(refreshUrl),
            type: 'POST',
            data: JSON.stringify({
                'formId': formId
            }),
            global: false,
            contentType: 'application/json'
        }).done(
            function (response) {
                if (response.imgSrc) {
                    imageSource(response.imgSrc);
                }
            }
        );
    };
});
