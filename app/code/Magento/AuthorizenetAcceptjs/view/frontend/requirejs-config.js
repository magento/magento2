/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    shim: {
        acceptjstest: {
            exports: 'Accept'
        },
        acceptjssandbox: {
            exports: 'Accept'
        }
    },
    paths: {
        acceptjssandbox: 'https://jstest.authorize.net/v1/Accept',
        acceptjs: 'https://jst.authorize.net/v1/Accept'
    }
};
