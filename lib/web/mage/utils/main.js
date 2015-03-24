/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(function (require) {
    'use strict';

    var utils = {},
        _ = require('underscore');

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
