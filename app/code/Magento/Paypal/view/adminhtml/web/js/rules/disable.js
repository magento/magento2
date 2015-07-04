/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Paypal/js/rules/simple/disable'
], function (disable) {
    "use strict";
    return function ($target, $owner, data) {
        disable($target, $owner, data);
        $target.find(data.enableButton).change();
    };
});
