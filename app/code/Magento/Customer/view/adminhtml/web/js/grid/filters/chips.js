/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
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
