/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    './levels-pool'
], function (logLevels) {
    'use strict';

    /**
     * @param {String} message
     * @param {Number} level
     * @param {Object} [data]
     */
    function LogEntry(message, level, data) {
        /**
         * @readonly
         * @type {Number}
         */
        this.timestamp = Date.now();

        /**
         * @readonly
         * @type {Number}
         */
        this.level = level;

        /**
         * @readonly
         * @type {String}
         */
        this.levelName = logLevels.getNameByCode(level);

        /**
         * @readonly
         * @type {Object}
         */
        this.data = data;

        /**
         * @readonly
         * @type {String}
         */
        this.message = message;
    }

    return LogEntry;
});
