/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([], function () {
    'use strict';

    return function ($target, $owner, data) {
        $target.find(data.enableButton + ' option[value="0"]').prop('selected', true);
        $target.find('label.enabled').removeClass('enabled');
        $target.find('.section-config').removeClass('enabled');
    };
});
