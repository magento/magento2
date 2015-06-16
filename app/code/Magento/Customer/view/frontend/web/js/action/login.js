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
        'use strict';
        var callbacks = [],
            action = function(loginData, redirectUrl) {
                return storage.post(
                    'customer/ajax/login',
                    JSON.stringify(loginData)
                ).done(function (response) {
                    if (response.errors) {
                        errorlist.add(response);
                        callbacks.forEach(function(callback) {
                            callback(loginData);
                        });
                    } else {
                        callbacks.forEach(function(callback) {
                            callback(loginData);
                        });
                        if (redirectUrl) {
                            window.location.href = redirectUrl;
                        } else {
                            location.reload();
                        }
                    }
                }).fail(function () {
                    errorlist.add({'message': 'Could not authenticate. Please try again later'});
                    callbacks.forEach(function(callback) {
                        callback(loginData);
                    });
                });
            };

        action.registerLoginCallback = function(callback) {
            callbacks.push(callback);
        };

        return action;
    }
);
