/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'Magento_Ui/js/dynamic-rows/dynamic-rows'
], function ($, Abstract) {
    'use strict';

    return Abstract.extend({

        /**
         * Checks is relevant value
         *
         * @param {String} value
         * @returns {Boolean}
         */
        isRelevant: function (value) {
            if ($.inArray(value, ['drop_down', 'radio', 'checkbox', 'multiple']) !== -1) {
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
