/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(function (require) {
    'use strict';

    var utils = {},
        _ = require('underscore'),
        root = this || (0, eval)('this');

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
