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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            }
        }
    };
});
