/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    design
 * @package     base_default
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
(function($, undefined) {
    "use strict";
    $.widget('mage.bundleOption', {
        options: {
            productBundleSelector: '.product-bundle-option',
            mapPopupPrice: '#map-popup-price',
            prices: {},
            priceTemplate: '<span class="price">${formattedPrice}</span>'
        },

        _create: function() {
            $(this.options.productBundleSelector).each(
                $.proxy(function(key, value) {
                    var element = $(value),
                        inputs = element.filter(":input"),
                        isNotCheckboxRadio = inputs.is(':not(":checkbox, :radio")');
                    element.on((isNotCheckboxRadio ? 'change' : 'click'), $.proxy(this.changeSelection, this));
                }, this)
            );
            $(this.options.bundleConfig.defaultValues).each(function(key, ele) {
                if (this.options.optionConfig.options[ele].isMulti) {
                    var selected = [];
                    $(this.options.bundleConfig.defaultValues[ele]).each(function(k, e) {
                        selected.push(this.options.bundleConfig.defaultValues[e]);
                    });
                    var parts = ele.attr('id').split('-');
                    this.options.bundleConfig.selected[parts[2]] = selected;
                } else {
                    this.options.bundleConfig.selected[ele] = [this.options.bundleConfig.defaultValues[ele]];
                }
            });
            this.reloadPrice();
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
                    if (element.is(":radio:checked")) {
                            selected.push(element.val());
                            config.selected[parts[2]] = selected;
                    }
                    if (element.is(":checkbox")) {
                        if (element.is(":checked")) {
                            config.selected[parts[2]].push(element.val());
                        } else {
                            config.selected[parts[2]].splice($.inArray(element.val(), config.selected[parts[2]]), 1);
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
            this.reloadPrice();
        },

        reloadPrice: function() {
            if (this.options.bundleConfig) {
                var optionPrice = {
                        excludeTax: 0,
                        includeTax: 0,
                        price: 0,
                        update: function(price, excludeTax, includeTax) {
                            this.price += price;
                            this.excludeTax += excludeTax;
                            this.includeTax += includeTax;
                        }
                    };
                $.each(this.options.bundleConfig.selected, $.proxy(function(index, value) {
                    if (this.options.bundleConfig.options[index]) {
                        $.each(value, $.proxy(function(key, element) {
                            if (element !== '' && element !== 'none' && element !== undefined && element !== null) {
                                if ($.isArray(element)) {
                                    $.each(element, $.proxy(function(k, e) {
                                        var prices = this.selectionPrice(index, e);
                                        optionPrice.update(prices[0], prices[1], prices[2]);
                                    }, this));
                                } else {
                                    var prices = this.selectionPrice(index, element);
                                    optionPrice.update(prices[0], prices[1], prices[2]);
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
                            price = optionPrice.priceInclTax;
                        } else if (value.indexOf('price-excluding-tax-') >= 0) {
                            price = optionPrice.priceExclTax;
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

            var price, tierPrice,
                selection = configOption.selections[selectionId];
            if (config.priceType === '0') {
                price = configOption.selections[selectionId].price;
                tierPrice = configOption.selections[selectionId].tierPrice;
                $.each(tierPrice, function(k, e) {
                    if (e.price_qty <= qty && e.price <= price) {
                        price = e.price;
                    }
                });
            } else {
                if (selection.priceType === '0') {
                    price = selection.priceValue;
                } else {
                    price = (config.basePrice * selection.priceValue) / 100;
                }
            }

            var disposition = configOption.selections[selectionId].plusDisposition +
                configOption.selections[selectionId].minusDisposition;
            if (config.specialPrice) {
                price = Math.min((Math.round(price * config.specialPrice) / 100), price);
            }

            var priceInclTax;
            if (selection.priceInclTax !== undefined) {
                priceInclTax = selection.priceInclTax;
                price = selection.priceExclTax !== undefined ? selection.priceExclTax : selection.price;
            } else {
                priceInclTax = price;
            }

            return [price * qty, disposition * qty, priceInclTax * qty];
        },

        populateQty: function(optionId, selectionId) {
            if (selectionId === '' || selectionId === 'none' || selectionId === undefined) {
                this.showQtyInput(optionId, '0', false);
            } else if (this.options.optionConfig.options[optionId].selections[selectionId].customQty === '1') {
                this.showQtyInput(optionId, this.options.optionConfig.options[optionId].selections[selectionId].qty, true);
            } else {
                this.showQtyInput(optionId, this.options.optionConfig.options[optionId].selections[selectionId].qty, false);
            }
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
})(jQuery);
