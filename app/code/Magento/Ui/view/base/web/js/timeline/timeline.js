/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'underscore',
    'moment',
    'uiLayout',
    'Magento_Ui/js/grid/listing'
], function (_, moment, layout, Listing) {
    'use strict';

    var ONE_DAY = 86400000;

    return Listing.extend({
        defaults: {
            recordTmpl: 'ui/timeline/record',
            dateFormat: 'YYYY-MM-DD HH:mm:ss',
            headerFormat: 'ddd MM/DD',
            detailsFormat: 'DD/MM/YYYY HH:mm:ss',
            scale: 7,
            scaleStep: 1,
            minScale: 7,
            maxScale: 28,
            minDays: 28,
            displayMode: 'timeline',
            displayModes: {
                timeline: {
                    label: 'Timeline',
                    value: 'timeline',
                    template: 'ui/timeline/timeline'
                }
            },
            viewConfig: {
                component: 'Magento_Ui/js/timeline/timeline-view',
                name: '${ $.name }_view',
                model: '${ $.name }'
            },
            tracks: {
                scale: true
            },
            statefull: {
                scale: true
            },
            range: {}
        },

        /**
         * Initializes Timeline component.
         *
         * @returns {Timeline} Chainable.
         */
        initialize: function () {
            this._super()
                .initView()
                .updateRange();

            return this;
        },

        /**
         * Initializes components configuration.
         *
         * @returns {Timeline} Chainable.
         */
        initConfig: function () {
            this._super();

            this.maxScale = Math.min(this.minDays, this.maxScale);
            this.minScale = Math.min(this.maxScale, this.minScale);

            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Timeline} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe.call(this.range, true, 'hasToday');

            return this;
        },

        /**
         * Initializes TimelineView component.
         *
         * @returns {Timeline} Chainable.
         */
        initView: function () {
            layout([this.viewConfig]);

            return this;
        },

        /**
         * Checks if provided event record is active,
         * i.e. it has already started.
         *
         * @param {Object} record
         * @returns {Boolean}
         */
        isActive: function (record) {
            return Number(record.status) === 1;
        },

        /**
         * Checks if provided event record is upcoming,
         * i.e. it will start later on.
         *
         * @param {Object} record
         * @returns {Boolean}
         */
        isUpcoming: function (record) {
            return Number(record.status) === 2;
        },

        /**
         * Checks if provided event record is permanent,
         * i.e. it has no ending time.
         *
         * @param {Object} record
         * @returns {Boolean}
         */
        isPermanent: function (record) {
            return !this.getEndDate(record);
        },

        /**
         * Checks if provided date indicates current day.
         *
         * @param {(Number|Moment)} date
         * @returns {Boolenan}
         */
        isToday: function (date) {
            return moment().isSame(date, 'day');
        },

        /**
         * Checks if range object contains todays date.
         *
         * @returns {Boolean}
         */
        hasToday: function () {
            return this.range.hasToday;
        },

        /**
         * Returns start date of provided record.
         *
         * @param {Object} record
         * @returns {String}
         */
        getStartDate: function (record) {
            return record['start_time'];
        },

        /**
         * Returns end date of provided record.
         *
         * @param {Object} record
         * @returns {String}
         */
        getEndDate: function (record) {
            return record['end_time'];
        },

        /**
         * Returns difference in days between records' start date
         * and a first day of a range.
         *
         * @param {Object} record
         * @returns {Number}
         */
        getStartDelta: function (record) {
            var start    = this.createDate(this.getStartDate(record)),
                firstDay = this.range.firstDay;

            return start.diff(firstDay, 'days', true);
        },

        /**
         * Calculates the amount of days that provided event lasts.
         *
         * @param {Object} record
         * @returns {Number}
         */
        getDaysLength: function (record) {
            var start   = this.createDate(this.getStartDate(record)),
                end     = this.createDate(this.getEndDate(record));

            if (!end.isValid()) {
                end = this.range.lastDay.endOf('day');
            }

            return end.diff(start, 'days', true);
        },

        /**
         * Creates new date object based on provided date string value.
         *
         * @param {String} dateStr
         * @returns {Moment}
         */
        createDate: function (dateStr) {
            return moment(dateStr, this.dateFormat);
        },

        /**
         * Converts days to weeks.
         *
         * @param {Number} days
         * @returns {Number}
         */
        daysToWeeks: function (days) {
            var weeks = days / 7;

            if (weeks % 1) {
                weeks = weeks.toFixed(1);
            }

            return weeks;
        },

        /**
         * Updates data of a range object,
         * e.g. total days, first day and last day, etc.
         *
         * @returns {Object} Range instance.
         */
        updateRange: function () {
            var firstDay    = this._getFirstDay(),
                lastDay     = this._getLastDay(),
                totalDays   = lastDay.diff(firstDay, 'days'),
                days        = [],
                i           = -1;

            if (totalDays < this.minDays) {
                totalDays += this.minDays - totalDays - 1;
            }

            while (++i <= totalDays) {
                days.push(+firstDay + ONE_DAY * i);
            }

            return _.extend(this.range, {
                days:       days,
                totalDays:  totalDays,
                firstDay:   firstDay,
                lastDay:    moment(_.last(days)),
                hasToday:   this.isToday(firstDay)
            });
        },

        /**
         *
         * @private
         * @param {String} key
         * @returns {Array<Moment>}
         */
        _getDates: function (key) {
            var dates = [];

            this.rows.forEach(function (record) {
                if (record[key]) {
                    dates.push(this.createDate(record[key]));
                }
            }, this);

            return dates;
        },

        /**
         * Returns date which is closest to the current day.
         *
         * @private
         * @returns {Moment}
         */
        _getFirstDay: function () {
            var dates = this._getDates('start_time'),
                first = moment.min(dates).subtract(1, 'day'),
                today = moment();

            if (!first.isValid() || first < today) {
                first = today;
            }

            return first.startOf('day');
        },

        /**
         * Returns the most distant date
         * specified in available records.
         *
         * @private
         * @returns {Moment}
         */
        _getLastDay: function () {
            var startDates  = this._getDates('start_time'),
                endDates    = this._getDates('end_time'),
                last        = moment.max(startDates.concat(endDates));

            return last.add(1, 'day').startOf('day');
        },

        /**
         * TODO: remove after integration with date binding.
         *
         * @param {Number} timestamp
         * @returns {String}
         */
        formatHeader: function (timestamp) {
            return moment(timestamp).format(this.headerFormat);
        },

        /**
         * TODO: remove after integration with date binding.
         *
         * @param {String} date
         * @returns {String}
         */
        formatDetails: function (date) {
            return moment(date).format(this.detailsFormat);
        }
    });
});
