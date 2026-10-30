/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

/**
 * @api
 */
define([
    './column',
    'mageUtils'
], function (Column, utils) {
    'use strict';

    return Column.extend({
        defaults: {
            link: 'link',
            bodyTmpl: 'ui/grid/cells/link'
        },

        /**
         * Returns link to given record.
         *
         * @param {Object} record - Data to be preprocessed.
         * @returns {String}
         */
        getLink: function (record) {
            return utils.nested(record, this.link);
        },

        /**
         * Check if link parameter exist in record.
         * @param {Object} record - Data to be preprocessed.
         * @returns {Boolean}
         */
        isLink: function (record) {
            return !!utils.nested(record, this.link);
        }
    });
});
