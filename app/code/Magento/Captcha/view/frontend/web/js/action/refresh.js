/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
