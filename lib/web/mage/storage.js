/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(['jquery', 'mage/url'], function ($, urlBuilder) {
    "use strict";
    return {
        get: function (url, contentType) {
            contentType = contentType || 'application/json';
            return $.ajax({
                url: urlBuilder.build(url),
                type: 'GET',
                async: false,
                contentType: contentType
            });
        },
        post: function(url, data, contentType) {
            contentType = contentType || 'application/json';
            return $.ajax({
                url: urlBuilder.build(url),
                type: 'POST',
                data: data,
                contentType: contentType
            });
        },
        put: function(url, data, contentType) {
            contentType = contentType || 'application/json';
            return $.ajax({
                url: urlBuilder.build(url),
                type: 'PUT',
                data: data,
                contentType: contentType
            });
        }
    };
});
