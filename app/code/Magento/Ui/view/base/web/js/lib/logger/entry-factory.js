/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

define([
    './entry'
], function (LogEntry) {
    'use strict';

    return {
        /**
         * @param {String} message
         * @param {Number} level
         * @param {Object} [messageData]
         * @returns {LogEntry}
         */
        createEntry: function (message, level, messageData) {
            return new LogEntry(message, level, messageData);
        }
    };
});
