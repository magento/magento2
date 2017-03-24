/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    './log-entry'
], function (LogEntry) {
    'use strict';

    /**
     *
     */
    function LogEntryFactory() {}

    /**
     *
     * @param {string} message
     * @param {number} level
     * @param {Object} [messageData]
     * @returns {LogEntry}
     */
    LogEntryFactory.prototype.create = function (message, level, messageData) {
        return new LogEntry(message, level, messageData);
    };
});
