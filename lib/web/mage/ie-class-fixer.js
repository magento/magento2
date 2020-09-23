/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([], function () {
    'use strict';

    if (navigator.userAgent.match(/Trident.*rv[ :]*11\./)) {
        document.documentElement.classList.add('ie11');
    }
});
