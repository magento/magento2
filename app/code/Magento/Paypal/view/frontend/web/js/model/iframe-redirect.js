/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
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

        if (!!errorMessage.message) { //eslint-disable-line no-extra-boolean-cast
            document.removeEventListener('click', iframe.stopEventPropagation, true);
            iframe.isInAction(false);
            messageList.addErrorMessage(errorMessage);
        } else if (!!goToSuccessPage) { //eslint-disable-line no-extra-boolean-cast
            window.location = successUrl;
        } else {
            window.location = cartUrl;
        }
    };
});
