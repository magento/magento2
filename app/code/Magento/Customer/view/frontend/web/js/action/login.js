/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        'mage/storage',
        'Magento_Ui/js/model/messageList',
        'Magento_Customer/js/customer-data'
    ],
    function($, storage, globalMessageList, customerData) {
        'use strict';
        var callbacks = [],
            action = function(loginData, redirectUrl, isGlobal, messageContainer) {
                messageContainer = messageContainer || globalMessageList;
                return storage.post(
                    'customer/ajax/login',
                    JSON.stringify(loginData),
                    isGlobal
                ).done(function (response) {
                    if (response.errors) {
                        messageContainer.addErrorMessage(response);
                        callbacks.forEach(function(callback) {
                            callback(loginData);
                        });
                    } else {
                        callbacks.forEach(function(callback) {
                            callback(loginData);
                        });
                        customerData.invalidate(['customer']);
                        if (redirectUrl) {
                            window.location.href = redirectUrl;
                        } else if (response.redirectUrl) {
                            window.location.href = response.redirectUrl;
                        } else {
                            location.reload();
                        }
                    }
                }).fail(function () {
                    messageContainer.addErrorMessage({'message': 'Could not authenticate. Please try again later'});
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
