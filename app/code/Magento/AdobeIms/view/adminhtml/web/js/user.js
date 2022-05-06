/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(['ko'], function (ko) {
    'use strict';

    return {
        isAuthorized: ko.observable(false),
        name: ko.observable(''),
        email: ko.observable('')
    };
});
