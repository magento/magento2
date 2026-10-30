/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'Magento_Ui/js/form/element/abstract'
], function ($, Abstract) {
    'use strict';

    return Abstract.extend({

        /**
         * Checks for relevant value
         *
         * @param {*} value
         * @returns {Boolean}
         */
        isRelevant: function (value) {
            if ($.inArray(value, ['field', 'area']) !== -1) {
                this.disabled(false);
                this.visible(true);

                return true;
            }

            this.reset();
            this.disabled(true);
            this.visible(false);

            return false;
        }
    });
});
