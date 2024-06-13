/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
define(function (require) {
    'use strict';

    var utils = {},
        _ = require('underscore'),
        root = typeof self == 'object' && self.self === self && self ||
            typeof global == 'object' && global.global === global && global ||
            Function('return this')() || {};

    root._ = _;

    return _.extend(
        utils,
        require('./arrays'),
        require('./compare'),
        require('./misc'),
        require('./objects'),
        require('./strings'),
        require('./template')
    );
});
