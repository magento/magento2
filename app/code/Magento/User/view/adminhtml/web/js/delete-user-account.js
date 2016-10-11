/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery"
], function($){
    'use strict';

    return function (params, elem) {

        elem.on('click', function() {
            if ($.validator.validateElement($('[name="current_password"]'))) {
                var postData = {
                    'data' : {
                        'user_id': params.objId,
                        'current_password': $('[name="current_password"]').val()
                    }
                }
                deleteConfirm(params.message, params.url, postData);
            }
        });
    }
});
