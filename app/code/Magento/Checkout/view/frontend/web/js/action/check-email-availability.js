/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global define*/
define(
    [
        'mage/storage',
        '../model/url-builder',
        'Magento_Customer/js/model/customer'
    ],
    function(storage, urlBuilder, customer) {
        "use strict";
        return function(deferred) {
            storage.post(
                urlBuilder.createUrl('/customers/isEmailAvailable', {}),
                JSON.stringify({
                    customerEmail: customer.customerData.email
                })
            ).done(
                function (isEmailAvailable) {
                    if (isEmailAvailable) {
                        deferred.resolve();
                    } else {
                        deferred.reject();
                    }
                }
            ).fail(
                function () {
                    deferred.reject();
                }
            );
        };
    }
);
