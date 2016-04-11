/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([], function () {
    'use strict';

    return function ($target, $owner, data) {
        if ($target.find(data.enableButton).val() === '0') {
            $target.find(data.enableBml).prop('disabled', true);
        }
    };
});
