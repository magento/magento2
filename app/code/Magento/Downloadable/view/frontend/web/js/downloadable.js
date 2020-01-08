/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * @api
 */
define([
    'jquery',
    'jquery-ui-modules/widget',
    'Magento_Catalog/js/price-box'
], function ($) {
    'use strict';

    /**
     * Downloadable widget
     */
    $.widget('mage.downloadable', {
        options: {
            priceHolderSelector: '.price-box'
        },

        /**
         *  @inheritdoc
         */
        _create: function () {
            var self = this;

            this.element.find(this.options.linkElement).on('change', $.proxy(function () {
                this._reloadPrice();
            }, this));

            this.element.find(this.options.allElements).on('change', function () {
                if (this.checked) {
                    $('label[for="' + this.id + '"] > span').text($(this).attr('data-checked'));
                    self.element.find(self.options.linkElement + ':not(:checked)').each(function () {
                        $(this).trigger('click');
                    });
                } else {
                    $('[for="' + this.id + '"] > span').text($(this).attr('data-notchecked'));
                    self.element.find(self.options.linkElement + ':checked').each(function () {
                        $(this).trigger('click');
                    });
                }
            });

            this._reloadPrice();
        },

        /**
         * Reload product price with selected link price included
         * @private
         */
        _reloadPrice: function () {
            var finalPrice = 0,
                basePrice = 0;

            this.element.find(this.options.linkElement + ':checked').each($.proxy(function (index, element) {
                finalPrice += this.options.config.links[$(element).val()].finalPrice;
                basePrice += this.options.config.links[$(element).val()].basePrice;
            }, this));

            $(this.options.priceHolderSelector).trigger('updatePrice', {
                'prices': {
                    'finalPrice': {
                        'amount': finalPrice
                    },
                    'basePrice': {
                        'amount': basePrice
                    }
                }
            });

            this.reloadAllCheckText();
        },

        /**
         * Reload all-elements-checkbox's label
         * @private
         */
        reloadAllCheckText: function () {
            var allChecked = true,
                allElementsCheck = $(this.options.allElements),
                allElementsLabel = $('label[for="' + allElementsCheck.attr('id') + '"] > span');

            $(this.options.linkElement).each(function () {
                if (!this.checked) {
                    allChecked = false;
                }
            });

            if (allChecked) {
                allElementsLabel.text(allElementsCheck.attr('data-checked'));
                allElementsCheck.prop('checked', true);
            } else {
                allElementsLabel.text(allElementsCheck.attr('data-notchecked'));
                allElementsCheck.prop('checked', false);
            }
        }
    });

    return $.mage.downloadable;
});
