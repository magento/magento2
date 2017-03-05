/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery'
], function ($) {
    'use strict';

    var postData;

    return function (params, elem) {

        elem.on('click', function () {

            postData = {
                'data': {
                    'user_id': params.objId,
                    'current_password': $('[name="current_password"]').val()
                }
            };

            if ($.validator.validateElement($('[name="current_password"]'))) {
                window.deleteConfirm(params.message, params.url, postData);
            }
        });
    };
});
