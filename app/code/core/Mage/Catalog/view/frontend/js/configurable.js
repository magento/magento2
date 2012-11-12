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
 * @category    frontend configurable product price option
 * @package     mage
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint evil:true browser:true jquery:true*/

(function ($, undefined) {
    $.widget('mage.configurable', {
        options: {
            state: {}
        },
        _create: function () {
            this.options.taxConfig = this.options.spConfig.taxConfig;
            if (this.options.containerId) {
                this.options.setings = $('#' + this.options.spConfig.containerId + ' ' + '.super-attribute-select');
            } else {
                this.options.setings = $('.super-attribute-select');
            }
            // Overwrite defaults by url
            if (this.options.spConfig.defaultValues) {
                this.options.values = this.options.spConfig.defaultValues;
            }
            var separatorIndex = window.location.href.indexOf('#');
            if (separatorIndex !== -1) {
                var paramsStr = window.location.href.substr(separatorIndex + 1);
                var urlValues = paramsStr.toQueryParams();
                if (!this.options.spConfig.defaultValues) {
                    this.options.values = {};
                }
                for (var i = 0; i < urlValues.length; i++) {
                    this.options.values[i] = urlValues[i];
                }
            }
            // Overwrite defaults by inputs values if needed
            if (this.options.spConfig.inputsInitialized) {
                this.options.values = {};
                $.each(this.options.setings, $.proxy(function (index, element) {
                    if (element.value) {
                        var attributeId = element.id.replace(/[a-z]*/, '');
                        this.options.values[attributeId] = element.value;
                    }
                }, this));
            }
            // Put events to check select reloads
            $.each(this.options.setings, $.proxy(function (index, element) {
                $(element).on('change', this, this._configure);
            }, this));
            // fill state
            $.each(this.options.setings, $.proxy(function (index, element) {
                var attributeId = element.id.replace(/[a-z]*/, '');
                if (attributeId && this.options.spConfig.attributes[attributeId]) {
                    element.config = this.options.spConfig.attributes[attributeId];
                    element.attributeId = attributeId;
                    this.options.state[attributeId] = false;
                }
            }, this));
            var childSettings = [];
            for (var j = this.options.setings.length - 1; j >= 0; j--) {
                var prevSetting = this.options.setings[j - 1] ? this.options.setings[j - 1] : false;
                var nextSetting = this.options.setings[j + 1] ? this.options.setings[j + 1] : false;
                if (j === 0) {
                    this._fillSelect(this.options.setings[j]);
                } else {
                    this.options.setings[j].disabled = true;
                }
                this.options.setings[j].childsetings = childSettings.slice(0);
                this.options.setings[j].prevSetting = prevSetting;
                this.options.setings[j].nextSetting = nextSetting;
                childSettings.push(this.options.setings[j]);
            }
            // Set values to inputs
            this._configureForValues();
        },
        _configureForValues: function () {
            if (this.options.values) {
                this.options.setings.each($.proxy(function (index, element) {
                    var attributeId = element.attributeId;
                    element.value = (typeof(this.options.values[attributeId]) === 'undefined') ? '' : this.options.values[attributeId];
                    this._configureElement(element);
                }, this));
            }
        },
        _configure: function (event) {
            event.data._configureElement(this);
        },
        _configureElement: function (element) {
            this._reloadOptionLabels(element);
            if (element.value) {
                this.options.state[element.config.id] = element.value;
                if (element.nextSetting) {
                    element.nextSetting.disabled = false;
                    this._fillSelect(element.nextSetting);
                    this._resetChildren(element.nextSetting);
                }
            }
            else {
                this._resetChildren(element);
            }
            this._reloadPrice();
        },
        _reloadOptionLabels: function (element) {
            var selectedPrice = 0;
            if (element.options[element.selectedIndex].config && !this.options.spConfig.stablePrices) {
                selectedPrice = parseFloat(element.options[element.selectedIndex].config.price);
            }
            for (var i = 0; i < element.options.length; i++) {
                if (element.options[i].config) {
                    element.options[i].text = this._getOptionLabel(element.options[i].config, element.options[i].config.price - selectedPrice);
                }
            }
        },
        _resetChildren: function (element) {
            if (element.childsetings) {
                for (var i = 0; i < element.childsetings.length; i++) {
                    element.childsetings[i].selectedIndex = 0;
                    element.childsetings[i].disabled = true;
                    if (element.config) {
                        this.options.state[element.config.id] = false;
                    }
                }
            }
        },
        _fillSelect: function (element) {
            var attributeId = element.id.replace(/[a-z]*/, '');
            var options = this._getAttributeOptions(attributeId);
            this._clearSelect(element);
            element.options[0] = new Option('', '');
            element.options[0].innerHTML = this.options.spConfig.chooseText;

            var prevConfig = false;
            if (element.prevSetting) {
                prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
            }
            if (options) {
                var index = 1;
                for (var i = 0; i < options.length; i++) {
                    var allowedProducts = [];
                    if (prevConfig) {
                        for (var j = 0; j < options[i].products.length; j++) {
                            if (prevConfig.config.allowedProducts && prevConfig.config.allowedProducts.indexOf(options[i].products[j]) > -1) {
                                allowedProducts.push(options[i].products[j]);
                            }
                        }
                    } else {
                        allowedProducts = options[i].products.slice(0);
                    }
                    if (allowedProducts.size() > 0) {
                        options[i].allowedProducts = allowedProducts;
                        element.options[index] = new Option(this._getOptionLabel(options[i], options[i].price), options[i].id);
                        if (typeof options[i].price !== 'undefined') {
                            element.options[index].setAttribute('price', options[i].price);
                        }
                        element.options[index].config = options[i];
                        index++;
                    }
                }
            }
        },
        _getOptionLabel: function (option, price) {
            price = parseFloat(price);
            var tax, incl, excl;
            if (this.options.taxConfig.includeTax) {
                tax = price / (100 + this.options.taxConfig.defaultTax) * this.options.taxConfig.defaultTax;
                excl = price - tax;
                incl = excl * (1 + (this.options.taxConfig.currentTax / 100));
            } else {
                tax = price * (this.options.taxConfig.currentTax / 100);
                excl = price;
                incl = excl + tax;
            }

            if (this.options.taxConfig.showIncludeTax || this.options.taxConfig.showBothPrices) {
                price = incl;
            } else {
                price = excl;
            }

            var str = option.label;
            if (price) {
                if (this.options.taxConfig.showBothPrices) {
                    str += ' ' + this._formatPrice(excl, true) + ' (' + this._formatPrice(price, true) + ' ' + this.options.taxConfig.inclTaxTitle + ')';
                } else {
                    str += ' ' + this._formatPrice(price, true);
                }
            }
            return str;
        },
        _formatPrice: function (price, showSign) {
            var str = '';
            price = parseFloat(price);
            if (showSign) {
                if (price < 0) {
                    str += '-';
                    price = -price;
                }
                else {
                    str += '+';
                }
            }
            var roundedPrice = (Math.round(price * 100) / 100).toString();
            if (this.options.spConfig.prices && this.options.spConfig.prices[roundedPrice]) {
                str += this.options.spConfig.prices[roundedPrice];
            }
            else {
                str += this.options.spConfig.template.replace(/#\{(.*?)\}/, price.toFixed(2));
            }
            return str;
        },
        _clearSelect: function (element) {
            for (var i = element.options.length - 1; i >= 0; i--) {
                element.remove(i);
            }
        },
        _getAttributeOptions: function (attributeId) {
            if (this.options.spConfig.attributes[attributeId]) {
                return this.options.spConfig.attributes[attributeId].options;
            }
        },
        _reloadPrice: function () {
            if (this.options.spConfig.disablePriceReload) {
                return true;
            }
            var price = 0;
            var oldPrice = 0;
            for (var i = this.options.setings.length - 1; i >= 0; i--) {
                var selected = this.options.setings[i].options[this.options.setings[i].selectedIndex];
                if (selected.config) {
                    price += parseFloat(selected.config.price);
                    oldPrice += parseFloat(selected.config.oldPrice);
                }
            }
            this.options.priceOptionInstance.changePrice('config', {'price': price, 'oldPrice': oldPrice});
            this.options.priceOptionInstance.reloadPrice();
            return price;
        }
    });
})(jQuery);