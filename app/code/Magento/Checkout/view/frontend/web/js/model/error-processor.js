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
    'consoleLogger'
], function (url, globalMessageList, consoleLogger) {
=======
    'mage/translate'
], function (url, globalMessageList, $t) {
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
                    messageContainer.addErrorMessage(error);
                } catch (e) {
                    consoleLogger.error(e);
                }
=======
                } catch (exception) {
                    error = $t('Something went wrong with your request. Please try again later.');
                }
                messageContainer.addErrorMessage(error);
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
            }
        }
    };
});
