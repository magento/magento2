/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([], function () {
    'use strict';

    return function ($target, $owner, data) {
        $target.find(data.enableBml).prop('disabled', false);
        $target.find(data.enableBml + ' option[value="1"]').prop('selected', true);
        $target.find('label[for="' + $target.find(data.enableBml).attr('id') + '"]').addClass('enabled');
    };
});
