/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([], function () {
    'use strict';

    return function ($target, $owner, data) {
        $target.find('label[for="' + $target.find(data.enableExpress).attr('id') + '"]').removeClass('enabled');
        $target.find(data.enableExpress + ' option[value="0"]').prop('selected', true);
        $target.find(data.enableExpress).prop('disabled', true);
    };
});
