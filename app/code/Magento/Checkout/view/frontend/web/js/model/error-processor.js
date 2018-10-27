/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'mage/url',
    'Magento_Ui/js/model/messageList',
<<<<<<< HEAD
    'mage/translate'
], function (url, globalMessageList, $t) {
=======
    'consoleLogger'
], function (url, globalMessageList, consoleLogger) {
>>>>>>> upstream/2.2-develop
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
                window.location.replace(url.build('customer/account/login/'));
            } else {
                try {
                    error = JSON.parse(response.responseText);
<<<<<<< HEAD
                } catch (exception) {
                    error = $t('Something went wrong with your request. Please try again later.');
                }
                messageContainer.addErrorMessage(error);
=======
                    messageContainer.addErrorMessage(error);
                } catch (e) {
                    consoleLogger.error(e);
                }
>>>>>>> upstream/2.2-develop
            }
        }
    };
});
