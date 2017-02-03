/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Paypal/js/rules/payflow/express/disable'
], function (disableExpress) {
    'use strict';

    return function ($target, $owner, data) {

        $target.find('input[id="' + $target.find(data.enableExpress).attr('id') + '_inherit"]').prop('checked', true);
        disableExpress($target, $owner, data);
        $target.find(data.enableExpress).change();
    };
});
