/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
