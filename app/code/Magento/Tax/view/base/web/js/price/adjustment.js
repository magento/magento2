/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_UI/js/grid/columns/column',
    'mage/translate'
], function (Element, $t) {
    'use strict';

    return Element.extend({
        defaults: {
            bodyTmpl: 'Magento_Tax/price/adjustment',
            taxPriceType: 'final_price',
            taxPriceCssClass: 'price-including-tax',
            bothPrices: 3,
            inclTax: 2,
            exclTax: 1,
            modules: {
                price: '${ $.parentName }'
            }
        },

        /**
         * {@inheritdoc}
         */
        initialize: function () {
            this._super()
                .setPriceAttributes();

            return this;
        },

        /**
         * Update parent price.
         *
         * @returns {Object} Chainable.
         */
        setPriceAttributes: function () {
            if (this.displayBothPrices) {
                this.price().priceWrapperCssClasses = this.taxPriceCssClass;
                this.price().priceWrapperAttr = {
                    'data-label': $t('Incl. Tax')
                };
            }

            return this;
        },

        /**
         * Get price tax adjustment.
         *
         * @param {Object} row
         * @return {HTMLElement} tax html
         */
        getTax: function (row) {
            return row['price_info']['extension_attributes']['tax_adjustments']['formatted_prices'][this.taxPriceType];
        },

        /**
         * Set price taax type.
         *
         * @param {String} priceType
         * @return {Object}
         */
        setPriceType: function (priceType) {
            this.taxPriceType = priceType;

            return this;
        },

        /**
         * Return whether display setting is to display
         * both price including tax and price excluding tax.
         *
         * @return {Boolean}
         */
        displayBothPrices: function () {
            return +this.source.data.displayTaxes === this.bothPrices;
        },

        /**
         * Return whether display setting is to display price including tax.
         *
         * @return {Boolean}
         */
        displayPriceIncludeTax: function () {
            return +this.source.data.displayTaxes === this.inclTax;
        },

        /**
         * Return whether display setting is to display price excluding tax.
         *
         * @return {Boolean}
         */
        displayPriceExclTax: function () {
            return +this.source.data.displayTaxes === this.exclTax;
        }
    });
});
