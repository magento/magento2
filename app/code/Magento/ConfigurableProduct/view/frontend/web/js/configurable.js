/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * @api
 */
define([
    'jquery',
    'underscore',
    'mage/template',
    'mage/translate',
    'priceUtils',
    'priceBox',
    'jquery-ui-modules/widget',
    'jquery/jquery.parsequery',
    'fotoramaVideoEvents'
], function ($, _, mageTemplate, $t, priceUtils) {
    'use strict';

    $.widget('mage.configurable', {
        options: {
            superSelector: '.super-attribute-select',
            selectSimpleProduct: '[name="selected_configurable_option"]',
            priceHolderSelector: '.price-box',
            spConfig: {},
            state: {},
            priceFormat: {},
            optionTemplate: '<%- data.label %>' +
            '<% if (typeof data.finalPrice.value !== "undefined") { %>' +
            ' <%- data.finalPrice.formatted %>' +
            '<% } %>',
            mediaGallerySelector: '[data-gallery-role=gallery-placeholder]',
            mediaGalleryInitial: null,
            slyOldPriceSelector: '.sly-old-price',
            normalPriceLabelSelector: '.product-info-main .normal-price .price-label',

            /**
             * Defines the mechanism of how images of a gallery should be
             * updated when user switches between configurations of a product.
             *
             * As for now value of this option can be either 'replace' or 'prepend'.
             *
             * @type {String}
             */
            gallerySwitchStrategy: 'replace',
            tierPriceTemplateSelector: '#tier-prices-template',
            tierPriceBlockSelector: '[data-role="tier-price-block"]',
            tierPriceTemplate: '',
            selectorProduct: '.product-info-main',
            selectorProductPrice: '[data-role=priceBox]',
            qtyInfo: '#qty'
        },

        /**
         * Creates widget
         * @private
         */
        _create: function () {
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

            $(this.element).trigger('configurable.initialized');
            $(this.options.qtyInfo).on('input', this._reloadPrice.bind(this));
        },

        /**
         * Initialize tax configuration, initial settings, and options values.
         * @private
         */
        _initializeOptions: function () {
            var options = this.options,
                gallery = $(options.mediaGallerySelector),
                priceBoxOptions = $(this.options.priceHolderSelector).priceBox('option').priceConfig || null;

            if (priceBoxOptions && priceBoxOptions.optionTemplate) {
                options.optionTemplate = priceBoxOptions.optionTemplate;
            }

            if (priceBoxOptions && priceBoxOptions.priceFormat) {
                options.priceFormat = priceBoxOptions.priceFormat;
            }
            options.optionTemplate = mageTemplate(options.optionTemplate);
            options.tierPriceTemplate = $(this.options.tierPriceTemplateSelector).html();

            options.settings = options.spConfig.containerId ?
                $(options.spConfig.containerId).find(options.superSelector) :
                $(options.superSelector);

            options.values = options.spConfig.defaultValues || {};
            options.parentImage = $('[data-role=base-image-container] img').attr('src');

            this.inputSimpleProduct = this.element.find(options.selectSimpleProduct);

            gallery.data('gallery') ?
                this._onGalleryLoaded(gallery) :
                gallery.on('gallery:loaded', this._onGalleryLoaded.bind(this, gallery));

        },

        /**
         * Override default options values settings with either URL query parameters or
         * initialized inputs values.
         * @private
         */
        _overrideDefaults: function () {
            var hashIndex = window.location.href.indexOf('#');

            if (hashIndex !== -1) {
                this._parseQueryParams(window.location.href.substr(hashIndex + 1));
            }

            if (this.options.spConfig.inputsInitialized) {
                this._setValuesByAttribute();
            }

            this._setInitialOptionsLabels();
        },

        /**
         * Parse query parameters from a query string and set options values based on the
         * key value pairs of the parameters.
         * @param {*} queryString - URL query string containing query parameters.
         * @private
         */
        _parseQueryParams: function (queryString) {
            var queryParams = $.parseQuery({
                query: queryString
            });

            $.each(queryParams, $.proxy(function (key, value) {
                if (this.options.spConfig.attributes[key] !== undefined &&
                    _.find(this.options.spConfig.attributes[key].options, function (element) {
                        return element.id === value;
                    })) {
                    this.options.values[key] = value;
                }
            }, this));
        },

        /**
         * Override default options values with values based on each element's attribute
         * identifier.
         * @private
         */
        _setValuesByAttribute: function () {
            this.options.values = {};
            $.each(this.options.settings, $.proxy(function (index, element) {
                var attributeId;

                if (element.value) {
                    attributeId = element.id.replace(/[a-z]*/, '');

                    if (this.options.spConfig.attributes[attributeId] !== undefined &&
                        _.find(this.options.spConfig.attributes[attributeId].options, function (optionElement) {
                            return optionElement.id === element.value;
                        })) {
                        this.options.values[attributeId] = element.value;
                    }
                }
            }, this));
        },

        /**
         * Set additional field with initial label to be used when switching between options with different prices.
         * @private
         */
        _setInitialOptionsLabels: function () {
            $.each(this.options.spConfig.attributes, $.proxy(function (index, element) {
                $.each(element.options, $.proxy(function (optIndex, optElement) {
                    this.options.spConfig.attributes[index].options[optIndex].initialLabel = optElement.label;
                }, this));
            }, this));
        },

        /**
         * Set up .on('change') events for each option element to configure the option.
         * @private
         */
        _setupChangeEvents: function () {
            $.each(this.options.settings, $.proxy(function (index, element) {
                $(element).on('change', this, this._configure);
            }, this));
        },

        /**
         * Iterate through the option settings and set each option's element configuration,
         * attribute identifier. Set the state based on the attribute identifier.
         * @private
         */
        _fillState: function () {
            $.each(this.options.settings, $.proxy(function (index, element) {
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
        _setChildSettings: function () {
            var childSettings = [],
                settings = this.options.settings,
                index = settings.length,
                option;

            while (index--) {
                option = settings[index];

                if (index) {
                    option.disabled = true;
                } else {
                    this._fillSelect(option);
                }

                _.extend(option, {
                    childSettings: childSettings.slice(),
                    prevSetting: settings[index - 1],
                    nextSetting: settings[index + 1]
                });

                childSettings.push(option);
            }
        },

        /**
         * Setup for all configurable option settings. Set the value of the option and configure
         * the option, which sets its state, and initializes the option's choices, etc.
         * @private
         */
        _configureForValues: function () {
            if (this.options.values) {
                this.options.settings.each($.proxy(function (index, element) {
                    var attributeId = element.attributeId;

                    element.value = this.options.values[attributeId] || '';
                    this._configureElement(element);
                }, this));
            }
        },

        /**
         * Event handler for configuring an option.
         * @private
         * @param {Object} event - Event triggered to configure an option.
         */
        _configure: function (event) {
            event.data._configureElement(this);
        },

        /**
         * Configure an option, initializing it's state and enabling related options, which
         * populates the related option's selection and resets child option selections.
         * @private
         * @param {*} element - The element associated with a configurable option.
         */
        _configureElement: function (element) {
            this.simpleProduct = this._getSimpleProductId(element);

            if (element.value) {
                this.options.state[element.config.id] = element.value;

                if (element.nextSetting) {
                    element.nextSetting.disabled = false;
                    this._fillSelect(element.nextSetting);
                    this._resetChildren(element.nextSetting);
                } else {
                    if (!!document.documentMode) { //eslint-disable-line
                        this.inputSimpleProduct.val(element.options[element.selectedIndex].config.allowedProducts[0]);
                    } else {
                        this.inputSimpleProduct.val(element.selectedOptions[0].config.allowedProducts[0]);
                    }
                }
            } else {
                this._resetChildren(element);
            }

            this._reloadPrice();
            this._displayRegularPriceBlock(this.simpleProduct);
            this._displayTierPriceBlock(this.simpleProduct);
            this._displayNormalPriceLabel();
            this._changeProductImage();
        },

        /**
         * Change displayed product image according to chosen options of configurable product
         *
         * @private
         */
        _changeProductImage: function () {
            var images,
                initialImages = this.options.mediaGalleryInitial,
                gallery = $(this.options.mediaGallerySelector).data('gallery');

            if (_.isUndefined(gallery)) {
                $(this.options.mediaGallerySelector).on('gallery:loaded', function () {
                    this._changeProductImage();
                }.bind(this));

                return;
            }

            images = this.options.spConfig.images[this.simpleProduct];

            if (images) {
                images = this._sortImages(images);

                if (this.options.gallerySwitchStrategy === 'prepend') {
                    images = images.concat(initialImages);
                }

                images = $.extend(true, [], images);
                images = this._setImageIndex(images);

                gallery.updateData(images);
                this._addFotoramaVideoEvents(false);
            } else {
                gallery.updateData(initialImages);
                this._addFotoramaVideoEvents(true);
            }
        },

        /**
         * Add video events
         *
         * @param {Boolean} isInitial
         * @private
         */
        _addFotoramaVideoEvents: function (isInitial) {
            if (_.isUndefined($.mage.AddFotoramaVideoEvents)) {
                return;
            }

            if (isInitial) {
                $(this.options.mediaGallerySelector).AddFotoramaVideoEvents();

                return;
            }

            $(this.options.mediaGallerySelector).AddFotoramaVideoEvents({
                selectedOption: this.simpleProduct,
                dataMergeStrategy: this.options.gallerySwitchStrategy
            });
        },

        /**
         * Sorting images array
         *
         * @private
         */
        _sortImages: function (images) {
            return _.sortBy(images, function (image) {
                return image.position;
            });
        },

        /**
         * Set correct indexes for image set.
         *
         * @param {Array} images
         * @private
         */
        _setImageIndex: function (images) {
            var length = images.length,
                i;

            for (i = 0; length > i; i++) {
                images[i].i = i + 1;
            }

            return images;
        },

        /**
         * For a given option element, reset all of its selectable options. Clear any selected
         * index, disable the option choice, and reset the option's state if necessary.
         * @private
         * @param {*} element - The element associated with a configurable option.
         */
        _resetChildren: function (element) {
            if (element.childSettings) {
                _.each(element.childSettings, function (set) {
                    set.selectedIndex = 0;
                    set.disabled = true;
                });

                if (element.config) {
                    this.options.state[element.config.id] = false;
                }
            }
        },

        /**
         * Populates an option's selectable choices.
         * @private
         * @param {*} element - Element associated with a configurable option.
         */
        _fillSelect: function (element) {
            var attributeId = element.id.replace(/[a-z]*/, ''),
                options = this._getAttributeOptions(attributeId),
                prevConfig,
                index = 1,
                allowedProducts,
                allowedProductsByOption,
                allowedProductsAll,
                i,
                j,
                finalPrice = parseFloat(this.options.spConfig.prices.finalPrice.amount),
                optionFinalPrice,
                optionPriceDiff,
                optionPrices = this.options.spConfig.optionPrices,
                allowedOptions = [],
                indexKey,
                allowedProductMinPrice,
                allowedProductsAllMinPrice,
                canDisplayOutOfStockProducts = false,
                filteredSalableProducts;

            this._clearSelect(element);
            element.options[0] = new Option('', '');
            element.options[0].innerHTML = this.options.spConfig.chooseText;
            prevConfig = false;

            if (element.prevSetting) {
                prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
            }

            if (options) {
                for (indexKey in this.options.spConfig.index) {
                    /* eslint-disable max-depth */
                    if (this.options.spConfig.index.hasOwnProperty(indexKey)) {
                        allowedOptions = allowedOptions.concat(_.values(this.options.spConfig.index[indexKey]));
                    }
                }

                if (prevConfig) {
                    allowedProductsByOption = {};
                    allowedProductsAll = [];

                    for (i = 0; i < options.length; i++) {
                        /* eslint-disable max-depth */
                        for (j = 0; j < options[i].products.length; j++) {
                            // prevConfig.config can be undefined
                            if (prevConfig.config &&
                                prevConfig.config.allowedProducts &&
                                prevConfig.config.allowedProducts.indexOf(options[i].products[j]) > -1) {
                                if (!allowedProductsByOption[i]) {
                                    allowedProductsByOption[i] = [];
                                }
                                allowedProductsByOption[i].push(options[i].products[j]);
                                allowedProductsAll.push(options[i].products[j]);
                            }
                        }
                    }

                    if (typeof allowedProductsAll[0] !== 'undefined' &&
                        typeof optionPrices[allowedProductsAll[0]] !== 'undefined') {
                        allowedProductsAllMinPrice = this._getAllowedProductWithMinPrice(allowedProductsAll);
                        finalPrice = parseFloat(optionPrices[allowedProductsAllMinPrice].finalPrice.amount);
                    }
                }

                for (i = 0; i < options.length; i++) {
                    if (prevConfig && typeof allowedProductsByOption[i] === 'undefined') {
                        continue; //jscs:ignore disallowKeywords
                    }

                    allowedProducts = prevConfig ? allowedProductsByOption[i] : options[i].products.slice(0);
                    optionPriceDiff = 0;

                    if (typeof allowedProducts[0] !== 'undefined' &&
                        typeof optionPrices[allowedProducts[0]] !== 'undefined') {
                        allowedProductMinPrice = this._getAllowedProductWithMinPrice(allowedProducts);
                        optionFinalPrice = parseFloat(optionPrices[allowedProductMinPrice].finalPrice.amount);
                        optionPriceDiff = optionFinalPrice - finalPrice;
                        options[i].label = options[i].initialLabel;

                        if (optionPriceDiff !== 0) {
                            options[i].label += ' ' + priceUtils.formatPriceLocale(
                                optionPriceDiff,
                                this.options.priceFormat,
                                true
                            );
                        }
                    }

                    if (allowedProducts.length > 0 || _.include(allowedOptions, options[i].id)) {
                        options[i].allowedProducts = allowedProducts;
                        element.options[index] = new Option(this._getOptionLabel(options[i]), options[i].id);

                        if (this.options.spConfig.canDisplayShowOutOfStockStatus) {
                            filteredSalableProducts = $(this.options.spConfig.salable[attributeId][options[i].id]).
                            filter(options[i].allowedProducts);
                            canDisplayOutOfStockProducts = filteredSalableProducts.length === 0;
                        }

                        if (typeof options[i].price !== 'undefined') {
                            element.options[index].setAttribute('price', options[i].price);
                        }

                        if (allowedProducts.length === 0 || canDisplayOutOfStockProducts) {
                            element.options[index].disabled = true;
                        }

                        element.options[index].config = options[i];
                        index++;
                    }

                    /* eslint-enable max-depth */
                }
            }
        },

        /**
         * Generate the label associated with a configurable option. This includes the option's
         * label or value and the option's price.
         * @private
         * @param {*} option - A single choice among a group of choices for a configurable option.
         * @return {String} The option label with option value and price (e.g. Black +1.99)
         */
        _getOptionLabel: function (option) {
            return option.label;
        },

        /**
         * Removes an option's selections.
         * @private
         * @param {*} element - The element associated with a configurable option.
         */
        _clearSelect: function (element) {
            var i;

            for (i = element.options.length - 1; i >= 0; i--) {
                element.remove(i);
            }
        },

        /**
         * Retrieve the attribute options associated with a specific attribute Id.
         * @private
         * @param {Number} attributeId - The id of the attribute whose configurable options are sought.
         * @return {Object} Object containing the attribute options.
         */
        _getAttributeOptions: function (attributeId) {
            if (this.options.spConfig.attributes[attributeId]) {
                return this.options.spConfig.attributes[attributeId].options;
            }
        },

        /**
         * Reload the price of the configurable product incorporating the prices of all of the
         * configurable product's option selections.
         */
        _reloadPrice: function () {
            $(this.options.priceHolderSelector).trigger('updatePrice', this._getPrices());
        },

        /**
         * Get product various prices
         * @returns {{}}
         * @private
         */
        _getPrices: function () {
            var prices = {},
                elements = _.toArray(this.options.settings),
                allowedProduct;

            _.each(elements, function (element) {
                var selected = element.options[element.selectedIndex],
                    config = selected && selected.config,
                    priceValue = this._calculatePrice({});

                if (config && config.allowedProducts.length === 1) {
                    priceValue = this._calculatePrice(config);
                } else if (element.value) {
                    allowedProduct = this._getAllowedProductWithMinPrice(config.allowedProducts);
                    priceValue = this._calculatePrice({
                        'allowedProducts': [
                            allowedProduct
                        ]
                    });
                }

                if (!_.isEmpty(priceValue)) {
                    prices.prices = priceValue;
                }
            }, this);

            return prices;
        },

        /**
         * Get product with minimum price from selected options.
         *
         * @param {Array} allowedProducts
         * @returns {String}
         * @private
         */
        _getAllowedProductWithMinPrice: function (allowedProducts) {
            var optionPrices = this.options.spConfig.optionPrices,
                product = {},
                optionMinPrice, optionFinalPrice;

            _.each(allowedProducts, function (allowedProduct) {
                optionFinalPrice = parseFloat(optionPrices[allowedProduct].finalPrice.amount);

                if (_.isEmpty(product) || optionFinalPrice < optionMinPrice) {
                    optionMinPrice = optionFinalPrice;
                    product = allowedProduct;
                }
            }, this);

            return product;
        },

        /**
         * Returns prices for configured products
         *
         * @param {*} config - Products configuration
         * @returns {*}
         * @private
         */
        _calculatePrice: function (config) {
            var displayPrices = $(this.options.priceHolderSelector).priceBox('option').prices,
                newPrices = this.options.spConfig.optionPrices[_.first(config.allowedProducts)] || {};

            _.each(displayPrices, function (price, code) {
                displayPrices[code].amount = newPrices[code] ? newPrices[code].amount - displayPrices[code].amount : 0;
            });

            return displayPrices;
        },

        /**
         * Returns Simple product Id
         *  depending on current selected option.
         *
         * @private
         * @param {HTMLElement} element
         * @returns {String|undefined}
         */
        _getSimpleProductId: function (element) {
            // TODO: Rewrite algorithm. It should return ID of
            //        simple product based on selected options.
            var allOptions = element.config.options,
                value = element.value,
                config;

            config = _.filter(allOptions, function (option) {
                return option.id === value;
            });
            config = _.first(config);

            return _.isEmpty(config) ?
                undefined :
                _.first(config.allowedProducts);

        },

        /**
         * Show or hide regular price block
         *
         * @param {*} optionId
         * @private
         */
        _displayRegularPriceBlock: function (optionId) {
            var shouldBeShown = true,
                $priceBox = this.element.parents(this.options.selectorProduct)
                    .find(this.options.selectorProductPrice);

            _.each(this.options.settings, function (element) {
                if (element.value === '') {
                    shouldBeShown = false;
                }
            });

            if (shouldBeShown &&
                this.options.spConfig.optionPrices[optionId].oldPrice.amount !==
                this.options.spConfig.optionPrices[optionId].finalPrice.amount
            ) {
                $(this.options.slyOldPriceSelector).show();
            } else {
                $(this.options.slyOldPriceSelector).hide();
            }

            $(document).trigger('updateMsrpPriceBlock',
                [
                    optionId,
                    this.options.spConfig.optionPrices,
                    $priceBox
                ]
            );
        },

        /**
         * Show or hide normal price label
         *
         * @private
         */
        _displayNormalPriceLabel: function () {
            var shouldBeShown = false;

            _.each(this.options.settings, function (element) {
                if (element.value === '') {
                    shouldBeShown = true;
                }
            });

            if (shouldBeShown) {
                $(this.options.normalPriceLabelSelector).show();
            } else {
                $(this.options.normalPriceLabelSelector).hide();
            }
        },

        /**
         * Callback which fired after gallery gets initialized.
         *
         * @param {HTMLElement} element - DOM element associated with gallery.
         */
        _onGalleryLoaded: function (element) {
            var galleryObject = element.data('gallery');

            this.options.mediaGalleryInitial = galleryObject.returnCurrentImages();
        },

        /**
         * Show or hide tier price block
         *
         * @param {*} optionId
         * @private
         */
        _displayTierPriceBlock: function (optionId) {
            var tierPrices = typeof optionId != 'undefined' && this.options.spConfig.optionPrices[optionId].tierPrices;

            if (_.isArray(tierPrices) && tierPrices.length > 0) {

                if (this.options.tierPriceTemplate) {
                    $(this.options.tierPriceBlockSelector).html(
                        mageTemplate(this.options.tierPriceTemplate, {
                            'tierPrices': tierPrices,
                            '$t': $t,
                            'currencyFormat': this.options.spConfig.currencyFormat,
                            'priceUtils': priceUtils
                        })
                    ).show();
                }
            } else {
                $(this.options.tierPriceBlockSelector).hide();
            }
        }
    });

    return $.mage.configurable;
});
