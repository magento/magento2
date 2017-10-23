/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**************************** CONFIGURABLE PRODUCT **************************/
/* global Product, optionsPrice */
define([
    'jquery',
    'mage/template',
    'mage/translate',
    'prototype'
], function (jQuery, mageTemplate) {
    'use strict';

    if (typeof Product == 'undefined') {
        window.Product = {};
    }

    Product.Config = Class.create();
    Product.Config.prototype = {
        /**
         * Initialize function.
         *
         * @param {Object} config
         */
        initialize: function (config) {
            var separatorIndex, paramsStr, urlValues, i, childSettings, prevSetting, nextSetting;

            // Magic preprocessing
            // TODO MAGETWO-31539
            config.taxConfig = {
                showBothPrices: false,
                inclTaxTitle: jQuery.mage.__('Incl. Tax')
            };

            this.config     = config;
            this.taxConfig  = this.config.taxConfig;

            if (config.containerId) {
                this.settings   = $$('#' + config.containerId + ' ' + '.super-attribute-select');
            } else {
                this.settings   = $$('.super-attribute-select');
            }
            this.state      = new Hash();
            this.priceTemplate = mageTemplate(this.config.template);
            this.prices     = config.prices;
            this.values     = {};

            // Set default values from config
            if (config.defaultValues) {
                this.values = config.defaultValues;
            }

            // Overwrite defaults by url
            separatorIndex = window.location.href.indexOf('#');

            if (separatorIndex != -1) { //eslint-disable-line eqeqeq
                paramsStr = window.location.href.substr(separatorIndex + 1);
                urlValues = paramsStr.toQueryParams();

                for (i in urlValues) { //eslint-disable-line guard-for-in
                    this.values[i] = urlValues[i];
                }
            }

            // Overwrite defaults by inputs values if needed
            if (config.inputsInitialized) {
                this.values = {};
                this.settings.each(function (element) {
                    var attributeId;

                    if (element.value) {
                        attributeId = element.id.replace(/[a-z]*/, '');
                        this.values[attributeId] = element.value;
                    }
                }.bind(this));
            }

            // Put events to check select reloads
            this.settings.each(function (element) {
                Event.observe(element, 'change', this.configure.bind(this));
            }.bind(this));

            // fill state
            this.settings.each(function (element) {
                var attributeId = element.id.replace(/[a-z]*/, '');

                if (attributeId && this.config.attributes[attributeId]) {
                    element.config = this.config.attributes[attributeId];
                    element.attributeId = attributeId;
                    this.state[attributeId] = false;
                }
            }.bind(this));

            // Init settings dropdown
            childSettings = [];

            for (i = this.settings.length - 1; i >= 0; i--) {
                prevSetting = this.settings[i - 1] ? this.settings[i - 1] : false;
                nextSetting = this.settings[i + 1] ? this.settings[i + 1] : false;

                if (i === 0) {
                    this.fillSelect(this.settings[i]);
                } else {
                    this.settings[i].disabled = true;
                }
                $(this.settings[i]).childSettings = childSettings.clone();
                $(this.settings[i]).prevSetting   = prevSetting;
                $(this.settings[i]).nextSetting   = nextSetting;
                childSettings.push(this.settings[i]);
            }

            // Set values to inputs
            this.configureForValues();
            document.observe('dom:loaded', this.configureForValues.bind(this));
        },

        /**
         * Configure for values.
         */
        configureForValues: function () {
            if (this.values) {
                this.settings.each(function (element) {
                    var attributeId = element.attributeId;

                    element.value = typeof this.values[attributeId] === 'undefined' ? '' : this.values[attributeId];
                    this.configureElement(element);
                }.bind(this));
            }
        },

        /**
         * @param {Object} event
         */
        configure: function (event) {
            var element = Event.element(event);

            this.configureElement(element);
        },

        /**
         * @param {Object} element
         */
        configureElement: function (element) {
            this.reloadOptionLabels(element);

            if (element.value) {
                this.state[element.config.id] = element.value;

                if (element.nextSetting) {
                    element.nextSetting.disabled = false;
                    this.fillSelect(element.nextSetting);
                    this.resetChildren(element.nextSetting);
                }
            } else {
                this.resetChildren(element);
            }
            this.reloadPrice();
        },

        /**
         * @param {Object} element
         */
        reloadOptionLabels: function (element) {
            var selectedPrice = 0,
                option, i;

            if (element.options[element.selectedIndex] && element.options[element.selectedIndex].config) {
                option = element.options[element.selectedIndex].config;
                selectedPrice = parseFloat(this.config.optionPrices[option.allowedProducts[0]].finalPrice.amount);
            }
            element.setAttribute('price', selectedPrice);

            for (i = 0; i < element.options.length; i++) {
                if (element.options[i].config) {
                    element.options[i].setAttribute('price', selectedPrice);
                    element.options[i].setAttribute('summarizePrice', 0);
                    element.options[i].text = this.getOptionLabel(element.options[i].config, selectedPrice);
                }
            }
        },

        /* eslint-disable max-depth */
        /**
         * @param {Object} element
         */
        resetChildren: function (element) {
            var i;

            if (element.childSettings) {
                for (i = 0; i < element.childSettings.length; i++) {
                    element.childSettings[i].selectedIndex = 0;
                    element.childSettings[i].disabled = true;

                    if (element.config) {
                        this.state[element.config.id] = false;
                    }
                }
            }
        },

        /**
         * @param {Object} element
         */
        fillSelect: function (element) {
            var attributeId = element.id.replace(/[a-z]*/, ''),
                options = this.getAttributeOptions(attributeId),
                prevConfig = false,
                index = 1,
                i, j, allowedProducts;

            this.clearSelect(element);
            element.options[0] = new Option('', '');
            element.options[0].innerHTML = this.config.chooseText;

            if (element.prevSetting) {
                prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
            }

            if (options) {
                for (i = 0; i < options.length; i++) {
                    allowedProducts = [];

                    if (prevConfig) {
                        for (j = 0; j < options[i].products.length; j++) {
                            if (prevConfig.config.allowedProducts &&
                                prevConfig.config.allowedProducts.indexOf(options[i].products[j]) > -1
                            ) {
                                allowedProducts.push(options[i].products[j]);
                            }
                        }
                    } else {
                        allowedProducts = options[i].products.clone();
                    }

                    if (allowedProducts.size() > 0) {
                        options[i].allowedProducts = allowedProducts;
                        element.options[index] = new Option(this.getOptionLabel(options[i]), options[i].id);

                        if (typeof options[i].price != 'undefined') {
                            element.options[index].setAttribute('price', options[i].price);
                        }
                        element.options[index].config = options[i];
                        index++;
                    }
                }
            }
        },

        //eslint-enable max-depth
        /**
         * @param {Object} option
         */
        getOptionLabel: function (option) {
            return option.label;
        },

        /**
         * @param {*} price
         * @param {Boolean} showSign
         * @return {String}
         */
        formatPrice: function (price, showSign) {
            var str = '',
                roundedPrice;

            price = parseFloat(price);

            if (showSign) {
                if (price < 0) {
                    str += '-';
                    price = -price;
                } else {
                    str += '+';
                }
            }

            roundedPrice = Number(Math.round(price + 'e+2') + 'e-2').toString();

            if (this.prices && this.prices[roundedPrice]) {
                str += this.prices[roundedPrice];
            } else {
                str += this.priceTemplate({
                    data: {
                        price: price.toFixed(2)
                    }
                });
            }

            return str;
        },

        /**
         * @param {Object} element
         */
        clearSelect: function (element) {
            var i;

            for (i = element.options.length - 1; i >= 0; i--) {
                element.remove(i);
            }
        },

        /**
         * @param {*} attributeId
         * @return {*|undefined}
         */
        getAttributeOptions: function (attributeId) {
            if (this.config.attributes[attributeId]) {
                return this.config.attributes[attributeId].options;
            }
        },

        /**
         * Reload price.
         *
         * @return {undefined|Number}
         */
        reloadPrice: function () {
            var price = 0,
                oldPrice = 0,
                inclTaxPrice = 0,
                exclTaxPrice = 0,
                i, selected;

            if (this.config.disablePriceReload) {
                return undefined;
            }

            for (i = this.settings.length - 1; i >= 0; i--) {
                selected = this.settings[i].options[this.settings[i].selectedIndex];

                if (selected.config) {
                    price += parseFloat(selected.config.price);
                    oldPrice += parseFloat(selected.config.oldPrice);
                    inclTaxPrice += parseFloat(selected.config.inclTaxPrice);
                    exclTaxPrice += parseFloat(selected.config.exclTaxPrice);
                }
            }

            optionsPrice.changePrice(
                'config', {
                    'price': price,
                    'oldPrice': oldPrice,
                    'inclTaxPrice': inclTaxPrice,
                    'exclTaxPrice': exclTaxPrice
                }
            );
            optionsPrice.reload();

            return price;
        },

        /**
         * Reload old price.
         */
        reloadOldPrice: function () {
            var price, i, selected;

            if (this.config.disablePriceReload) {
                return;
            }

            if ($('old-price-' + this.config.productId)) {

                price = parseFloat(this.config.oldPrice);

                for (i = this.settings.length - 1; i >= 0; i--) {
                    selected = this.settings[i].options[this.settings[i].selectedIndex];

                    if (selected.config) {
                        price += parseFloat(selected.config.price);
                    }
                }

                if (price < 0) {
                    price = 0;
                }
                price = this.formatPrice(price);

                if ($('old-price-' + this.config.productId)) {
                    $('old-price-' + this.config.productId).innerHTML = price;
                }

            }
        }
    };
});
