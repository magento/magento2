/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Paypal/js/rules/payflow/express/disable'
], function (disableExpress) {
    'use strict';

    return function ($target, $owner, data) {
        if ($target.find(data.enableButton).val() === '0') {
            disableExpress($target, $owner, data);
            $target.find(data.enableExpress).change();
        }
    };
});
