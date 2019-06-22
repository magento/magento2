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
            regionScope: 'data.general.region'
        },

        /**
         * Set region to customer address form
         *
         * @param {String} value - region
         */
        setDifferedFromDefault: function (value) {
            this._super();

            if (parseFloat(value)) {
                this.source.set(this.regionScope, this.indexedOptions[value].label);
            }
        }
    });
});
