/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'ko',
        'Magento_Paypal/js/model/iframe',
        'Magento_Ui/js/model/messageList'
    ],
    function (ko, iframe, messageList) {
        'use strict';

        return function (cartUrl, errorMessage, goToSuccessPage, successUrl) {
            if (this === window.self) {
                window.location = cartUrl;
            }

            if (!!errorMessage.message) {
                document.removeEventListener('click', iframe.stopEventPropagation, true);
                iframe.isInAction(false);
                messageList.addErrorMessage(errorMessage);
            } else if (!!goToSuccessPage) {
                window.location = successUrl;
            } else {
                window.location = cartUrl;
            }
        };
    }
);
