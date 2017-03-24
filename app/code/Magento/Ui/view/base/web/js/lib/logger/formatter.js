/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'moment',
    'mage/utils/template'
], function (moment, template) {
    'use strict';

    /**
     *
     * @param {string} dateFormat
     * @param {string} template
     */
    function LogFormatter(dateFormat, template) {
        /**
         *
         * @protected {string}
         */
        this.dateFormat_ = 'YYYY-MM-DD hh:mm:ss';

        /**
         *
         * @protected {string}
         */
        this.template_ = '[${ $.date }] [${ $.levelName }] ${ $.message }';

        if (dateFormat) {
            this.dateFormat_ = dateFormat;
        }

        if (template) {
            this.template_ = template;
        }
    }

    /**
     * @param {LogEntry} entry
     * @returns {string}
     */
    LogFormatter.prototype.process = function (entry) {
        var message = template(entry.message, entry.data),
            date = moment(entry.timestamp).format(this.dateFormat_);

        return template(this.template_, {
            date: date,
            entry: entry,
            message: message
        });
    }

    return LogFormatter;
});
