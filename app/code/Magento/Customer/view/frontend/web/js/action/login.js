/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        'mage/storage',
        'Magento_Ui/js/model/errorlist'
    ],
    function($, storage, errorlist) {
        "use strict";
        return function(loginData, redirectUrl) {
            return storage.post(
                'customer/ajax/login',
                JSON.stringify(loginData)
            ).done(function (response) {
                if (response) {
                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    } else {
                        location.reload();
                    }
                } else {
                    errorlist.add('Server returned no response');
                }
            }).fail(function (response) {
                if (response.status == 401) {
                    errorlist.add('Invalid login or password');
                } else {
                    errorlist.add('Could not authenticate. Please try again later');
                }
            });
        };
    }
);
