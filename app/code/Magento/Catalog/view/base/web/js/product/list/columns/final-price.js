/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'uiRegistry',
    'mageUtils',
    'uiCollection'
], function (_, registry, utils, Collection) {
    'use strict';

    return Collection.extend({
        defaults: {
            label: false,
            headerTmpl: 'ui/grid/columns/text',
            showMinimalPrice: false,
            showMaximumPrice: false,
            useLinkForAsLowAs: false,
            bodyTmpl: 'Magento_Catalog/product/final_price',
            priceWrapperCssClasses: '',
            priceWrapperAttr: {}
        },

        /**
         * Get product final price.
         *
         * @param {Object} row
         * @return {HTMLElement} final price html
         */
        getPrice: function (row) {
            return row['price_info']['formatted_prices']['final_price'];
        },

        /**
         * UnsanitizedHtml version of getPrice.
         *
         * @param {Object} row
         * @return {HTMLElement} final price html
         */
        getPriceUnsanitizedHtml: function (row) {
            return this.getPrice(row);
        },

        /**
         * Get product regular price.
         *
         * @param {Object} row
         * @return {HTMLElement} regular price html
         */
        getRegularPrice: function (row) {
            return row['price_info']['formatted_prices']['regular_price'];
        },

        /**
         * UnsanitizedHtml version of getRegularPrice.
         *
         * @param {Object} row
         * @return {HTMLElement} regular price html
         */
        getRegularPriceUnsanitizedHtml: function (row) {
            return this.getRegularPrice(row);
        },

        /**
         * Check if product has a price range.
         *
         * @param {Object} row
         * @return {Boolean}
         */
        hasPriceRange: function (row) {
            return row['price_info']['max_regular_price'] !== row['price_info']['min_regular_price'];
        },

        /**
         * Check if product has special price.
         *
         * @param {Object} row
         * @return {HTMLElement} special price html
         */
        hasSpecialPrice: function (row) {
            return row['price_info']['regular_price'] > row['price_info']['final_price'];
        },

        /**
         * Check if product has minimal price.
         *
         * @param {Object} row
         * @return {HTMLElement} minimal price html
         */
        isMinimalPrice: function (row) {
            return row['price_info']['minimal_price'] < row['price_info']['final_price'];
        },

        /**
         * Get product minimal price.
         *
         * @param {Object} row
         * @return {HTMLElement} minimal price html
         */
        getMinimalPrice: function (row) {
            return row['price_info']['formatted_prices']['minimal_price'];
        },

        /**
         * UnsanitizedHtml version of getMinimalPrice.
         *
         * @param {Object} row
         * @return {HTMLElement} minimal price html
         */
        getMinimalPriceUnsanitizedHtml: function (row) {
            return this.getMinimalPrice(row);
        },

        /**
         * Check if product is salable.
         *
         * @param {Object} row
         * @return {Boolean}
         */
        isSalable: function (row) {
            return row['is_salable'];
        },

        /**
         * Get product maximum price.
         *
         * @param {Object} row
         * @return {HTMLElement} maximum price html
         */
        getMaxPrice: function (row) {
            return row['price_info']['formatted_prices']['max_price'];
        },

        /**
         * UnsanitizedHtml version of getMaxPrice.
         *
         * @param {Object} row
         * @return {HTMLElement} maximum price html
         */
        getMaxPriceUnsanitizedHtml: function (row) {
            return this.getMaxPrice(row);
        },

        /**
         * Get product maximum regular price in case of price range and special price.
         *
         * @param {Object} row
         * @return {HTMLElement} maximum regular price html
         */
        getMaxRegularPrice: function (row) {
            return row['price_info']['formatted_prices']['max_regular_price'];
        },

        /**
         * UnsanitizedHtml version of getMaxRegularPrice.
         *
         * @param {Object} row
         * @return {HTMLElement} maximum regular price html
         */
        getMaxRegularPriceUnsanitizedHtml: function (row) {
            return this.getMaxRegularPrice(row);
        },

        /**
         * Get product minimal regular price in case of price range and special price.
         *
         * @param {Object} row
         * @return {HTMLElement} minimal regular price html
         */
        getMinRegularPrice: function (row) {
            return row['price_info']['formatted_prices']['min_regular_price'];
        },

        /**
         * UnsanitizedHtml version of getMinRegularPrice.
         *
         * @param {Object} row
         * @return {HTMLElement} minimal regular price html
         */
        getMinRegularPriceUnsanitizedHtml: function (row) {
            return this.getMinRegularPrice(row);
        },

        /**
         * Get adjustments names and return as string.
         *
         * @return {String} adjustments classes
         */
        getAdjustmentCssClasses: function () {
            return _.pluck(this.getAdjustments(), 'index').join(' ');
        },

        /**
         * Get product minimal price as number.
         *
         * @param {Object} row
         * @return {Number} minimal price amount
         */
        getMinimalPriceAmount: function (row) {
            return row['price_info']['minimal_price'];
        },

        /**
         * UnsanitizedHtml version of getMinimalPriceAmount
         *
         * @param {Object} row
         * @return {Number} minimal price amount
         */
        getMinimalPriceAmountUnsanitizedHtml: function (row) {
            return this.getMinimalPriceAmount(row);
        },

        /**
         * Get product minimal regular price as number in case of special price.
         *
         * @param {Object} row
         * @return {Number} minimal regular price amount
         */
        getMinimalRegularPriceAmount: function (row) {
            return row['price_info']['min_regular_price'];
        },

        /**
         * Get product maximum price as number.
         *
         * @param {Object} row
         * @return {Number} maximum price amount
         */
        getMaximumPriceAmount: function (row) {
            return row['price_info']['max_price'];
        },

        /**
         * Get product maximum regular price as number in case of special price.
         *
         * @param {Object} row
         * @return {Number} maximum regular price amount
         */
        getMaximumRegularPriceAmount: function (row) {
            return row['price_info']['max_regular_price'];
        },

        /**
         * Check if minimal regular price exist for product.
         *
         * @param {Object} row
         * @return {Boolean}
         */
        showMinRegularPrice: function (row) {
            return this.getMinimalPriceAmount(row) < this.getMinimalRegularPriceAmount(row);
        },

        /**
         * Check if maximum regular price exist for product.
         *
         * @param {Object} row
         * @return {Boolean}
         */
        showMaxRegularPrice: function (row) {
            return this.getMaximumPriceAmount(row) < this.getMaximumRegularPriceAmount(row);
        },

        /**
         * Get path to the columns' body template.
         *
         * @returns {String}
         */
        getBody: function () {
            return this.bodyTmpl;
        },

        /**
         * Get all price adjustments.
         *
         * @returns {Object}
         */
        getAdjustments: function () {
            var adjustments = this.elems();

            _.each(adjustments, function (adjustment) {
                adjustment.setPriceType(this.priceType);
                adjustment.source = this.source;
            }, this);

            return adjustments;
        }
    });
});
