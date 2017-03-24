/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    './levels'
], function (levels) {
    'use strict';

    /**
     *
     * @param {LogEntryHandler} entryHandler
     * @param {LogEntryFactory} entryFactory
     * @param {LogCriteriaFactory} criteriaFactory
     */
    function Logger(entryHandler, entryFactory) {
        /**
         *
         * @protected {Array<LogEntry>}
         */
        this.entries_ = [];

        /**
         *
         * @protected {number}
         */
        this.displayLevel_ = levels.ALL;

        /**
         *
         * @protected {Array<LogCriteria>}
         */
        this.displayCriterias_ = [];

        /**
         *
         * @protected {LogEntryFactory}
         */
        this.entryFactory_ = entryFactory;

        /**
         *
         * @protected {LogEntryHandler}
         */
        this.entryHandler_ = entryHandler;

        this.addDisplayMask(this.matchesLevel_.bind(this));
    }

    /**
     *
     * @param {number} level
     * @returns {void}
     */
    Logger.prototype.setDisplayLevel = function (level) {
        var levels = Object.values(Logger.levels);

        if (!~levels.indexOf(level)) {
            throw new TypeError('');
        }

        this.displayLevel_ = level;
    };

    /**
     *
     * @param {LogCriteria} criteria
     * @returns {void}
     */
    Logger.prototype.addDisplayMask = function (criteria) {
        this.displayCriterias_.push(criteria);
    };

    /**
     *
     * @param {LogCriteria} criteria
     * @returns {void}
     */
    Logger.prototype.removeDisplayMask = function (criteria) {
        var index = this.displayCriterias_.indexOf(criteria);

        if (~index) {
            this.displayCriterias_.splice(index, 1);
        }
    };

    /**
     *
     * @param {string} message
     * @param {MessageData} messageData
     * @returns {LogEntry}
     */
    Logger.prototype.error = function (message, messageData) {
        return this.log_(message, levels.ERROR, messageData);
    };

    /**
     *
     * @param {string} message
     * @param {MessageData} messageData
     * @returns {LogEntry}
     */
    Logger.prototype.warn = function (message, messageData) {
        return this.log_(message, levels.WARN, messageData);
    };

    /**
     *
     * @param {string} message
     * @param {MessageData} messageData
     * @returns {LogEntry}
     */
    Logger.prototype.info = function (message, messageData) {
        return this.log_(message, levels.INFO, messageData);
    };

    /**
     *
     * @returns {LogEntry}
     */
    Logger.prototype.debug = function (message, messageData) {
        return this.log_(message, levels.DEBUG, messageData);
    };

    /**
     *
     * @protected
     * @param {string} message
     * @param {string} level
     */
    Logger.prototype.log_ = function (message, level, data) {
        const entry = this.entryFactory_.create(message, level, messageData);

        this.entries_.push(entry);

        if (this.matchesCriterias_(entry)) {
            this.entryHandler_.process(entry);
        }

        return entry;
    };

    /**
     *
     * @param {LogCriteria} [criteria]
     * @returns {Array<LogEntry>}
     */
    Logger.prototype.dump = function (criteria) {
        if (criteria) {
            return this.entries_.filter(criteria);
        }

        return this.entries_;
    };

    /**
     *
     * @protected
     * @param {LogEntry} entry
     * @returns {boolean}
     */
    Logger.prototype.matchesCriterias_ = function (entry) {
        return this.displayCriterias_.every(criteria => {
            return criteria.matches(entry);
        });
    };

    /**
     *
     * @protected
     * @param {LogEntry} entry
     * @returns {boolean}
     */
    Logger.prototype.matchesLevel_ = function (entry) {
        return entry.level >= this.displayLevel_;
    };

    Logger.levels = levels;

    return Logger;
});

