/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([], function () {
    'use strict';

    return {
        loginUrl: 'https://ims-na1.adobelogin.com/ims/authorize',
        profileUrl: 'adobe_ims/user/profile',
        logoutUrl: 'adobe_ims/user/logout',
        manageAccountLink: 'https://account.adobe.com/',
        login: {
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
            popupWindowTimeout: 10000
        }
    };
});

