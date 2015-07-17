/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([], function () {
    "use strict";
    return function ($target, $owner, data) {
        $target.find('.section-config').addClass('enabled');
    };
});
