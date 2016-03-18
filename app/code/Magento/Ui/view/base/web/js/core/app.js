/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    './renderer/types',
    './renderer/layout',
    'Magento_Ui/js/lib/ko/initialize'
], function (types, layout) {
    'use strict';

    return function (data) {
        types.set(data.types);
        layout(data.components);
    };
});
