/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([], function () {
    'use strict';

    return function ($target, $owner, data) {
        $target.find('label[for="' + $target.find(data.enableBml).attr('id') + '"]').removeClass('enabled');
        $target.find(data.enableBml + ' option[value="0"]').prop('selected', true);
        $target.find(data.enableBml).prop('disabled', true);
    };
});
