/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
