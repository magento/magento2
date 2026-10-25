/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

define([
    'Magento_Ui/js/form/element/select'
], function (Select) {
    'use strict';

    return Select.extend({
        defaults: {
            currenciesForWebsites: {},
            tracks: {
                currency: true
            }
        },

        /**
         * Set currency symbol per website
         *
         * @param {String} value - currency symbol
         */
        setDifferedFromDefault: function (value) {
            this.currency = this.currenciesForWebsites[value];

            return this._super();
        }
    });
});
