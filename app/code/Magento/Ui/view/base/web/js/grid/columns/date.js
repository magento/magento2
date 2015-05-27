/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
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
            return moment(data).isValid() ? moment(data).format(this.dateFormat) : '';
        }
    });
});
