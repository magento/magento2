/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([], function () {
    'use strict';

    return function ($target, $owner, data) {
        $target.find(data.enableExpress).prop('disabled', false);
        $target.find(data.enableExpress + ' option[value="1"]').prop('selected', true);
        $target.find('label[for="' + $target.find(data.enableExpress).attr('id') + '"]').addClass('enabled');
        $target.find(data.enableExpress).change();
    };
});
