/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * @api
 */
define([
    'mageUtils',
    'moment',
    './column',
    'underscore',
    'moment-timezone-with-data'
], function (utils, moment, Column, _) {
    'use strict';

    return Column.extend({
        defaults: {
            dateFormat: 'MMM d, YYYY h:mm:ss A',
            calendarConfig: []
        },

        /**
         * Overrides base method to normalize date format
         *
         * @returns {DateColumn} Chainable
         */
        initConfig: function () {
            this._super();

            this.dateFormat = utils.normalizeDate(this.dateFormat ? this.dateFormat : this.options.dateFormat);

            return this;
        },

        /**
         * Formats incoming date based on the 'dateFormat' property.
         *
         * @returns {String} Formatted date.
         */
        getLabel: function (value, format) {
            var date;

            if (this.storeLocale !== undefined) {
                moment.updateLocale(this.storeLocale, utils.extend({}, this.calendarConfig));
            }

            date = moment.utc(this._super());

            if (!_.isUndefined(this.timezone) && moment.tz.zone(this.timezone) !== null) {
                date = date.tz(this.timezone);
            }

            date = date.isValid() && value[this.index] ?
                date.format(format || this.dateFormat) :
                '';

            return date;
        }
    });
});
