/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/template',
    'jquery/ui',
    'Magento_Bundle/js/price-bundle'
], function ($, mageTemplate) {
    'use strict';

    /**
     * Widget product Summary:
     * Handles rendering of Bundle options and displays them in the Summary box
     */
    $.widget('mage.productSummary', {
        options: {
            mainContainer:          '#product_addtocart_form',
            templates: {
                summaryBlock:       '[data-template="bundle-summary"]',
                optionBlock:        '[data-template="bundle-option"]'
            },
            optionSelector:         '[data-container="options"]',
            summaryContainer:       '[data-container="product-summary"]',
            bundleSummaryContainer: '.bundle-summary'
        },
        cache: {},
        /**
         * Method attaches event observer to the product form
         * @private
         */
        _create: function () {
            this.element
                .closest(this.options.mainContainer)
                .on('updateProductSummary', $.proxy(this._renderSummaryBox, this))
                .priceBundle({})
            ;
        },
        /**
         * Method extracts data from the event and renders Summary box
         * using jQuery template mechanism
         * @param {Event} event
         * @param {Object} data
         * @private
         */
        _renderSummaryBox: function (event, data) {
            this.cache.currentElement = data.config;
            this.cache.currentElementCount = 0;

            // Clear Summary box
            this.element.html('');

            $.each(this.cache.currentElement.selected, $.proxy(this._renderOption, this));
            this.element
                .parents(this.options.bundleSummaryContainer)
                .toggleClass('empty', !this.cache.currentElementCount); // Zero elements equal '.empty' container
        },

        /**
         * @param {String} key
         * @param {String} row
         * @private
         */
        _renderOption: function (key, row) {
            var template;

            if (row && row.length > 0 && row[0] !== null) {
                template = this.element
                    .closest(this.options.summaryContainer)
                    .find(this.options.templates.summaryBlock)
                    .html();
                template = mageTemplate($.trim(template), {
                    data: {
                        _label_: this.cache.currentElement.options[key].title
                    }
                });

                this.cache.currentKey = key;
                this.cache.summaryContainer = $(template);
                this.element.append(this.cache.summaryContainer);

                $.each(row, this._renderOptionRow.bind(this));
                this.cache.currentElementCount += row.length;

                //Reset Cache
                this.cache.currentKey = null;
            }
        },

        /**
         * @param {String} key
         * @param {String} optionIndex
         * @private
         */
        _renderOptionRow: function (key, optionIndex) {
            var template;

            template = this.element
                .closest(this.options.summaryContainer)
                .find(this.options.templates.optionBlock)
                .html();
            template = mageTemplate($.trim(template), {
                data: {
                    _quantity_: this.cache.currentElement.options[this.cache.currentKey].selections[optionIndex].qty,
                    _label_: this.cache.currentElement.options[this.cache.currentKey].selections[optionIndex].name
                }
            });
            this.cache.summaryContainer
                .find(this.options.optionSelector)
                .append(template);
        }
    });

    return $.mage.productSummary;
});
