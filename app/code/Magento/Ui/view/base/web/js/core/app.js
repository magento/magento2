/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    './renderer/types',
    './renderer/layout'
], function (types, layout) {
    'use strict';

    return function (data) {
        types.set(data.types);
        layout(data.components);
    };
});
