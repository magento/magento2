/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([], function () {
    'use strict';

    return function ($target, $owner, data) {
        $target.find('label[for="' + $target.find(data.enableBmlPayPal).attr('id') + '"]').removeClass('enabled');
        $target.find(data.enableBmlPayPal + ' option[value="0"]').prop('selected', true);
        $target.find(data.enableBmlPayPal).prop('disabled', true);
    };
});
