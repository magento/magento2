/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

define([
    'Magento_Ui/js/form/element/country'
], function (Country) {
    'use strict';

    return Country.extend({
        defaults: {
            countryScope: 'data.country'
        },

        /**
         * Set country to customer address form
         *
         * @param {String} value - country
         */
        setDifferedFromDefault: function (value) {
            this._super();

            if (value) {
                this.source.set(this.countryScope, this.indexedOptions[value].label);
            }
        }
    });
});
