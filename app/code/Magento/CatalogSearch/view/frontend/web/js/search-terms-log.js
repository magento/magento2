/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
