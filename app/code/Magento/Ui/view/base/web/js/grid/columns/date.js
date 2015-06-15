/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'mageUtils',
    'moment',
    './column'
], function (utils, moment, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            dateFormat: 'MMM D, YYYY h:mm:ss A'
        },

        /**
         * Initializes components' static properties.
         *
         * @returns {DateColumn} Chainable.
         */
        initProperties: function () {
            this.dateFormat = utils.normalizeDate(this.dateFormat);

            return this._super();
        },

        /**
         * Formats incoming date based on the 'dateFormat' property.
         *
         * @param {String} date - Date to be formatted.
         * @returns {String} Formatted date.
         */
        getLabel: function (date) {
            date = moment(date);

            date = date.isValid() ?
                date.format(this.dateFormat) :
                '';

            return date;
        }
    });
});
