/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'moment',
    'mageUtils',
    './abstract'
], function (moment, utils, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            dateFormat: 'MM/dd/YYYY',
            options: {}
        },

        /**
         * Initializes regular properties of instance.
         *
         * @returns {Object} Chainable.
         */
        initProperties: function () {
            this.dateFormat = utils.normalizeDate(this.dateFormat);

            return this._super();
        },

        /**
         * Formats provided value according to 'dateFormat' property.
         *
         * @returns {String}
         */
        normalizeData: function () {
            var value = this._super();

            if (value) {
                value = moment(value).format(this.dateFormat);
            }

            return value;
        }
    });
});
