/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
