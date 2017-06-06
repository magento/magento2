/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'underscore',
    'uiCollection'
], function (ko, _, Collection) {
    'use strict';

    return Collection.extend({
        /**
         * Find from all price ui components, price with specific code, init source on it and set priceType
         *
         * @param {String} code
         * @returns {*|T}
         */
        getPriceByCode: function (code) {
            var elems = this.elems() ? this.elems() : ko.getObservable(this, 'elems'),
                price;

            price = _.filter(elems, function (elem) {
                return elem.index === code;
            }, this).pop();

            price.source = this.source();
            price.priceType = code;

            return price;
        },

        /**
         * Retrieve body template
         *
         * @returns {String}
         */
        getBody: function () {
            return this.bodyTmpl;
        },

        /**
         * Check whether price has price range, depends on different options, that can be choose
         *
         * @param {Object} row
         * @returns {Boolean}
         */
        hasPriceRange: function (row) {
            return row['price_info']['max_regular_price'] !== row['price_info']['min_regular_price'];
        }
    });
});
