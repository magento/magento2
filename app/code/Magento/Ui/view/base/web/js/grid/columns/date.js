define([
    'mageUtils',
    'moment',
    './sortable'
], function (utils, moment, Sortable) {
    'use strict';

    return Sortable.extend({
        defaults: {
            dateFormat: 'MMM D, YYYY h:mm:ss A'
        },

        initProperties: function () {
            this.dateFormat = utils.normalizeDate(this.dateFormat);

            return this._super();
        },

        getLabel: function (data) {
            return moment(data).format(this.dateFormat);
        }
    });
});
