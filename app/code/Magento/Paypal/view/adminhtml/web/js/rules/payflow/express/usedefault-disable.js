/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Paypal/js/rules/payflow/express/enable'
], function (enableExpress) {
    'use strict';

    return function ($target, $owner, data) {

        $target.find('input[id="' + $target.find(data.enableExpress).attr('id') + '_inherit"]').prop('checked', false);
        enableExpress($target, $owner, data);
        $target.find(data.enableExpress).change();
    };
});

