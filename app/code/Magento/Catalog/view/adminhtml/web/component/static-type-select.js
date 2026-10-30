/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'Magento_Ui/js/form/element/select'
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
            if (!value || $.inArray(value, ['drop_down', 'radio', 'checkbox', 'multiple']) !== -1) {
                this.reset();
                this.disabled(true);

                return false;
            }

            this.disabled(false);

            return true;
        }
    });
});
