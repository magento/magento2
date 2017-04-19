/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore'
], function (_) {
    'use strict';

    var LEVELS,
        CODE_MAP;

    LEVELS = {
        NONE: 0,
        ERROR: 1,
        WARN: 2,
        INFO: 3,
        DEBUG: 4,
        ALL: 5
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
