/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_UI/js/grid/columns/column'
], function (Element) {
    'use strict';

    return Element.extend({
        defaults: {
            bodyTmpl: 'Magento_Weee/price/adjustment',
            dataSource: '${ $.parentName }.provider',
            //Weee configuration constants can be configured from backend
            inclFptWithDesc: 1,//show FPT and description
            inclFpt: 0, //show FPT attribute
            exclFpt: 2, //do not show FPT
            bothFptPrices: 3 //show price without FPT and with FPT and with description
        },

        /**
         * Get Weee attributes.
         *
         * @param {Object} row
         * @return {HTMLElement} Weee html
         */
        getWeeeAttributes: function (row) {
            return row['price_info']['extension_attributes']['weee_attributes'];
        },

        /**
         * Get Weee without Tax attributes.
         *
         * @param {Object} taxAmount
         * @return {HTMLElement} Weee html
         */
        getWeeeTaxWithoutTax: function (taxAmount) {
            return taxAmount['amount_excl_tax'];
        },

        /**
         * Get Weee with Tax attributes.
         *
         * @param {Object} taxAmount
         * @return {HTMLElement} Weee html
         */
        getWeeeTaxWithTax: function (taxAmount) {
            return taxAmount['tax_amount_incl_tax'];
        },

        /**
         * Get Weee Tax name.
         *
         * @param {String} taxAmount
         * @return {String} Weee name
         */
        getWeeTaxAttributeName: function (taxAmount) {
            return taxAmount['attribute_code'];
        },

        /**
         * Set price type.
         *
         * @param {String} priceType
         * @return {Object}
         */
        setPriceType: function (priceType) {
            this.taxPriceType = priceType;

            return this;
        },

        /**
         * Check if Weee Tax must be shown.
         *
         * @param {Object} row
         * @return {Boolean}
         */
        isShown: function (row) {
            return row['price_info']['extension_attributes']['weee_attributes'].length;
        },

        /**
         * Get Weee final price.
         *
         * @param {Object} row
         * @return {HTMLElement} Weee final price html
         */
        getWeeeAdjustment: function (row) {
            return row['price_info']['extension_attributes']['weee_adjustment'];
        },

        /**
         * Return whether display setting is to display price including FPT only.
         *
         * @return {Boolean}
         */
        displayPriceInclFpt: function () {
            return +this.source.data.displayWeee === this.inclFpt;
        },

        /**
         * Return whether display setting is to display
         * price including FPT and FPT description.
         *
         * @return {Boolean}
         */
        displayPriceInclFptDescr: function () {
            return +this.source.data.displayWeee === this.inclFptWithDesc;
        },

        /**
         * Return whether display setting is to display price
         * excluding FPT but including FPT description and final price.
         *
         * @return {Boolean}
         */
        displayPriceExclFptDescr: function () {
            return +this.source.data.displayWeee === this.exclFpt;
        },

        /**
         * Return whether display setting is to display price excluding FPT.
         *
         * @return {Boolean}
         */
        displayPriceExclFpt: function () {
            return +this.source.data.displayWeee === this.bothFptPrices;
        },

        /**
         * Return whether display setting is to display price excluding tax.
         *
         * @return {Boolean}
         */
        displayPriceExclTax: function () {
            return +this.source.data.displayTaxes === this.inclFptWithDesc;
        },

        /**
         * Return whether display setting is to display price including tax.
         *
         * @return {Boolean}
         */
        displayPriceInclTax: function () {
            return +this.source.data.displayTaxes === this.exclFpt;
        },

        /**
         * Return whether display setting is to display
         * both price including tax and price excluding tax.
         *
         * @return {Boolean}
         */
        displayBothPricesTax: function () {
            return +this.source.data.displayTaxes === this.bothFptPrices;
        }
    });
});
