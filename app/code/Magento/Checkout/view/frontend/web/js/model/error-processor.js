/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * @api
 */
define([
    'mage/url',
    'Magento_Ui/js/model/messageList',
    'mage/translate'
], function (url, globalMessageList, $t) {
    'use strict';

    return {
        /**
         * @param {Object} response
         * @param {Object} messageContainer
         */
        process: function (response, messageContainer) {
            var error;

            messageContainer = messageContainer || globalMessageList;

            if (response.status == 401) { //eslint-disable-line eqeqeq
                error = {
                    message: $t('You are not authorized to access this resource.')
                };
                messageContainer.addErrorMessage(error);
                this.redirectTo(url.build('customer/account/login/'), 2000);
            } else {
                try {
                    error = JSON.parse(response.responseText);
                } catch (exception) {
                    error = {
                        message: $t('Something went wrong with your request. Please try again later.')
                    };
                }
                messageContainer.addErrorMessage(error);
            }
        },

        /**
         * Method to redirect by requested URL.
         */
        redirectTo: function (redirectUrl, delay = 0) {
            setTimeout(() => {
                window.location.replace(redirectUrl);
            }, delay);
        }
    };
});
