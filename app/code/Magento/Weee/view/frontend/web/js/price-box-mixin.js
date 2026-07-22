/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
define([
    'jquery',
    'underscore',
    'mage/template',
    'Magento_Catalog/js/price-utils'
], function ($, _, mageTemplate, priceUtils) {
    'use strict';

    return function (priceBox) {
        $.widget('mage.priceBox', priceBox, {

            options: {
                weeeTemplate: '<span class="weee" data-price-type="weee" data-label="<%- data.label %>">' +
                    '<span class="price"><%- data.formatted %></span>' +
                    '</span>',
                weeeFinalPriceTemplate: '<span class="price-final price-final_price" data-price-type="weeePrice">' +
                    '<span class="price"><%- data.formatted %></span>' +
                    '</span>'
            },

            /**
             * Override reloadPrice to add WEEE breakdown
             */
            reloadPrice: function reDrawPrices() {
                var priceFormat = this.options.priceConfig && this.options.priceConfig.priceFormat || {},
                    priceTemplate = mageTemplate(this.options.priceTemplate);

                // First, render prices normally
                _.each(this.cache.displayPrices, function (price, priceCode) {
                    price.final = _.reduce(price.adjustments, function (memo, amount) {
                        return memo + amount;
                    }, price.amount);

                    price.formatted = priceUtils.formatPrice(price.final, priceFormat);

                    $('[data-price-type="' + priceCode + '"]', this.element).html(priceTemplate({
                        data: price
                    }));
                }, this);

                // Then, add WEEE breakdown if available
                this._addWeeeBreakdown();
            },

            /**
             * Add WEEE breakdown to price display
             */
            _addWeeeBreakdown: function () {
                var productId = this._getSelectedProductId(),
                    weeeData,
                    priceContainer,
                    weeeTemplate,
                    weeeFinalTemplate,
                    weeeHtml = '';

                if (!productId) {
                    return;
                }

                weeeData = this._getWeeeData(productId);

                if (!weeeData || !weeeData.weeeAttributes || weeeData.weeeAttributes.length === 0) {
                    return;
                }

                // Get templates
                weeeTemplate = mageTemplate(this.options.weeeTemplate);
                weeeFinalTemplate = mageTemplate(this.options.weeeFinalPriceTemplate);

                // Find the price container
                priceContainer = this.element.find('[data-price-type="finalPrice"]').parent();

                // Remove old WEEE elements
                priceContainer.find('.weee, .price-final').remove();

                // Update the main price to show base price without WEEE
                this.element.find('[data-price-type="finalPrice"]').html(
                    '<span class="price">' + weeeData.formattedWithoutWeee + '</span>'
                );

                // Build WEEE HTML using template
                _.each(weeeData.weeeAttributes, function (weee) {
                    weeeHtml += weeeTemplate({
                        data: {
                            label: weee.name,
                            formatted: weee.formatted
                        }
                    });
                });

                // Add final price (with WEEE) using template
                weeeHtml += weeeFinalTemplate({
                    data: {
                        formatted: weeeData.formattedWithWeee
                    }
                });

                // Append to container
                priceContainer.append(weeeHtml);
            },

            /**
             * Get selected product ID from configurable/swatch widget
             */
            _getSelectedProductId: function () {
                var swatchWidget = this._getSwatchWidget(),
                    configurableWidget;

                // Try to get from swatch widget (product detail page)
                if (swatchWidget && swatchWidget.getProduct) {
                    return swatchWidget.getProduct();
                }

                // Try to get from configurable widget (product detail page)
                configurableWidget = this._getConfigurableWidget();
                if (configurableWidget && configurableWidget.simpleProduct) {
                    return configurableWidget.simpleProduct;
                }

                return null;
            },

            /**
             * Get WEEE data from jsonConfig
             */
            _getWeeeData: function (productId) {
                var swatchWidget = this._getSwatchWidget(),
                    configurableWidget = this._getConfigurableWidget(),
                    jsonConfig = swatchWidget && swatchWidget.options.jsonConfig ||
                        configurableWidget && configurableWidget.options.spConfig,
                    optionPrices;

                if (!jsonConfig) {
                    return null;
                }

                optionPrices = jsonConfig.optionPrices;
                if (optionPrices && optionPrices[productId] && optionPrices[productId].finalPrice) {
                    return optionPrices[productId].finalPrice;
                }

                return null;
            },

            /**
             * Find the swatch widget relative to this price-box
             */
            _getSwatchWidget: function () {
                var $productItem = this.element.closest('.product-item, .product-item-info'),
                    $swatchOptions,
                    widget;

                // On listing pages, find swatch widget in the same product item
                if ($productItem.length) {
                    // On listing pages, swatch renderer uses data-role="swatch-option-{productId}"
                    $swatchOptions = $productItem.find('[data-role^="swatch-option-"]');

                    if ($swatchOptions.length) {
                        widget = $swatchOptions.data('mage-SwatchRenderer') ||
                            $swatchOptions.data('mageSwatchRenderer');
                    }

                    if (widget) {
                        return widget;
                    }

                    // Try product detail page selector
                    $swatchOptions = $productItem.find('[data-role="swatch-options"]');

                    if ($swatchOptions.length) {
                        return $swatchOptions.data('mage-SwatchRenderer');
                    }
                }

                // On product detail page, use global selector
                $swatchOptions = $('[data-role="swatch-options"]');

                if ($swatchOptions.length) {
                    return $swatchOptions.data('mage-SwatchRenderer');
                }

                return null;
            },

            /**
             * Find the configurable widget relative to this price-box
             */
            _getConfigurableWidget: function () {
                var $productItem = this.element.closest('.product-item, .product-item-info'),
                    $form;

                // On listing pages, find form in the same product item
                if ($productItem.length) {
                    $form = $productItem.find('form');
                    if ($form.length) {
                        return $form.data('mageConfigurable') || $form.data('configurable');
                    }
                }

                // On product detail page, use global form selector
                $form = $('#product_addtocart_form');
                if ($form.length) {
                    return $form.data('mageConfigurable') || $form.data('configurable');
                }

                return null;
            }
        });

        return $.mage.priceBox;
    };
});
