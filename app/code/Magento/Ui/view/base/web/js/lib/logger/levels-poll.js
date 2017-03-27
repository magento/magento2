/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore'
], function (_) {
    'use strict';

    var LEVELS,
        CODE_MAP;

    LEVELS = {
        ALL: 0,
        DEBUG: 1,
        INFO: 2,
        WARN: 3,
        ERROR: 4,
        NONE: 5
    };

    CODE_MAP = _.invert(LEVELS);

    return {
        /**
         * Returns the list of available log levels.
         *
         * @returns {Object}
         */
        getLevels: function () {
            return LEVELS;
        },

        /**
         * Returns name of the log level that matches to the provided code.
         *
         * @returns {String}
         */
        getNameByCode: function (code) {
            return CODE_MAP[code];
        }
    };
});
