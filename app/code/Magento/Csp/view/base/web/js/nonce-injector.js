/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

define('Magento_Csp/js/nonce-injector', [], function () {
    'use strict';

    return function (config) {
        window.cspNonce = config.nonce;
    };
});
