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
     * @param {LogFormatter} formatter
     */
    function ConsoleOutputHandler(formatter) {
        /**
         * @protected
         * @type {LogFormatter}
         */
        this.formatter_ = formatter;
    }

    /**
     * Display data of the provided entry to the console.
     *
     * @param {LogEntry} entry - Entry to be displayed.
     */
    ConsoleOutputHandler.prototype.show = function (entry) {
        var displayString = this.formatter_.process(entry);

        switch (entry.level) {
            case levels.ERROR:
                console.error(displayString);
                break;

            case levels.WARN:
                console.warn(displayString);
                break;

            case levels.INFO:
                console.info(displayString);
                break;

            case levels.DEBUG:
                console.log(displayString);
                break;
        }
    };

    /**
     * Displays the array of entries.
     *
     * @param {Array<LogEntry>} entries
     */
    ConsoleOutputHandler.prototype.dump = function (entries) {
        entries.forEach(this.show, this);
    };

    return ConsoleOutputHandler;
});
