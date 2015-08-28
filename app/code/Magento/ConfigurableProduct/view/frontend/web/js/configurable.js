/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define([
    "jquery",
    "underscore",
    "mage/template",
    "priceUtils",
    "priceBox",
    "jquery/ui",
    "jquery/jquery.parsequery",
    "mage/gallery"
], function($, _, mageTemplate, utils){

    $.widget('mage.configurable', {
        options: {
            superSelector: '.super-attribute-select',
            selectSimpleProduct: '[name="selected_configurable_option"]',
            priceHolderSelector: '.price-box',
            state: {},
            priceFormat: {},
            optionTemplate: '<%- data.label %>' +
                            '<% if (data.finalPrice.value) { %>' +
                                ' <%- data.finalPrice.formatted %>' +
                            '<% } %>',
            mediaGallerySelector: '[data-role=media-gallery]'
        },

        _create: function() {
            // Initial setting of various option values
            this._initializeOptions();

            // Override defaults with URL query parameters and/or inputs values
            this._overrideDefaults();

            // Change events to check select reloads
            this._setupChangeEvents();

            // Fill state
            this._fillState();

            // Setup child and prev/next settings
            this._setChildSettings();

            // Setup/configure values to inputs
            this._configureForValues();
        },

        /**
         * Initialize tax configuration, initial settings, and options values.
         * @private
         */
        _initializeOptions: function() {
            var priceBoxOptions = $(this.options.priceHolderSelector).priceBox('option');

            if(priceBoxOptions.priceConfig && priceBoxOptions.priceConfig.optionTemplate) {
                this.options.optionTemplate = priceBoxOptions.priceConfig.optionTemplate;
            }

            if(priceBoxOptions.priceConfig && priceBoxOptions.priceConfig.priceFormat) {
                this.options.priceFormat = priceBoxOptions.priceConfig.priceFormat;
            }
            this.options.optionTemplate = mageTemplate(this.options.optionTemplate);

            this.options.settings = (this.options.spConfig.containerId) ?
                $(this.options.spConfig.containerId).find(this.options.superSelector) :
                $(this.options.superSelector);

            this.options.values = this.options.spConfig.defaultValues || {};
            this.options.parentImage = $('[data-role=base-image-container] img').attr('src');

            this.initialGalleryImages = $(this.options.mediaGallerySelector).data('mageGallery')
                ? $(this.options.mediaGallerySelector).gallery('option', 'images')
                : [];
            this.inputSimpleProduct = this.element.find(this.options.selectSimpleProduct);
        },

        /**
         * Override default options values settings with either URL query parameters or
         * initialized inputs values.
         * @private
         */
        _overrideDefaults: function() {
            var hashIndex = window.location.href.indexOf('#');
            if (hashIndex !== -1) {
                this._parseQueryParams(window.location.href.substr(hashIndex + 1));
            }
            if (this.options.spConfig.inputsInitialized) {
                this._setValuesByAttribute();
            }
        },

        /**
         * Parse query parameters from a query string and set options values based on the
         * key value pairs of the parameters.
         * @param queryString URL query string containing query parameters.
         * @private
         */
        _parseQueryParams: function(queryString) {
            var queryParams = $.parseQuery({query: queryString});
            $.each(queryParams, $.proxy(function(key, value) {
                this.options.values[key] = value;
            }, this));
        },

        /**
         * Override default options values with values based on each element's attribute
         * identifier.
         * @private
         */
        _setValuesByAttribute: function() {
            this.options.values = {};
            $.each(this.options.settings, $.proxy(function(index, element) {
                if (element.value) {
                    var attributeId = element.id.replace(/[a-z]*/, '');
                    this.options.values[attributeId] = element.value;
                }
            }, this));
        },

        /**
         * Set up .on('change') events for each option element to configure the option.
         * @private
         */
        _setupChangeEvents: function() {
            $.each(this.options.settings, $.proxy(function(index, element) {
                $(element).on('change', this, this._configure);
            }, this));
        },

        /**
         * Iterate through the option settings and set each option's element configuration,
         * attribute identifier. Set the state based on the attribute identifier.
         * @private
         */
        _fillState: function() {
            $.each(this.options.settings, $.proxy(function(index, element) {
                var attributeId = element.id.replace(/[a-z]*/, '');
                if (attributeId && this.options.spConfig.attributes[attributeId]) {
                    element.config = this.options.spConfig.attributes[attributeId];
                    element.attributeId = attributeId;
                    this.options.state[attributeId] = false;
                }
            }, this));
        },

        /**
         * Set each option's child settings, and next/prev option setting. Fill (initialize)
         * an option's list of selections as needed or disable an option's setting.
         * @private
         */
        _setChildSettings: function() {
            var childSettings   = [],
                settings        = this.options.settings,
                index           = settings.length,
                option;

            while (index--) {
                option = settings[index];

                !index ?
                    this._fillSelect(option) :
                    (option.disabled = true);

                _.extend(option, {
                    childSettings:  childSettings.slice(),
                    prevSetting:    settings[index - 1],
                    nextSetting:    settings[index + 1]
                });

                childSettings.push(option);
            }
        },

        /**
         * Setup for all configurable option settings. Set the value of the option and configure
         * the option, which sets its state, and initializes the option's choices, etc.
         * @private
         */
        _configureForValues: function() {
            if (this.options.values) {
                this.options.settings.each($.proxy(function(index, element) {
                    var attributeId = element.attributeId;
                    element.value = (typeof(this.options.values[attributeId]) === 'undefined') ?
                        '' :
                        this.options.values[attributeId];
                    this._configureElement(element);
                }, this));
            }
        },

        /**
         * Event handler for configuring an option.
         * @private
         * @param event Event triggered to configure an option.
         */
        _configure: function(event) {
            event.data._configureElement(this);
        },

        /**
         * Configure an option, initializing it's state and enabling related options, which
         * populates the related option's selection and resets child option selections.
         * @private
         * @param element The element associated with a configurable option.
         */
        _configureElement: function(element) {
            if (element.value) {
                this.options.state[element.config.id] = element.value;
                if (element.nextSetting) {
                    element.nextSetting.disabled = false;
                    this._fillSelect(element.nextSetting);
                    this._resetChildren(element.nextSetting);
                } else {
                    this.inputSimpleProduct.val(element.selectedOptions[0].config.allowedProducts[0]);
                }
            }
            else {
                this._resetChildren(element);
            }
            this._reloadPrice();
            this._changeProductImage();
        },

        /**
         * Change displayed product image according to chosen options of configurable product
         * @private
         */
        _changeProductImage: function () {
            var images = this.options.spConfig.images,
                imagesArray = null,
                galleryElement = $(this.options.mediaGallerySelector);
            $.each(this.options.settings, function (k, v) {
                var selectValue = parseInt(v.value, 10),
                    attributeId = v.id.replace(/[a-z]*/, '');
                if (selectValue > 0 && attributeId) {
                    if (!imagesArray) {
                        imagesArray = images[attributeId][selectValue];
                    } else {
                        var intersectedArray = {};
                        $.each(imagesArray, function (productId) {
                            if (images[attributeId][selectValue][productId]) {
                                intersectedArray[productId] = images[attributeId][selectValue][productId];
                            }
                        });
                        imagesArray = intersectedArray;
                    }
                }
            });

            var result = [];
            $.each(imagesArray || {}, function (k, v) {
                result.push({
                    small: v,
                    medium: v,
                    large: v
                });
            });

            if (galleryElement.length && galleryElement.data('mageGallery')) {
                galleryElement.gallery('option', 'images', result.length > 0 ? result : this.initialGalleryImages);
            }
        },

        /**
         * For a given option element, reset all of its selectable options. Clear any selected
         * index, disable the option choice, and reset the option's state if necessary.
         * @private
         * @param element The element associated with a configurable option.
         */
        _resetChildren: function(element) {
            if (element.childSettings) {
                for (var i = 0; i < element.childSettings.length; i++) {
                    element.childSettings[i].selectedIndex = 0;
                    element.childSettings[i].disabled = true;
                    if (element.config) {
                        this.options.state[element.config.id] = false;
                    }
                }
            }
        },

        /**
         * Populates an option's selectable choices.
         * @private
         * @param element Element associated with a configurable option.
         */
        _fillSelect: function(element) {
            var attributeId = element.id.replace(/[a-z]*/, ''),
                options = this._getAttributeOptions(attributeId);
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
                            // prevConfig.config can be undefined
                            if (prevConfig.config &&
                                prevConfig.config.allowedProducts &&
                                prevConfig.config.allowedProducts.indexOf(options[i].products[j]) > -1) {
                                allowedProducts.push(options[i].products[j]);
                            }
                        }
                    } else {
                        allowedProducts = options[i].products.slice(0);
                    }
                    if (allowedProducts.length > 0) {
                        options[i].allowedProducts = allowedProducts;
                        element.options[index] = new Option(this._getOptionLabel(options[i]), options[i].id);
                        if (typeof options[i].price !== 'undefined') {
                            element.options[index].setAttribute('price', options[i].prices);
                        }
                        element.options[index].config = options[i];
                        index++;
                    }
                }
            }
        },

        /**
         * Generate the label associated with a configurable option. This includes the option's
         * label or value and the option's price.
         * @private
         * @param option A single choice among a group of choices for a configurable option.
         * @param selOption Current selected option.
         * @return {String} The option label with option value and price (e.g. Black +1.99)
         */
        _getOptionLabel: function(option, selOption) {
            return option.label;
        },

        /**
         * Removes an option's selections.
         * @private
         * @param element The element associated with a configurable option.
         */
        _clearSelect: function(element) {
            for (var i = element.options.length - 1; i >= 0; i--) {
                element.remove(i);
            }
        },

        /**
         * Retrieve the attribute options associated with a specific attribute Id.
         * @private
         * @param attributeId The id of the attribute whose configurable options are sought.
         * @return {Object} Object containing the attribute options.
         */
        _getAttributeOptions: function(attributeId) {
            if (this.options.spConfig.attributes[attributeId]) {
                return this.options.spConfig.attributes[attributeId].options;
            }
        },

        /**
         * Reload the price of the configurable product incorporating the prices of all of the
         * configurable product's option selections.
         * @private
         * @return {Number} The price of the configurable product including selected options.
         */
        _reloadPrice: function() {
            $(this.options.priceHolderSelector).trigger('updatePrice', this._getPrices());
        },

        _getPrices: function () {
            var prices = {},
                elements = _.toArray(this.options.settings);

            _.each(elements, function(element) {
                var selected = element.options[element.selectedIndex],
                    config = selected && selected.config;

                prices[element.attributeId] = config && config.allowedProducts.length === 1
                    ? this._calculatePrice(config)
                    : {};
            }, this);

            return prices;
        },

        _calculatePrice: function (config) {
            var displayPrices = $(this.options.priceHolderSelector).priceBox('option').prices;
            var newPrices = this.options.spConfig.optionPrices[_.first(config.allowedProducts)];

            _.each(displayPrices, function (price, code) {
                if (newPrices[code]) {
                    displayPrices[code].amount =  newPrices[code].amount - displayPrices[code].amount
                }
            });
            return displayPrices;
        }

    });

    return $.mage.configurable;
});
