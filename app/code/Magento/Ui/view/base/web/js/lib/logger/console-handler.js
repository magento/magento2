/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'logger/levels'
], function (levels) {
    'use strict';

    /**
     *
     * @param {LogFormatter} formatter
     */
    function ConsoleEntryHandler(formatter) {
        /**
         *
         * @protected {LogFormatter}
         */
        this.formatter_ = formatter;
    }

    /**
     *
     * @param {LogEntry} entry
     * @returns {void}
     */
    ConsoleEntryHandler.prototype.process = function (entry) {
        const displayString = this.formatter_.process(entry);

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
                console.debug(displayString);
                break;
        }
    }

    return ConsoleEntryHandler;
});
