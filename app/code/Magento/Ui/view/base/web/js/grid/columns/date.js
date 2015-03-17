define([
    './text',
    'moment'
], function (Text, moment) {
    'use strict';

    return Text.extend({
        defaults: {
            dateFormat: 'MMM d, YYYY h:mm:ss A'
        },

        getLabel: function (data) {
            return moment(data).format(this.dateFormat);
        }
    });
});
