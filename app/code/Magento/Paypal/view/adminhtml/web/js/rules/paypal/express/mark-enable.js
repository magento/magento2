/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Paypal/js/rules/simple/mark-enable'
], function (markEnable) {
    "use strict";
    return function ($target, $owner, data) {
        if ($owner.find(data.enableButton).val() == 1) {
            markEnable($target, $owner, data);
        }
    };
});
