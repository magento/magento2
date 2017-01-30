/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Paypal/js/rules/simple/disable'
], function (disable) {
    'use strict';

    return function ($target, $owner, data) {
        disable($target, $owner, data);
        $target.find(data.enableButton).change();
    };
});
