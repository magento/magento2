/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    './levels-pool'
], function (logLevels) {
    'use strict';

    var levels = logLevels.getLevels();

    /**
     * @param {LogOutputHandler} outputHandler
     * @param {LogEntryFactory} entryFactory
     */
    function Logger(outputHandler, entryFactory) {
        /**
         * An array of log entries.
         *
         * @protected
         * @type {Array<LogEntry>}
         */
        this.entries_ = [];

        /**
         * Current display level.
         *
         * @protected
         * @type {Number}
         */
        this.displayLevel_ = levels.ERROR;

        /**
         * An array of display criteria.
         *
         * @protected
         * @type {Array<LogCriteria>}
         */
        this.displayCriteria_ = [];

        /**
         * @protected
         * @type {LogEntryFactory}
         */
        this.entryFactory_ = entryFactory;

        /**
         * @protected
         * @type {Array<LogOutputHandler>}
         */
        this.outputHandlers_ = [outputHandler];

        this.addDisplayCriteria(this.matchesLevel_);
    }

    /**
     * Swaps current display level with the provided one.
     *
     * @param {Number} level - Level's code.
     */
    Logger.prototype.setDisplayLevel = function (level) {
        var levelName = logLevels.getNameByCode(level);

        if (!levelName) {
            throw new TypeError('The provided level is not defined in the levels list.');
        }

        this.displayLevel_ = level;
    };

    /**
     * Sets up the criteria by which log entries will be filtered out from the output.
     *
     * @param {LogCriteria} criteria
     */
    Logger.prototype.addDisplayCriteria = function (criteria) {
        this.displayCriteria_.push(criteria);
    };

    /**
     * Removes previously defined criteria.
     *
     * @param {LogCriteria} criteria
     */
    Logger.prototype.removeDisplayCriteria = function (criteria) {
        var index = this.displayCriteria_.indexOf(criteria);

        if (~index) {
            this.displayCriteria_.splice(index, 1);
        }
    };

    /**
     * @param {String} message
     * @param {Object} [messageData]
     * @returns {LogEntry}
     */
    Logger.prototype.error = function (message, messageData) {
        return this.log_(message, levels.ERROR, messageData);
    };

    /**
     * @param {String} message
     * @param {Object} [messageData]
     * @returns {LogEntry}
     */
    Logger.prototype.warn = function (message, messageData) {
        return this.log_(message, levels.WARN, messageData);
    };

    /**
     * @param {String} message
     * @param {Object} [messageData]
     * @returns {LogEntry}
     */
    Logger.prototype.info = function (message, messageData) {
        return this.log_(message, levels.INFO, messageData);
    };

    /**
     * @param {String} message
     * @param {Object} [messageData]
     * @returns {LogEntry}
     */
    Logger.prototype.debug = function (message, messageData) {
        return this.log_(message, levels.DEBUG, messageData);
    };

    /**
     * @protected
     * @param {String} message
     * @param {Number} level
     * @param {Object} [messageData]
     * @returns {LogEntry}
     */
    Logger.prototype.log_ = function (message, level, messageData) {
        var entry = this.createEntry_(message, level, messageData);

        this.entries_.push(entry);

        if (this.matchesCriteria_(entry)) {
            this.processOutput_(entry);
        }

        return entry;
    };

    /**
     * @protected
     * @param {String} message
     * @param {Number} level
     * @param {Object} [messageData]
     * @returns {LogEntry}
     */
    Logger.prototype.createEntry_ = function (message, level, messageData) {
        return this.entryFactory_.createEntry(message, level, messageData);
    };

    /**
     * Returns an array of log entries that have been added to the logger.
     *
     * @param {LogCriteria} [criteria] - Optional filter criteria.
     * @returns {Array<LogEntry>}
     */
    Logger.prototype.getEntries = function (criteria) {
        if (criteria) {
            return this.entries_.filter(criteria);
        }

        return this.entries_;
    };

    /**
     * @param {LogCriteria} [criteria]
     */
    Logger.prototype.dump = function (criteria) {
        var entries;

        if (!criteria) {
            criteria = this.matchesCriteria_;
        }

        entries = this.entries_.filter(criteria, this);

        this.outputHandlers_.forEach(function (handler) {
            handler.dump(entries);
        });
    };

    /**
     * @protected
     * @param {LogEntry} entry
     */
    Logger.prototype.processOutput_ = function (entry) {
        this.outputHandlers_.forEach(function (handler) {
            handler.show(entry);
        });
    };

    /**
     * @protected
     * @param {LogEntry} entry
     * @returns {Boolean}
     */
    Logger.prototype.matchesCriteria_ = function (entry) {
        return this.displayCriteria_.every(function (criteria) {
            return criteria.call(this, entry);
        }, this);
    };

    /**
     * Checks that the level of provided entry passes the "displayLevel_" threshold.
     *
     * @protected
     * @param {LogEntry} entry - Entry to be checked.
     * @returns {Boolean}
     */
    Logger.prototype.matchesLevel_ = function (entry) {
        return entry.level <= this.displayLevel_;
    };

    return Logger;
});
