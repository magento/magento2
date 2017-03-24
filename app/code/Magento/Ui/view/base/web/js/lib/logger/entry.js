/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(function () {
    'use strict';

    /**
     *
     */
    function LogEntry(message, level, data) {
        /**
         *
         * @readonly
         */
        this.timestamp = Date.now();

        /**
         *
         * @readonly
         */
        this.level = level;

        /**
         *
         * @readonly
         */
        this.data = data;

        /**
         *
         * @readonly
         */
        this.message = message;
    }

    return LogEntry;
});
