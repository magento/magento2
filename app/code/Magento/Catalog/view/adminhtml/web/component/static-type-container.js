/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'Magento_Ui/js/form/components/group'
], function ($, Group) {
    'use strict';

    return Group.extend({

        /**
         * Checks is relevant value
         *
         * @param {String} value
         * @returns {Boolean}
         */
        isRelevant: function (value) {
            if ($.inArray(value, ['field', 'area', 'file', 'date', 'date_time', 'time']) !== -1) {
                this.visible(true);

                return true;
            }

            this.visible(false);

            return false;
        }
    });
});
