/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

define([
    'Magento_Ui/js/form/element/region'
], function (Region) {
    'use strict';

    return Region.extend({
        defaults: {
            regionScope: 'data.region'
        },

        /**
         * Set region to customer address form
         *
         * @param {String} value - region
         */
        setDifferedFromDefault: function (value) {
            this._super();

            const indexedOptionsArray = Object.values(this.indexedOptions),
                countryId = this.source.data.country_id,
                hasRegionList = indexedOptionsArray.some(option => option.country_id === countryId);

            // Clear the region field when the country changes
            this.source.set(this.regionScope, '');

            if (hasRegionList) {
                this.source.set(
                    this.regionScope,
                    parseFloat(value) ? this.indexedOptions?.[value]?.label || '' : ''
                );
            }
        }
    });
});
