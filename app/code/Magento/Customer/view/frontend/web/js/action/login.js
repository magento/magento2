/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        'mage/storage',
        'Magento_Ui/js/model/errorlist',
        'Magento_Customer/js/model/customer'
    ],
    function($, storage, errorlist, customer) {
        "use strict";
        return function(loginData, redirectUrl) {
            return storage.post(
                'customer/ajax/login',
                JSON.stringify(loginData)
            ).done(function (response) {
                if (response.errors) {
                    customer.increaseFailedLoginAttempt();
                    errorlist.add(response);
                } else {
                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    } else {
                        location.reload();
                    }
                }
            }).fail(function () {
                customer.increaseFailedLoginAttempt();
                errorlist.add({'message': 'Could not authenticate. Please try again later'});
            });
        };
    }
);
