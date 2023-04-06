/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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

            this.source.set(
                this.regionScope,
                hasRegionList
                    ? parseFloat(value) ? this.indexedOptions?.[value]?.label || '' : ''
                    : this.source.data?.region || ''
            );
        }
    });
});
