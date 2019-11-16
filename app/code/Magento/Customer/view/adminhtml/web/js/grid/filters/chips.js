/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/grid/filters/chips'
], function (Chips) {
    'use strict';

    return Chips.extend({

        /**
         * Clear previous filters while initializing element to prevent filters sharing between customers
         *
         * @param {Object} elem
         */
        initElement: function (elem) {
            this.clear();
            this._super(elem);
        }
    });
});
