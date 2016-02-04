/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([], function () {
    'use strict';

    return function ($target) {
        $target.find('tr[id$="_merchant_id"], input[id$="_merchant_id"]').show();
        $target.find('input[id$="_merchant_id"]').attr('disabled', false);
    };
});
