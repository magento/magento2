/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true expr:true*/
define([
    "jquery",
    "jquery/ui",
    "jquery/template",
    "Magento_Bundle/js/price-bundle"
], function($){
    "use strict";

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
        _create: function() {
            this.element
                .closest(this.options.mainContainer)
                .on('updateProductSummary', $.proxy(this._renderSummaryBox, this))
                .priceBundle({})
            ;
        },
        /**
         * Method extracts data from the event and renders Summary box
         * using jQuery template mechanism
         * @param event
         * @param data
         * @private
         */
        _renderSummaryBox: function(event, data) {
            this.cache.currentElement = data.config;
            this.cache.currentElementCount = 0;

            // Clear Summary box
            this.element.html("");

            $.each(this.cache.currentElement.selected, $.proxy(this._renderOption, this));
            this.element
                .parents(this.options.bundleSummaryContainer)
                .toggleClass('empty', !this.cache.currentElementCount); // Zero elements equal '.empty' container
        },
        _renderOption: function(key, row) {
            if (row !== undefined) {
                if (row.length > 0 && row[0] !== null) {
                    this.cache.currentKey = key;
                    this.cache.summaryContainer = this.element
                        .closest(this.options.summaryContainer)
                        .find(this.options.templates.summaryBlock)
                        .tmpl([{_label_: this.cache.currentElement.options[this.cache.currentKey].title}])
                        .appendTo(this.element);

                    $.each(row, $.proxy(this._renderOptionRow, this));
                    this.cache.currentElementCount += row.length;

                    //Reset Cache
                    this.cache.currentKey = null;
                }
            }
        },
        _renderOptionRow: function(key, option) {
            this.cache.currentOptions = [];
            if (!$.isArray(option)) {   // Regular options (single)
                this.cache.currentOptions.push({
                    _quantity_: this.cache.currentElement.options[this.cache.currentKey].selections[option].qty,
                    _label_: this.cache.currentElement.options[this.cache.currentKey].selections[option].name
                });
            } else {    // Used for Multi-select
                $.each(option, $.proxy(this._pushOptionRow, this));
            }
            this.element
                .closest(this.options.summaryContainer)
                .find(this.options.templates.optionBlock)
                .tmpl(this.cache.currentOptions)
                .appendTo(this.cache.summaryContainer.find(this.options.optionSelector));

            // Reset cache
            this.cache.currentOptions = [];
        },
        _pushOptionRow: function(index, value) {
            this.cache.currentOptions.push({
                _quantity_: this.cache.currentElement.options[this.cache.currentKey].selections[value].qty,
                _label_: this.cache.currentElement.options[this.cache.currentKey].selections[value].name
            });
        }
    });

    return $.mage.productSummary;
});
