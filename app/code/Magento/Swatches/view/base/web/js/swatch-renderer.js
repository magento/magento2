/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'mage/template',
    'mage/smart-keyboard-handler',
    'mage/translate',
    'priceUtils',
    'jquery-ui-modules/widget',
    'jquery/jquery.parsequery',
    'mage/validation/validation'
], function ($, _, mageTemplate, keyboardHandler, $t, priceUtils) {
    'use strict';

    /**
     * Extend form validation to support swatch accessibility
     */
    $.widget('mage.validation', $.mage.validation, {
        /**
         * Handle form with swatches validation. Focus on first invalid swatch block.
         *
         * @param {jQuery.Event} event
         * @param {Object} validation
         */
        listenFormValidateHandler: function (event, validation) {
            var swatchWrapper, firstActive, swatches, swatch, successList, errorList, firstSwatch;

            this._superApply(arguments);

            swatchWrapper = '.swatch-attribute-options';
            swatches = $(event.target).find(swatchWrapper);

            if (!swatches.length) {
                return;
            }

            swatch = '.swatch-attribute';
            firstActive = $(validation.errorList[0].element || []);
            successList = validation.successList;
            errorList = validation.errorList;
            firstSwatch = $(firstActive).parent(swatch).find(swatchWrapper);

            keyboardHandler.focus(swatches);

            $.each(successList, function (index, item) {
                $(item).parent(swatch).find(swatchWrapper).attr('aria-invalid', false);
            });

            $.each(errorList, function (index, item) {
                $(item.element).parent(swatch).find(swatchWrapper).attr('aria-invalid', true);
            });

            if (firstSwatch.length) {
                $(firstSwatch).trigger('focus');
            }
        }
    });

    /**
     * Render swatch controls with options and use tooltips.
     * Required two json:
     *  - jsonConfig (magento's option config)
     *  - jsonSwatchConfig (swatch's option config)
     *
     *  Tuning:
     *  - numberToShow (show "more" button if options are more)
     *  - onlySwatches (hide selectboxes)
     *  - moreButtonText (text for "more" button)
     *  - selectorProduct (selector for product container)
     *  - selectorProductPrice (selector for change price)
     */
    $.widget('mage.SwatchRenderer', {
        options: {
            classes: {
                attributeSelectedOptionLabelClass: 'swatch-attribute-selected-option',
                attributeOptionsWrapper: 'swatch-attribute-options',
                attributeInput: 'swatch-input',
                optionClass: 'swatch-option',
                selectClass: 'swatch-select',
                moreButton: 'swatch-more',
                loader: 'swatch-option-loading'
            },
            // option's json config
            jsonConfig: {},

            // swatch's json config
            jsonSwatchConfig: {},

            // selector of parental block of prices and swatches (need to know where to seek for price block)
            selectorProduct: '.product-info-main',

            // selector of price wrapper (need to know where set price)
            selectorProductPrice: '[data-role=priceBox]',

            //selector of product images gallery wrapper
            mediaGallerySelector: '[data-gallery-role=gallery-placeholder]',

            // selector of category product tile wrapper
            selectorProductTile: '.product-item',

            // number of controls to show (false or zero = show all)
            numberToShow: false,

            // show only swatch controls
            onlySwatches: false,

            // enable label for control
            enableControlLabel: true,

            // control label id
            controlLabelId: '',

            // text for more button
            moreButtonText: $t('More'),

            // Callback url for media
            mediaCallback: '',

            // Local media cache
            mediaCache: {},

            // Cache for BaseProduct images. Needed when option unset
            mediaGalleryInitial: [{}],

            // Use ajax to get image data
            useAjax: false,

            /**
             * Defines the mechanism of how images of a gallery should be
             * updated when user switches between configurations of a product.
             *
             * As for now value of this option can be either 'replace' or 'prepend'.
             *
             * @type {String}
             */
            gallerySwitchStrategy: 'replace',

            // whether swatches are rendered in product list or on product page
            inProductList: false,

            // sly-old-price block selector
            slyOldPriceSelector: '.sly-old-price',

            // tier prise selectors start
            tierPriceTemplateSelector: '#tier-prices-template',
            tierPriceBlockSelector: '[data-role="tier-price-block"]',
            tierPriceTemplate: '',
            // tier prise selectors end

            // A price label selector
            normalPriceLabelSelector: '.product-info-main .normal-price .price-label',
            qtyInfo: '#qty'
        },

        /**
         * Get chosen product
         *
         * @returns int|null
         */
        getProduct: function () {
            var products = this._CalcProducts();

            return _.isArray(products) ? products[0] : null;
        },

        /**
         * Get chosen product id
         *
         * @returns int|null
         */
        getProductId: function () {
            var products = this._CalcProducts();

            return _.isArray(products) && products.length === 1 ? products[0] : null;
        },

        /**
         * @private
         */
        _init: function () {
            // Don't render the same set of swatches twice
            if ($(this.element).attr('data-rendered')) {
                return;
            }

            $(this.element).attr('data-rendered', true);

            if (_.isEmpty(this.options.jsonConfig.images)) {
                this.options.useAjax = true;
                // creates debounced variant of _LoadProductMedia()
                // to use it in events handlers instead of _LoadProductMedia()
                this._debouncedLoadProductMedia = _.debounce(this._LoadProductMedia.bind(this), 500);
            }

            this.options.tierPriceTemplate = $(this.options.tierPriceTemplateSelector).html();

            if (this.options.jsonConfig !== '' && this.options.jsonSwatchConfig !== '') {
                // store unsorted attributes
                this.options.jsonConfig.mappedAttributes = _.clone(this.options.jsonConfig.attributes);
                this._sortAttributes();
                this._RenderControls();
                this._setPreSelectedGallery();
                $(this.element).trigger('swatch.initialized');
            } else {
                console.log('SwatchRenderer: No input data received');
            }
        },

        /**
         * @private
         */
        _sortAttributes: function () {
            this.options.jsonConfig.attributes = _.sortBy(this.options.jsonConfig.attributes, function (attribute) {
                return parseInt(attribute.position, 10);
            });
        },

        /**
         * @private
         */
        _create: function () {
            var options = this.options,
                gallery = $('[data-gallery-role=gallery-placeholder]', '.column.main'),
                productData = this._determineProductData(),
                $main = productData.isInProductView ?
                    this.element.parents('.column.main') :
                    this.element.parents('.product-item-info');

            if (productData.isInProductView) {
                gallery.data('gallery') ?
                    this._onGalleryLoaded(gallery) :
                    gallery.on('gallery:loaded', this._onGalleryLoaded.bind(this, gallery));
            } else {
                options.mediaGalleryInitial = [{
                    'img': $main.find('.product-image-photo').attr('src')
                }];
            }

            this.productForm = this.element.parents(this.options.selectorProductTile).find('form:first');
            this.inProductList = this.productForm.length > 0;
            $(this.options.qtyInfo).on('input', this._onQtyChanged.bind(this));
        },

        /**
         * Determine product id and related data
         *
         * @returns {{productId: *, isInProductView: bool}}
         * @private
         */
        _determineProductData: function () {
            // Check if product is in a list of products.
            var productId,
                isInProductView = false;

            productId = this.element.parents('.product-item-details')
                .find('.price-box.price-final_price').attr('data-product-id');

            if (!productId) {
                // Check individual product.
                productId = $('[name=product]').val();
                isInProductView = productId > 0;
            }

            return {
                productId: productId,
                isInProductView: isInProductView
            };
        },

        /**
         * Render controls
         *
         * @private
         */
        _RenderControls: function () {
            var $widget = this,
                container = $widget.element,
                options = $widget.options,
                jsonConfig = options.jsonConfig,
                showTooltip = options.showTooltip;

            $widget.optionsMap = {};

            jsonConfig.attributes.forEach(function (item) {
                var itemId = item.id;

                // Show only swatch controls
                if (options.onlySwatches && !options.jsonSwatchConfig.hasOwnProperty(itemId)) {
                    return;
                }

                $widget.optionsMap[itemId] = {};

                // Aggregate options array to hash (key => value)
                item.options.forEach(function (option) {
                    var optionProducts = option.products;

                    if (optionProducts.length > 0) {
                        $widget.optionsMap[itemId][option.id] = {
                            price: parseInt(
                                jsonConfig.optionPrices[optionProducts[0]].finalPrice.amount,
                                10
                            ),
                            products: optionProducts
                        };
                    }
                });
            });

            if ($widget.inProductList) {
                // Move all inputs to product form.
                $widget.productForm.append(container.find('.' + $widget.options.classes.attributeInput));
            }

            if (showTooltip === 1) {
                require(['Magento_Swatches/js/swatch-renderer-tooltip'], function () {
                    container
                        .find('[data-option-type="1"], [data-option-type="2"],' +
                            ' [data-option-type="0"], [data-option-type="3"]')
                        .SwatchRendererTooltip();
                });
            }

            // Handle events like click or change
            $widget._EventListener();

            // Rewind options
            $widget._Rewind(container);

            // Emulate click on all swatches from request
            $widget._EmulateSelected($.parseQuery());
            $widget._EmulateSelected($widget._getSelectedAttributes());
        },

        /**
         * Event listener
         *
         * @private
         */
        _EventListener: function () {
            var $widget = this,
                options = this.options.classes,
                target;

            $widget.element.on('click', '.' + options.optionClass, function () {
                return $widget._OnClick($(this), $widget);
            });

            $widget.element.on('change', '.' + options.selectClass, function () {
                return $widget._OnChange($(this), $widget);
            });

            $widget.element.on('click', '.' + options.moreButton, function (e) {
                e.preventDefault();

                return $widget._OnMoreClick($(this));
            });

            $widget.element.on('keydown', function (e) {
                if (e.which === 13) {
                    target = $(e.target);

                    if (target.is('.' + options.optionClass)) {
                        return $widget._OnClick(target, $widget);
                    } else if (target.is('.' + options.selectClass)) {
                        return $widget._OnChange(target, $widget);
                    } else if (target.is('.' + options.moreButton)) {
                        e.preventDefault();

                        return $widget._OnMoreClick(target);
                    }
                }
            });
        },

        /**
         * Load media gallery using ajax or json config.
         *
         * @private
         */
        _loadMedia: function () {
            var $main = this.inProductList ?
                this.element.parents('.product-item-info') :
                this.element.parents('.column.main'),
                images;

            if (this.options.useAjax) {
                this._debouncedLoadProductMedia();
            }  else {
                images = this.options.jsonConfig.images[this.getProduct()];

                if (!images) {
                    images = this.options.mediaGalleryInitial;
                }
                this.updateBaseImage(this._sortImages(images), $main, !this.inProductList);
            }
        },

        /**
         * Sorting images array
         *
         * @private
         */
        _sortImages: function (images) {
            return _.sortBy(images, function (image) {
                return parseInt(image.position, 10);
            });
        },

        /**
         * Event for swatch options
         *
         * @param {Object} $this
         * @param {Object} $widget
         * @private
         */
        _OnClick: function ($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                $wrapper = $this.parents('.' + $widget.options.classes.attributeOptionsWrapper),
                $label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                attributeId = $parent.data('attribute-id'),
                $input = $parent.find('.' + $widget.options.classes.attributeInput),
                checkAdditionalData = JSON.parse(this.options.jsonSwatchConfig[attributeId]['additional_data']),
                $priceBox = $widget.element.parents($widget.options.selectorProduct)
                    .find(this.options.selectorProductPrice);

            if ($widget.inProductList) {
                $input = $widget.productForm.find(
                    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                );
            }

            if ($this.hasClass('disabled')) {
                return;
            }

            if ($this.hasClass('selected')) {
                $parent.removeAttr('data-option-selected').find('.selected').removeClass('selected');
                $input.val('');
                $label.text('');
                $this.attr('aria-checked', false);
            } else {
                $parent.attr('data-option-selected', $this.data('option-id')).find('.selected').removeClass('selected');
                $label.text($this.data('option-label'));
                $input.val($this.data('option-id'));
                $input.attr('data-attr-name', this._getAttributeCodeById(attributeId));
                $this.addClass('selected');
                $widget._toggleCheckedAttributes($this, $wrapper);
            }

            $widget._Rebuild();

            if ($priceBox.is(':data(mage-priceBox)')) {
                $widget._UpdatePrice();
            }

            $(document).trigger('updateMsrpPriceBlock',
                [
                    this._getSelectedOptionPriceIndex(),
                    $widget.options.jsonConfig.optionPrices,
                    $priceBox
                ]);

            if (parseInt(checkAdditionalData['update_product_preview_image'], 10) === 1) {
                $widget._loadMedia();
            }

            $input.trigger('change');
        },

        /**
         * Get selected option price index
         *
         * @return {String|undefined}
         * @private
         */
        _getSelectedOptionPriceIndex: function () {
            var allowedProduct = this._getAllowedProductWithMinPrice(this._CalcProducts());

            if (_.isEmpty(allowedProduct)) {
                return undefined;
            }

            return allowedProduct;
        },

        /**
         * Get human readable attribute code (eg. size, color) by it ID from configuration
         *
         * @param {Number} attributeId
         * @returns {*}
         * @private
         */
        _getAttributeCodeById: function (attributeId) {
            var attribute = this.options.jsonConfig.mappedAttributes[attributeId];

            return attribute ? attribute.code : attributeId;
        },

        /**
         * Toggle accessibility attributes
         *
         * @param {Object} $this
         * @param {Object} $wrapper
         * @private
         */
        _toggleCheckedAttributes: function ($this, $wrapper) {
            $wrapper.attr('aria-activedescendant', $this.attr('id'))
                .find('.' + this.options.classes.optionClass).attr('aria-checked', false);
            $this.attr('aria-checked', true);
        },

        /**
         * Event for select
         *
         * @param {Object} $this
         * @param {Object} $widget
         * @private
         */
        _OnChange: function ($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                attributeId = $parent.data('attribute-id'),
                $input = $parent.find('.' + $widget.options.classes.attributeInput);

            if ($widget.productForm.length > 0) {
                $input = $widget.productForm.find(
                    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                );
            }

            if ($this.val() > 0) {
                $parent.attr('data-option-selected', $this.val());
                $input.val($this.val());
            } else {
                $parent.removeAttr('data-option-selected');
                $input.val('');
            }

            $widget._Rebuild();
            $widget._UpdatePrice();
            $widget._loadMedia();
            $input.trigger('change');
        },

        /**
         * Event for more switcher
         *
         * @param {Object} $this
         * @private
         */
        _OnMoreClick: function ($this) {
            $this.nextAll().show();
            $this.trigger('blur').remove();
        },

        /**
         * Rewind options for controls
         *
         * @private
         */
        _Rewind: function (controls) {
            controls.find('div[data-option-id], option[data-option-id]')
                .removeClass('disabled')
                .prop('disabled', false);
            controls.find('div[data-option-empty], option[data-option-empty]')
                .attr('disabled', true)
                .addClass('disabled')
                .attr('tabindex', '-1');
        },

        /**
         * Rebuild container
         *
         * @private
         */
        _Rebuild: function () {
            var $widget = this,
                controls = $widget.element.find('.' + $widget.options.classes.attributeClass + '[data-attribute-id]'),
                selected = controls.filter('[data-option-selected]');

            // Enable all options
            $widget._Rewind(controls);

            // done if nothing selected
            if (selected.length <= 0) {
                return;
            }

            // Disable not available options
            controls.each(function () {
                var $this = $(this),
                    id = $this.data('attribute-id'),
                    products = $widget._CalcProducts(id);

                if (selected.length === 1 && selected.first().data('attribute-id') === id) {
                    return;
                }

                $this.find('[data-option-id]').each(function () {
                    var $element = $(this),
                        option = $element.data('option-id');

                    if (!$widget.optionsMap.hasOwnProperty(id) || !$widget.optionsMap[id].hasOwnProperty(option) ||
                        $element.hasClass('selected') ||
                        $element.is(':selected')) {
                        return;
                    }

                    if (_.intersection(products, $widget.optionsMap[id][option].products).length <= 0) {
                        $element.attr('disabled', true).addClass('disabled');
                    }
                });
            });
        },

        /**
         * Get selected product list
         *
         * @returns {Array}
         * @private
         */
        _CalcProducts: function ($skipAttributeId) {
            var $widget = this,
                selectedOptions = '.' + $widget.options.classes.attributeClass + '[data-option-selected]',
                products = [];

            // Generate intersection of products
            $widget.element.find(selectedOptions).each(function () {
                var id = $(this).data('attribute-id'),
                    option = $(this).attr('data-option-selected');

                if ($skipAttributeId !== undefined && $skipAttributeId === id) {
                    return;
                }

                if (!$widget.optionsMap.hasOwnProperty(id) || !$widget.optionsMap[id].hasOwnProperty(option)) {
                    return;
                }

                if (products.length === 0) {
                    products = $widget.optionsMap[id][option].products;
                } else {
                    products = _.intersection(products, $widget.optionsMap[id][option].products);
                }
            });

            return products;
        },

        /**
         * Update total price
         *
         * @private
         */
        _UpdatePrice: function () {
            var $widget = this,
                $product = $widget.element.parents($widget.options.selectorProduct),
                $productPrice = $product.find(this.options.selectorProductPrice),
                result = $widget._getNewPrices(),
                tierPriceHtml,
                isShow;

            $productPrice.trigger(
                'updatePrice',
                {
                    'prices': $widget._getPrices(result, $productPrice.priceBox('option').prices)
                }
            );

            isShow = typeof result !== 'undefined' && result.oldPrice.amount !== result.finalPrice.amount;

            $productPrice.find('span:first').toggleClass('special-price', isShow);

            $product.find(this.options.slyOldPriceSelector)[isShow ? 'show' : 'hide']();

            if (typeof result !== 'undefined' && result.tierPrices && result.tierPrices.length) {
                if (this.options.tierPriceTemplate) {
                    tierPriceHtml = mageTemplate(
                        this.options.tierPriceTemplate,
                        {
                            'tierPrices': result.tierPrices,
                            '$t': $t,
                            'currencyFormat': this.options.jsonConfig.currencyFormat,
                            'priceUtils': priceUtils
                        }
                    );
                    $(this.options.tierPriceBlockSelector).html(tierPriceHtml).show();
                }
            } else {
                $(this.options.tierPriceBlockSelector).hide();
            }

            $(this.options.normalPriceLabelSelector).hide();

            _.each($('.' + this.options.classes.attributeOptionsWrapper), function (attribute) {
                if ($(attribute).find('.' + this.options.classes.optionClass + '.selected').length === 0) {
                    if ($(attribute).find('.' + this.options.classes.selectClass).length > 0) {
                        _.each($(attribute).find('.' + this.options.classes.selectClass), function (dropdown) {
                            if ($(dropdown).val() === '0') {
                                $(this.options.normalPriceLabelSelector).show();
                            }
                        }.bind(this));
                    } else {
                        $(this.options.normalPriceLabelSelector).show();
                    }
                }
            }.bind(this));
        },

        /**
         * Get new prices for selected options
         *
         * @returns {*}
         * @private
         */
        _getNewPrices: function () {
            var $widget = this,
                newPrices = $widget.options.jsonConfig.prices,
                allowedProduct = this._getAllowedProductWithMinPrice(this._CalcProducts());

            if (!_.isEmpty(allowedProduct)) {
                newPrices = this.options.jsonConfig.optionPrices[allowedProduct];
            }

            return newPrices;
        },

        /**
         * Get prices
         *
         * @param {Object} newPrices
         * @param {Object} displayPrices
         * @returns {*}
         * @private
         */
        _getPrices: function (newPrices, displayPrices) {
            var $widget = this;

            if (_.isEmpty(newPrices)) {
                newPrices = $widget._getNewPrices();
            }
            _.each(displayPrices, function (price, code) {

                if (newPrices[code]) {
                    displayPrices[code].amount = newPrices[code].amount - displayPrices[code].amount;
                }
            });

            return displayPrices;
        },

        /**
         * Get product with minimum price from selected options.
         *
         * @param {Array} allowedProducts
         * @returns {String}
         * @private
         */
        _getAllowedProductWithMinPrice: function (allowedProducts) {
            var optionPrices = this.options.jsonConfig.optionPrices,
                product = {},
                optionFinalPrice, optionMinPrice;

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
         * Gets all product media and change current to the needed one
         *
         * @private
         */
        _LoadProductMedia: function () {
            var $widget = this,
                $this = $widget.element,
                productData = this._determineProductData(),
                mediaCallData,
                mediaCacheKey,

                /**
                 * Processes product media data
                 *
                 * @param {Object} data
                 * @returns void
                 */
                mediaSuccessCallback = function (data) {
                    if (!(mediaCacheKey in $widget.options.mediaCache)) {
                        $widget.options.mediaCache[mediaCacheKey] = data;
                    }
                    $widget._ProductMediaCallback($this, data, productData.isInProductView);
                    setTimeout(function () {
                        $widget._DisableProductMediaLoader($this);
                    }, 300);
                };

            if (!$widget.options.mediaCallback) {
                return;
            }

            mediaCallData = {
                'product_id': this.getProduct()
            };

            mediaCacheKey = JSON.stringify(mediaCallData);

            if (mediaCacheKey in $widget.options.mediaCache) {
                $widget._XhrKiller();
                $widget._EnableProductMediaLoader($this);
                mediaSuccessCallback($widget.options.mediaCache[mediaCacheKey]);
            } else {
                mediaCallData.isAjax = true;
                $widget._XhrKiller();
                $widget._EnableProductMediaLoader($this);
                $widget.xhr = $.ajax({
                    url: $widget.options.mediaCallback,
                    cache: true,
                    type: 'GET',
                    dataType: 'json',
                    data: mediaCallData,
                    success: mediaSuccessCallback
                }).done(function () {
                    $widget._XhrKiller();
                });
            }
        },

        /**
         * Enable loader
         *
         * @param {Object} $this
         * @private
         */
        _EnableProductMediaLoader: function ($this) {
            var $widget = this;

            if ($('body.catalog-product-view').length > 0) {
                $this.parents('.column.main').find('.photo.image')
                    .addClass($widget.options.classes.loader);
            } else {
                //Category View
                $this.parents('.product-item-info').find('.product-image-photo')
                    .addClass($widget.options.classes.loader);
            }
        },

        /**
         * Disable loader
         *
         * @param {Object} $this
         * @private
         */
        _DisableProductMediaLoader: function ($this) {
            var $widget = this;

            if ($('body.catalog-product-view').length > 0) {
                $this.parents('.column.main').find('.photo.image')
                    .removeClass($widget.options.classes.loader);
            } else {
                //Category View
                $this.parents('.product-item-info').find('.product-image-photo')
                    .removeClass($widget.options.classes.loader);
            }
        },

        /**
         * Callback for product media
         *
         * @param {Object} $this
         * @param {String} response
         * @param {Boolean} isInProductView
         * @private
         */
        _ProductMediaCallback: function ($this, response, isInProductView) {
            var $main = isInProductView ? $this.parents('.column.main') : $this.parents('.product-item-info'),
                $widget = this,
                images = [],

                /**
                 * Check whether object supported or not
                 *
                 * @param {Object} e
                 * @returns {*|Boolean}
                 */
                support = function (e) {
                    return e.hasOwnProperty('large') && e.hasOwnProperty('medium') && e.hasOwnProperty('small');
                };

            if (_.size($widget) < 1 || !support(response)) {
                this.updateBaseImage(this.options.mediaGalleryInitial, $main, isInProductView);

                return;
            }

            images.push({
                full: response.large,
                img: response.medium,
                thumb: response.small,
                isMain: true
            });

            if (response.hasOwnProperty('gallery')) {
                $.each(response.gallery, function () {
                    if (!support(this) || response.large === this.large) {
                        return;
                    }
                    images.push({
                        full: this.large,
                        img: this.medium,
                        thumb: this.small
                    });
                });
            }

            this.updateBaseImage(images, $main, isInProductView);
        },

        /**
         * Check if images to update are initial and set their type
         * @param {Array} images
         */
        _setImageType: function (images) {

            images.map(function (img) {
                if (!img.type) {
                    img.type = 'image';
                }
            });

            return images;
        },

        /**
         * Update [gallery-placeholder] or [product-image-photo]
         * @param {Array} images
         * @param {jQuery} context
         * @param {Boolean} isInProductView
         */
        updateBaseImage: function (images, context, isInProductView) {
            var justAnImage = images[0],
                initialImages = this.options.mediaGalleryInitial,
                imagesToUpdate,
                gallery = context.find(this.options.mediaGallerySelector).data('gallery'),
                isInitial;

            if (isInProductView) {
                if (_.isUndefined(gallery)) {
                    context.find(this.options.mediaGallerySelector).on('gallery:loaded', function () {
                        this.updateBaseImage(images, context, isInProductView);
                    }.bind(this));

                    return;
                }

                imagesToUpdate = images.length ? this._setImageType($.extend(true, [], images)) : [];
                isInitial = _.isEqual(imagesToUpdate, initialImages);

                if (this.options.gallerySwitchStrategy === 'prepend' && !isInitial) {
                    imagesToUpdate = imagesToUpdate.concat(initialImages);
                }

                imagesToUpdate = this._setImageIndex(imagesToUpdate);

                gallery.updateData(imagesToUpdate);
                this._addFotoramaVideoEvents(isInitial);
            } else if (justAnImage && justAnImage.img) {
                context.find('.product-image-photo').attr('src', justAnImage.img);
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
                selectedOption: this.getProduct(),
                dataMergeStrategy: this.options.gallerySwitchStrategy
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
         * Kill doubled AJAX requests
         *
         * @private
         */
        _XhrKiller: function () {
            var $widget = this;

            if ($widget.xhr !== undefined && $widget.xhr !== null) {
                $widget.xhr.abort();
                $widget.xhr = null;
            }
        },

        /**
         * Emulate mouse click on all swatches that should be selected
         * @param {Object} [selectedAttributes]
         * @private
         */
        _EmulateSelected: function (selectedAttributes) {
            $.each(selectedAttributes, $.proxy(function (attributeCode, optionId) {
                var elem = this.element.find('.' + this.options.classes.attributeClass +
                    '[data-attribute-code="' + attributeCode + '"] [data-option-id="' + optionId + '"]'),
                    parentInput = elem.parent();

                if (elem.hasClass('selected')) {
                    return;
                }

                if (parentInput.hasClass(this.options.classes.selectClass)) {
                    parentInput.val(optionId);
                    parentInput.trigger('change');
                } else {
                    elem.trigger('click');
                }
            }, this));
        },

        /**
         * Emulate mouse click or selection change on all swatches that should be selected
         * @param {Object} [selectedAttributes]
         * @private
         */
        _EmulateSelectedByAttributeId: function (selectedAttributes) {
            $.each(selectedAttributes, $.proxy(function (attributeId, optionId) {
                var elem = this.element.find('.' + this.options.classes.attributeClass +
                    '[data-attribute-id="' + attributeId + '"] [data-option-id="' + optionId + '"]'),
                    parentInput = elem.parent();

                if (elem.hasClass('selected')) {
                    return;
                }

                if (parentInput.hasClass(this.options.classes.selectClass)) {
                    parentInput.val(optionId);
                    parentInput.trigger('change');
                } else {
                    elem.trigger('click');
                }
            }, this));
        },

        /**
         * Get default options values settings with either URL query parameters
         * @private
         */
        _getSelectedAttributes: function () {
            var hashIndex = window.location.href.indexOf('#'),
                selectedAttributes = {},
                params;

            if (hashIndex !== -1) {
                params = $.parseQuery(window.location.href.substr(hashIndex + 1));

                selectedAttributes = _.invert(_.mapObject(_.invert(params), function (attributeId) {
                    var attribute = this.options.jsonConfig.mappedAttributes[attributeId];

                    return attribute ? attribute.code : attributeId;
                }.bind(this)));
            }

            return selectedAttributes;
        },

        /**
         * Callback which fired after gallery gets initialized.
         *
         * @param {HTMLElement} element - DOM element associated with a gallery.
         */
        _onGalleryLoaded: function (element) {
            var galleryObject = element.data('gallery');

            this.options.mediaGalleryInitial = galleryObject.returnCurrentImages();
        },

        /**
         * Sets mediaCache for cases when jsonConfig contains preSelectedGallery on layered navigation result pages
         *
         * @private
         */
        _setPreSelectedGallery: function () {
            var mediaCallData;

            if (this.options.jsonConfig.preSelectedGallery) {
                mediaCallData = {
                    'product_id': this.getProduct()
                };

                this.options.mediaCache[JSON.stringify(mediaCallData)] = this.options.jsonConfig.preSelectedGallery;
            }
        },

        /**
         * Callback for quantity change event.
         */
        _onQtyChanged: function () {
            var $price = this.element.parents(this.options.selectorProduct)
                .find(this.options.selectorProductPrice);

            $price.trigger(
                'updatePrice',
                {
                    'prices': this._getPrices(this._getNewPrices(), $price.priceBox('option').prices)
                }
            );
        }
    });

    return $.mage.SwatchRenderer;
});
