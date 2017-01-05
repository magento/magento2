/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    './renderer/types',
    './renderer/layout',
    '../lib/knockout/bootstrap'
], function (types, layout) {
    'use strict';

    return function (data, merge) {
        types.set(data.types);
        layout(data.components, undefined, true, merge);
    };
});
