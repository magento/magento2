/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*jshint loopfunc: true */
define([
    "jquery",
    "jquery/ui",
    "jquery/template",
    "Magento_Catalog/js/price-option"
], function($){
    "use strict";

    $.widget('mage.bundleOption', {
        options: {
            productBundleSelector: '.product.bundle.option',
            mapPopupPrice: '#map-popup-price',
            prices: {},
            priceTemplate: '<span class="price">${formattedPrice}</span>'
        },

        _init: function() {
            if ("optionConfig" in this.options && "bundleConfig" in this.options) {
                if ("defaultValues" in this.options.bundleConfig) {
                    for (var key in this.options.bundleConfig.defaultValues) {
                        if (this.options.optionConfig.options[key].isMulti) {
                            var selected = [];
                            $(this.options.bundleConfig.defaultValues[key]).each($.proxy(function(k, e) {
                                selected.push(this.options.bundleConfig.defaultValues[key][k]);
                            }, this));
                            this.options.bundleConfig.selected[key] = selected;
                        } else {
                            var value = this.options.bundleConfig.defaultValues[key],
                                qty = $('#bundle-option-' + key + '-qty-input').val();
                            this.options.bundleConfig.options[key].selections[value].qty = parseInt(qty, 10);
                            this.options.optionConfig.options[key].selections[value].qty = parseInt(qty, 10);
                            this.options.bundleConfig.selected[key] = [value];
                        }
                    }
                }

                // Trigger Event to update Summary box
                this.reloadPrice();
                this.element.trigger('updateProductSummary', [
                    {config: this.options.bundleConfig}
                ]);
            }
        },
        _create: function() {
            this.element.on('reloadPrice', $.proxy(function() {
                this.reloadPrice();
            }, this));
            $(this.options.productBundleSelector).each(
                $.proxy(function(key, value) {
                    var element = $(value),
                        inputs = element.filter(":input"),
                        isNotCheckboxRadio = inputs.is(':not(":checkbox, :radio")');
                    element.on((isNotCheckboxRadio ? 'change' : 'click'), $.proxy(this.changeSelection, this));

                    var _this = this;
                    element.each(function() {
                        var _elem = $(this),
                            _elements;
                        if (_elem.is(':not(":checkbox, select[multiple]")')) {
                            if (_elem.closest('dd').find('input.qty').length) {
                                _elements = _elem.closest('dd').find('input.qty');
                            } else if (_elem.parentsUntil('nested.options-list').find('input.qty').length) {
                                _elements = _elem.parentsUntil('nested.options-list').find('input.qty');
                            } else {
                                _elements = {};
                            }
                            _elements.each(function() {
                                var _qty = $(this);
                                _qty.on('blur', $.proxy(function() {
                                    var parts = _qty.attr('id').split('-'),
                                        value = _elem.val(),
                                        quantity = parseInt(_qty.val(), 10);
                                    if (quantity > 0 &&
                                        _this.options.bundleConfig.options[parts[2]] &&
                                        _this.options.bundleConfig.options[parts[2]].selections[value] &&
                                        _this.options.optionConfig.options[parts[2]].selections[_elem.val()] &&
                                        _this.options.optionConfig.options[parts[2]]
                                    ) {
                                        _this.options.bundleConfig.options[parts[2]].selections[value].qty = parseInt(quantity, 10);
                                        _this.options.optionConfig.options[parts[2]].selections[_elem.val()].qty = parseInt(quantity, 10);
                                    }
                                    _this.reloadPrice();
                                    _this.element.trigger('updateProductSummary', [
                                        {config: _this.options.bundleConfig}
                                    ]);
                                }, this));
                            });
                        }
                    });
                }, this)
            );
        },

        _formatCurrency: function(price, format, showPlus) {
            var precision = isNaN(format.requiredPrecision = Math.abs(format.requiredPrecision)) ? 2 : format.requiredPrecision,
                integerRequired = isNaN(format.integerRequired = Math.abs(format.integerRequired)) ? 1 : format.integerRequired,
                decimalSymbol = format.decimalSymbol === undefined ? "," : format.decimalSymbol,
                groupSymbol = format.groupSymbol === undefined ? "." : format.groupSymbol,
                groupLength = format.groupLength === undefined ? 3 : format.groupLength,
                s = '';

            if (showPlus === undefined || showPlus === true) {
                s = price < 0 ? "-" : ( showPlus ? "+" : "");
            } else if (showPlus === false) {
                s = '';
            }
            var i = parseInt(price = Math.abs(+price || 0).toFixed(precision), 10) + '',
                pad = (i.length < integerRequired) ? (integerRequired - i.length) : 0;
            while (pad) {
                i = '0' + i;
                pad--;
            }
            var j = i.length > groupLength ? i.length % groupLength : 0,
                re = new RegExp("(\\d{" + groupLength + "})(?=\\d)", "g");

            /**
             * replace(/-/, 0) is only for fixing Safari bug which appears
             * when Math.abs(0).toFixed() executed on "0" number.
             * Result is "0.-0" :(
             */
            var r = (j ? i.substr(0, j) + groupSymbol : "") + i.substr(j).replace(re, "$1" + groupSymbol) +
                    (precision ? decimalSymbol + Math.abs(price - i).toFixed(precision).replace(/-/, 0).slice(2) : ""),
                pattern = format.pattern.indexOf('{sign}') < 0 ? s + format.pattern : format.pattern.replace('{sign}', s);
            return pattern.replace('%s', r).replace(/^\s\s*/, '').replace(/\s\s*$/, '');
        },

        changeSelection: function(e) {
            var element = $(e.target),
                parts = element.attr('id').split('-'),
                config = this.options.bundleConfig;
            if (config.options[parts[2]].isMulti) {
                var selected = [];
                if (element.is('select')) {
                    selected.push(element.val());
                    config.selected[parts[2]] = selected;
                } else if (element.is('input')) {
                    if (element.is(":checkbox")) {
                        if (element.is(":checked")) {
                            selected.push(element.val());
                            if (parts[2] in config.selected) {
                                config.selected[parts[2]].push(selected);
                            } else {
                                config.selected[parts[2]] = [selected];
                            }
                        } else {
                            config.selected[parts[2]] = $.grep(config.selected[parts[2]], function(e) {
                                return e[0] != element.val();
                            });
                        }
                    }
                }
            } else {
                if (element.val() !== '') {
                    config.selected[parts[2]] = [element.val()];
                } else {
                    config.selected[parts[2]] = [];
                }
                this.populateQty(parts[2], element.val());
            }
            // Trigger Event to update Summary box
            this.reloadPrice();
            this.element.trigger('updateProductSummary', [
                {config: this.options.bundleConfig}
            ]);
        },

        reloadPrice: function() {
            if (this.options.bundleConfig) {
                var optionPrice = {
                    exclTaxPrice: this.options.bundleConfig.finalBasePriceExclTax,
                    inclTaxPrice: this.options.bundleConfig.finalBasePriceInclTax,
                    price: this.options.bundleConfig.finalPrice,
                    update: function(price, exclTaxPrice, inclTaxPrice) {
                        this.price += price;
                        this.exclTaxPrice += exclTaxPrice;
                        this.inclTaxPrice += inclTaxPrice;
                    }
                };
                $.each(this.options.bundleConfig.selected, $.proxy(function(index, value) {
                    if (value !== undefined && this.options.bundleConfig.options[index]) {
                        $.each(value, $.proxy(function(key, element) {
                            if (element !== '' && element !== 'none' && element !== undefined && element !== null) {
                                if ($.isArray(element)) {
                                    $.each(element, $.proxy(function(k, e) {
                                        var prices = this.selectionPrice(index, e);
                                        optionPrice.update(
                                            prices[0],
                                            prices[1],
                                            prices[2]);
                                    }, this));
                                } else {
                                    var prices = this.selectionPrice(index, element);
                                    optionPrice.update(
                                        prices[0],
                                        prices[1],
                                        prices[2]);
                                }
                            }
                        }, this));
                    }
                }, this));
                // Loop through each priceSelector and update price
                $.each(this.options.priceSelectors, $.proxy(function(index, value) {
                    var priceElement = $(value),
                        clone = $(value + "_clone"),
                        isClone = false;
                    if (priceElement.length === 0) {
                        priceElement = clone;
                        isClone = true;
                    }
                    if (priceElement.length === 1) {
                        var price = 0;
                        if (value.indexOf('price-including-tax-') >= 0) {
                            price = optionPrice.inclTaxPrice;
                        } else if (value.indexOf('price-excluding-tax-') >= 0) {
                            price = optionPrice.exclTaxPrice;
                        } else if (value.indexOf('product-price-') >= 0) {
                            price = optionPrice.price;
                        }

                        var priceHtml = $.tmpl(this.options.priceTemplate,
                            {'formattedPrice': this._formatCurrency(price, this.options.bundleConfig.priceFormat)}
                        );
                        priceElement.html(priceHtml[0].outerHTML);
                        // If clone exists, update clone price as well
                        if (!isClone && clone.length === 1) {
                            clone.html(priceHtml[0].outerHTML);
                        }
                        $(this.options.mapPopupPrice).find(value).html(priceHtml);
                    }
                }, this));
            }
        },

        selectionPrice: function(optionId, selectionId) {
            //Those constants need to be in sync with Magento\Bundle\Block\Catalog\Product\View\Type\Bundle
            var TOTAL_ROUNDING = 2;
            var ROW_ROUNDING = 1;
            var UNIT_ROUNDING = 0;
            var qty = null,
                config = this.options.bundleConfig,
                configOption = config.options[optionId];
            if (configOption.selections[selectionId].customQty === 1 && !configOption.isMulti) {
                if ($(this.options.bundleOptionQtyPrefix + optionId + this.options.bundleOptionQtySuffix)) {
                    qty = $(this.options.bundleOptionQtyPrefix + optionId + this.options.bundleOptionQtySuffix).val();
                } else {
                    qty = 1;
                }
            } else {
                qty = configOption.selections[selectionId].qty;
            }

            var selection = configOption.selections[selectionId];

            var price = selection.price;
            var inclTaxPrice = selection.inclTaxPrice;
            var exclTaxPrice = selection.exclTaxPrice;
            var tierPrice = selection.tierPrice;

            if (config.priceType === '0') {
                $.each(tierPrice, function(k, e) {
                    if (e.price_qty <= qty && e.price <= price) {
                        price = e.price;
                        inclTaxPrice = e.inclTaxPrice;
                        exclTaxPrice = e.exclTaxPrice;
                    }
                });
            }

            if (this.options.bundleConfig.isFixedPrice || this.options.roundingMethod == TOTAL_ROUNDING) {
                return [price * qty, exclTaxPrice * qty, inclTaxPrice * qty];
            } else if (this.options.roundingMethod == UNIT_ROUNDING) {
                price = Math.round(price * 100) / 100;
                exclTaxPrice = Math.round(exclTaxPrice * 100) / 100;
                inclTaxPrice = Math.round(inclTaxPrice * 100) / 100;
                return [price * qty, exclTaxPrice * qty, inclTaxPrice * qty];
            } else {
                var rowTotal = Math.round(price * qty * 100) /100;
                var rowTotalExclTax = Math.round(exclTaxPrice * qty * 100) /100;
                var rowTotalInclTax = Math.round(inclTaxPrice * qty * 100) /100;
                return [rowTotal, rowTotalExclTax, rowTotalInclTax];
            }
        },

        populateQty: function(optionId, selectionId) {
            if (selectionId === '' || selectionId === 'none' || selectionId === undefined) {
                this.showQtyInput(optionId, '0', false);
            } else if (this.options.optionConfig.options[optionId].selections[selectionId].customQty === '1') {
                this.showQtyInput(optionId, this.options.optionConfig.options[optionId].selections[selectionId].qty, true);
            } else {
                this.showQtyInput(optionId, this.options.optionConfig.options[optionId].selections[selectionId].qty, false);
            }
            this.reloadPrice();
            this.element.trigger('updateProductSummary', [
                {config: this.options.bundleConfig}
            ]);
        },

        showQtyInput: function(optionId, value, canEdit) {
            var element = $('#bundle-option-' + optionId + '-qty-input');
            element.val(value).attr('disabled', !canEdit);
            if (canEdit) {
                element.removeClass('qty-disabled');
            } else {
                element.addClass('qty-disabled');
            }
        }
    });

    return $.mage.bundleOption;
});