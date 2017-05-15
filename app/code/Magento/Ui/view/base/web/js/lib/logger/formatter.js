/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'moment',
    'mage/utils/template'
], function (moment, mageTemplate) {
    'use strict';

    /**
     * @param {String} dateFormat
     * @param {String} template
     */
    function LogFormatter(dateFormat, template) {
        /**
         * @protected
         * @type {String}
         */
        this.dateFormat_ = 'YYYY-MM-DD hh:mm:ss';

        /**
         * @protected
         * @type {String}
         */
        this.template_ = '[${ $.date }] [${ $.entry.levelName }] ${ $.message }';

        if (dateFormat) {
            this.dateFormat_ = dateFormat;
        }

        if (template) {
            this.template_ = template;
        }
    }

    /**
     * @param {LogEntry} entry
     * @returns {String}
     */
    LogFormatter.prototype.process = function (entry) {
        var message = mageTemplate.template(entry.message, entry.data),
            date = moment(entry.timestamp).format(this.dateFormat_);

        return mageTemplate.template(this.template_, {
            date: date,
            entry: entry,
            message: message
        });
    };

    return LogFormatter;
});
