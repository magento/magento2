/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
