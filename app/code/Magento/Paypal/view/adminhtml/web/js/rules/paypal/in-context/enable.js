/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([], function () {
    'use strict';

    return function ($target, $owner, data) {
        $target.find(data.enableInContextPayPal).prop('disabled', false);
        $target.find(data.enableInContextPayPal + ' option[value="1"]').prop('selected', true);
        $target.find('label[for="' + $target.find(data.enableInContextPayPal).attr('id') + '"]').addClass('enabled');
    };
});
