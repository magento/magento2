/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'Magento_AdobeIms/js/action/authorization'
], function ($, modal, authorization) {
    'use strict';

    var loginConfig = {
        url: 'https://auth-stg1.services.adobe.com/',
        callbackParsingParams: {
            regexpPattern: /auth\[code=(success|error);message=(.+)\]/,
            codeIndex: 1,
            messageIndex: 2,
            nameIndex: 3,
            successCode: 'success',
            errorCode: 'error'
        },
        popupWindowParams: {
            width: 500,
            height: 600,
            top: 100,
            left: 300
        },
        popupWindowTimeout: 60000
    }

    $("#save").click('click', function(event) {
        event.stopImmediatePropagation();
        authorization(loginConfig);
    });

});
