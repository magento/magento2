/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['jquery', 'underscore', 'jquery/ui'], function ($, _) {
    'use strict';

    /**
     * Parse params
     * @param {String} query
     * @returns {{}}
     */
    $.parseParams = function (query) {
        var re = /([^&=]+)=?([^&]*)/g,
            decodeRE = /\+/g,  // Regex for replacing addition symbol with a space
            decode = function (str) {
                return decodeURIComponent(str.replace(decodeRE, " "));
            },
            params = {}, e;

        while (e = re.exec(query)) {
            var k = decode(e[1]), v = decode(e[2]);
            if (k.substring(k.length - 2) === '[]') {
                k = k.substring(0, k.length - 2);
                (params[k] || (params[k] = [])).push(v);
            }
            else params[k] = v;
        }
        return params;
    };

    /**
     * Render tooltips by attributes (only to up).
     * Required element attributes:
     *  - option-type (integer, 0-3)
     *  - option-label (string)
     *  - option-tooltip-thumb
     *  - option-tooltip-value
     */
    $.widget('custom.SwatchRendererTooltip', {
        options: {
            delay: 200,                             //how much ms before tooltip to show
            tooltip_class: 'swatch-option-tooltip'  //configurable, but remember about css
        },

        /**
         * @private
         */
        _init: function () {
            var $widget = this,
                $this = this.element,
                $element = $('.' + $widget.options.tooltip_class),
                timer,
                type = parseInt($this.attr('option-type'), 10),
                label = $this.attr('option-label'),
                thumb = $this.attr('option-tooltip-thumb'),
                value = $this.attr('option-tooltip-value');

            if ($element.size() == 0) {
                $element = $('<div class="' + $widget.options.tooltip_class + '"><div class="image"></div><div class="title"></div><div class="corner"></div></div>');
                $('body').append($element);
            }

            var $image = $element.find('.image'),
                $title = $element.find('.title'),
                $corner = $element.find('.corner');

            $this.hover(function () {
                if (!$this.hasClass('disabled')) {
                    timer = setTimeout(
                        function () {

                            // Image
                            if (type == 2) {
                                $image.css({
                                    'background': 'url("' + thumb + '") no-repeat center', //Background case
                                    'background-size': 'initial'
                                });
                                $image.show();
                            }

                            // Color
                            else if (type == 1) {
                                $image.css({background: value});
                                $image.show();
                            }

                            // Textual or Clear
                            else if (type == 0 || type == 3) {
                                $image.hide();
                            }

                            $title.text(label);

                            var leftOpt = $this.offset().left,
                                left = leftOpt + ($this.width() / 2) - ($element.width() / 2),
                                $window = $(window);

                            // the numbers (5 and 5) is magick constants for offset from left or right page
                            if (left < 0) {
                                left = 5;
                            } else if (left + $element.width() > $window.width()) {
                                left = $window.width() - $element.width() - 5;
                            }

                            // the numbers (6,  3 and 18) is magick constants for offset tooltip
                            var leftCorner = 0;
                            if ($element.width() < $this.width()) {
                                leftCorner = $element.width() / 2 - 3;
                            } else {
                                leftCorner = (leftOpt > left ? leftOpt - left : left - leftOpt) + ($this.width() / 2) - 6;
                            }

                            $corner.css({
                                left: leftCorner
                            });
                            $element.css({
                                left: left,
                                top: $this.offset().top - $element.height() - $corner.height() - 18
                            }).show();
                        },
                        $widget.options.delay
                    );
                }
            }, function () {
                $element.hide();
                clearTimeout(timer);
            });
            $(document).on('tap', function () {
                $element.hide();
                clearTimeout(timer);
            });

            $this.on('tap', function (event) {
                event.stopPropagation();
            });
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
    $.widget('custom.SwatchRenderer', {
        options: {
            classes: {
                attributeClass: 'swatch-attribute',
                attributeLabelClass: 'swatch-attribute-label',
                attributeSelectedOptionLabelClass: 'swatch-attribute-selected-option',
                attributeOptionsWrapper: 'swatch-attribute-options',
                attributeInput: 'swatch-input',
                optionClass: 'swatch-option',
                selectClass: 'swatch-select',
                moreButton: 'swatch-more',
                loader: 'swatch-option-loading'
            },
            jsonConfig: {},                                    // option's json config
            jsonSwatchConfig: {},                              // swatch's json config
            selectorProduct: '.product-info-main',             // selector of parental block of prices and swatches (need to know where to seek for price block)
            selectorProductPrice: '[data-role=priceBox]',      // selector of price wrapper (need to know where set price)
            numberToShow: false,                               // number of controls to show (false or zero = show all)
            onlySwatches: false,                               // show only swatch controls
            enableControlLabel: true,                          // enable label for control
            moreButtonText: 'More',                            // text for more button
            mediaCallback: '',                                 // Callback url for media
            mediaGalleryInitial: [{}]                          // Cache for BaseProduct images. Needed when option unset
        },

        /**
         * Get chosen product
         *
         * @returns array
         */
        getProduct: function () {
            return this._CalcProducts().shift();
        },

        /**
         * @private
         */
        _init: function () {
            if (this.options.jsonConfig != '' && this.options.jsonSwatchConfig != '') {
                this._RenderControls();
            } else {
                console.log('SwatchRenderer: No input data received');
            }
        },

        /**
         * @private
         */
        _create: function () {
            var options = this.options,
                gallery = $('[data-gallery-role=gallery-placeholder]', '.column.main'),
                isProductViewExist = $('body.catalog-product-view').size() > 0,
                $main = isProductViewExist ?
                    this.element.parents('.column.main') :
                    this.element.parents('.product-item-info');

            if (isProductViewExist) {
                gallery.on('gallery:loaded', function () {
                    var galleryObject = gallery.data('gallery');

                    options.mediaGalleryInitial = galleryObject.returnCurrentImages();
                });
            } else {
                options.mediaGalleryInitial = [{
                    'img': $main.find('.product-image-photo').attr('src')
                }];
            }
        },

        /**
         * Render controls
         *
         * @private
         */
        _RenderControls: function () {
            var $widget = this,
                container = this.element,
                classes = this.options.classes,
                chooseText = this.options.jsonConfig.chooseText;

            $widget.optionsMap = {};

            $.each(this.options.jsonConfig.attributes, function () {
                var item = this,
                    options = $widget._RenderSwatchOptions(item),
                    select = $widget._RenderSwatchSelect(item, chooseText),
                    input = $widget._RenderFormInput(item),
                    label = '';

                // Show only swatch controls
                if ($widget.options.onlySwatches && !$widget.options.jsonSwatchConfig.hasOwnProperty(item.id)) {
                    return;
                }

                if ($widget.options.enableControlLabel) {
                    label +=
                        '<span class="' + classes.attributeLabelClass + '">' + item.label + '</span>' +
                        '<span class="' + classes.attributeSelectedOptionLabelClass + '"></span>';
                }

                // Create new control
                container.append(
                    '<div class="' + classes.attributeClass + ' ' + item.code + '" attribute-code="' + item.code + '" attribute-id="' + item.id + '">' + label +
                    '<div class="' + classes.attributeOptionsWrapper + ' clearfix">' + options + select + '</div>' + input + '</div>'
                );

                $widget.optionsMap[item.id] = {};

                // Aggregate options array to hash (key => value)
                $.each(item.options, function () {
                    if (this.products.length > 0) {
                        $widget.optionsMap[item.id][this.id] = {
                            price: parseInt($widget.options.jsonConfig.optionPrices[this.products[0]].finalPrice.amount, 10),
                            products: this.products
                        };
                    }
                });
            });

            // Connect Tooltip
            container
                .find('[option-type="1"], [option-type="2"], [option-type="0"], [option-type="3"]')
                .SwatchRendererTooltip();

            // Hide all elements below more button
            $('.' + classes.moreButton).nextAll().hide();

            // Handle events like click or change
            $widget._EventListener();

            // Rewind options
            $widget._Rewind(container);

            //Emulate click on all swatches from Request
            $widget._EmulateSelected();
        },

        /**
         * Render swatch options by part of config
         *
         * @param config
         * @returns {string}
         * @private
         */
        _RenderSwatchOptions: function (config) {
            if (!this.options.jsonSwatchConfig.hasOwnProperty(config.id)) {
                return '';
            }

            var optionConfig = this.options.jsonSwatchConfig[config.id],
                optionClass = this.options.classes.optionClass,
                moreLimit = this.options.numberToShow,
                moreClass = this.options.classes.moreButton,
                moreText = this.options.moreButtonText,
                countAttributes = 0,
                html = '';

            $.each(config.options, function () {
                if (!optionConfig.hasOwnProperty(this.id)) {
                    return '';
                }

                // Add more button
                if (moreLimit != false && moreLimit == countAttributes++) {
                    html += '<a href="#" class="' + moreClass + '">' + moreText + '</a>';
                }

                var id = this.id,
                    type = optionConfig[id].type,
                    value = optionConfig[id].hasOwnProperty('value') ? optionConfig[id].value : '',
                    thumb = optionConfig[id].hasOwnProperty('thumb') ? optionConfig[id].thumb : '',
                    label = this.label ? this.label : '',
                    attr =
                        ' option-type="' + type + '"' +
                        ' option-id="' + id + '"' +
                        ' option-label="' + label + '"' +
                        ' option-tooltip-thumb="' + thumb + '"' +
                        ' option-tooltip-value="' + value + '"';

                if (!this.hasOwnProperty('products') || this.products.length <= 0) {
                    attr += ' option-empty="true"';
                }

                // Text
                if (type == 0) {
                    html += '<div class="' + optionClass + ' text" ' + attr + '>' + (value ? value : label) + '</div>';
                }

                // Color
                else if (type == 1) {
                    html += '<div class="' + optionClass + ' color" ' + attr +
                        '" style="background: ' + value + ' no-repeat center; background-size: initial;">' + '' + '</div>';
                }

                // Image
                else if (type == 2) {
                    html += '<div class="' + optionClass + ' image" ' + attr +
                        '" style="background: url(' + value + ') no-repeat center; background-size: initial;">' + '' + '</div>';
                }

                // Clear
                else if (type == 3) {
                    html += '<div class="' + optionClass + '" ' + attr + '></div>';
                }

                // Default
                else {
                    html += '<div class="' + optionClass + '" ' + attr + '>' + label + '</div>';
                }
            });

            return html;
        },

        /**
         * Render select by part of config
         *
         * @param config
         * @param chooseText
         * @returns {string}
         * @private
         */
        _RenderSwatchSelect: function (config, chooseText) {
            if (this.options.jsonSwatchConfig.hasOwnProperty(config.id)) {
                return '';
            }

            var html =
                '<select class="' + this.options.classes.selectClass + ' ' + config.code + '">' +
                '<option value="0" option-id="0">' + chooseText + '</option>';

            $.each(config.options, function () {
                var label = this.label,
                    attr = ' value="' + this.id + '" option-id="' + this.id + '"';

                if (!this.hasOwnProperty('products') || this.products.length <= 0) {
                    attr += ' option-empty="true"';
                }

                html += '<option ' + attr + '>' + label + '</option>';
            });

            html += '</select>';

            return html;
        },

        /**
         * Input for submit form.
         * This control shouldn't have "type=hidden", "display: none" for validation work :(
         *
         * @param config
         * @private
         */
        _RenderFormInput: function (config) {
            return '<input class="' + this.options.classes.attributeInput + '" ' +
                'name="super_attribute[' + config.id + ']" ' +
                'value="" ' +
                'data-validate="{required:true}" ' +
                'aria-required="true" ' +
                'aria-invalid="true" ' +
                'style="visibility: hidden; position:absolute; left:-1000px">';
        },

        /**
         * Event listener
         *
         * @private
         */
        _EventListener: function () {

            var $widget = this;

            $widget.element.on('click', '.' + this.options.classes.optionClass, function () {
                return $widget._OnClick($(this), $widget);
            });

            $widget.element.on('change', '.' + this.options.classes.selectClass, function () {
                return $widget._OnChange($(this), $widget);
            });

            $widget.element.on('click', '.' + this.options.classes.moreButton, function (e) {
                e.preventDefault();
                return $widget._OnMoreClick($(this));
            });
        },

        /**
         * Event for swatch options
         *
         * @param $this
         * @param $widget
         * @private
         */
        _OnClick: function ($this, $widget) {

            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                $label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                $input = $parent.find('.' + $widget.options.classes.attributeInput);

            if ($this.hasClass('disabled')) {
                return;
            }

            if ($this.hasClass('selected')) {
                $parent.removeAttr('option-selected').find('.selected').removeClass('selected');
                $input.val('');
                $label.text('');
            } else {
                $parent.attr('option-selected', $this.attr('option-id')).find('.selected').removeClass('selected');
                $label.text($this.attr('option-label'));
                $input.val($this.attr('option-id'));
                $this.addClass('selected');
            }

            $widget._Rebuild();

            if ($widget.element.parents($widget.options.selectorProduct)
                    .find(this.options.selectorProductPrice).is(':data(mage-priceBox)')
            ) {
                $widget._UpdatePrice();
            }

            $widget._LoadProductMedia();
        },

        /**
         * Event for select
         *
         * @param $this
         * @param $widget
         * @private
         */
        _OnChange: function ($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                $input = $parent.find('.' + $widget.options.classes.attributeInput);

            if ($this.val() > 0) {
                $parent.attr('option-selected', $this.val());
                $input.val($this.val());
            } else {
                $parent.removeAttr('option-selected');
                $input.val('');
            }

            $widget._Rebuild();
            $widget._UpdatePrice();
            $widget._LoadProductMedia();
        },

        /**
         * Event for more switcher
         *
         * @param $this
         * @private
         */
        _OnMoreClick: function ($this) {
            $this.nextAll().show();
            $this.blur().remove();
        },

        /**
         * Rewind options for controls
         *
         * @private
         */
        _Rewind: function (controls) {
            controls.find('div[option-id], option[option-id]').removeClass('disabled').removeAttr('disabled');
            controls.find('div[option-empty], option[option-empty]').attr('disabled', true).addClass('disabled');
        },

        /**
         * Rebuild container
         *
         * @private
         */
        _Rebuild: function () {

            var $widget = this,
                controls = $widget.element.find('.' + $widget.options.classes.attributeClass + '[attribute-id]'),
                selected = controls.filter('[option-selected]');

            // Enable all options
            $widget._Rewind(controls);

            // done if nothing selected
            if (selected.size() <= 0) {
                return;
            }

            // Disable not available options
            controls.each(function () {
                var $this = $(this),
                    id = $this.attr('attribute-id'),
                    products = $widget._CalcProducts(id);

                if (selected.size() == 1 && selected.first().attr('attribute-id') == id) {
                    return;
                }

                $this.find('[option-id]').each(function () {
                    var $this = $(this),
                        option = $this.attr('option-id');

                    if (!$widget.optionsMap.hasOwnProperty(id) || !$widget.optionsMap[id].hasOwnProperty(option) ||
                        $this.hasClass('selected') ||
                        $this.is(':selected')) {
                        return;
                    }

                    if (_.intersection(products, $widget.optionsMap[id][option].products).length <= 0) {
                        $this.attr('disabled', true).addClass('disabled');
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
                products = [];

            // Generate intersection of products
            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                var id = $(this).attr('attribute-id');
                var option = $(this).attr('option-selected');

                if ($skipAttributeId != undefined && $skipAttributeId == id) {
                    return;
                }

                if (!$widget.optionsMap.hasOwnProperty(id) || !$widget.optionsMap[id].hasOwnProperty(option)) {
                    return;
                }

                if (products.length == 0) {
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
                options = _.object(_.keys($widget.optionsMap), {}),
                result;

            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                var attributeId = $(this).attr('attribute-id'),
                    selectedOptionId = $(this).attr('option-selected');

                options[attributeId] = selectedOptionId;
            });

            result = $widget.options.jsonConfig.optionPrices[_.findKey($widget.options.jsonConfig.index, options)];

            $productPrice.trigger(
                'updatePrice',
                {
                    'prices': $widget._getPrices(result, $productPrice.priceBox('option').prices)
                }
            );

        },

        /**
         * Get prices
         * @param {Object} newPrices
         * @returns {Object}
         * @private
         */
        _getPrices: function (newPrices, displayPrices) {
            var $widget = this;

            if (_.isEmpty(newPrices)) {
                newPrices = $widget.options.jsonConfig.prices;
            }

            _.each(displayPrices, function (price, code) {
                if (newPrices[code]) {
                    displayPrices[code].amount = newPrices[code].amount - displayPrices[code].amount;
                }
            });

            return displayPrices;
        },

        /**
         * Gets all product media and change current to the needed one
         *
         * @private
         */
        _LoadProductMedia: function () {
            var $widget = this,
                $this = $widget.element,
                attributes = {},
                productId = 0;

            if (!$widget.options.mediaCallback) {
                return;
            }

            $this.find('[option-selected]').each(function () {
                var $selected = $(this);
                attributes[$selected.attr('attribute-code')] = $selected.attr('option-selected');
            });

            if ($('body.catalog-product-view').size() > 0) {
                //Product Page
                productId = document.getElementsByName('product')[0].value;
            } else {
                //Category View
                productId = $this.parents('.product.details.product-item-details')
                    .find('.price-box.price-final_price').attr('data-product-id');
            }

            var additional = $.parseParams(window.location.search.substring(1));

            $widget._XhrKiller();
            $widget._EnableProductMediaLoader($this);
            $widget.xhr = $.post(
                $widget.options.mediaCallback,
                {product_id: productId, attributes: attributes, isAjax: true, additional: additional},
                function (data) {
                    $widget._ProductMediaCallback($this, data);
                    $widget._DisableProductMediaLoader($this);
                },
                'json'
            ).done(function () {
                    $widget._XhrKiller();
                });
        },

        /**
         * Enable loader
         *
         * @param $this
         * @private
         */
        _EnableProductMediaLoader: function ($this) {
            var $widget = this;

            if ($('body.catalog-product-view').size() > 0) {
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
         * @param $this
         * @private
         */
        _DisableProductMediaLoader: function ($this) {
            var $widget = this;

            if ($('body.catalog-product-view').size() > 0) {
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
         * @param $this
         * @param response
         * @private
         */
        _ProductMediaCallback: function ($this, response) {
            var isProductViewExist = $('body.catalog-product-view').size() > 0,
                $main = isProductViewExist
                    ? $this.parents('.column.main')
                    : $this.parents('.product-item-info'),
                $widget = this,
                images = [],
                support = function (e) {
                    return e.hasOwnProperty('large') && e.hasOwnProperty('medium') && e.hasOwnProperty('small');
                };

            if ($widget._ObjectLength(response) < 1) {
                this.updateBaseImage(this.options.mediaGalleryInitial, $main, isProductViewExist);

                return;
            }

            if (support(response)) {
                images.push({
                    full: response.large,
                    img: response.medium,
                    thumb: response.small
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
            }

            this.updateBaseImage(images, $main, isProductViewExist);
        },

        /**
         * Update [gallery-placeholder] or [product-image-photo]
         * @param {Array} images
         * @param {jQuery} context
         * @param {Boolean} isProductViewExist
         */
        updateBaseImage: function (images, context, isProductViewExist) {
            var justAnImage = images[0];

            if (isProductViewExist) {
                context
                    .find('[data-gallery-role=gallery-placeholder]')
                    .data('gallery')
                    .updateData(images);
            } else if (justAnImage && justAnImage.img) {
                context.find('.product-image-photo').attr('src', justAnImage.img);
            }
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
         *
         * @private
         */
        _EmulateSelected: function () {
            var $widget = this,
                $this = $widget.element,
                request = $.parseParams(window.location.search.substring(1));

            $.each(request, function (key, value) {
                $this.find('.' + $widget.options.classes.attributeClass
                    + '[attribute-code="' + key + '"] [option-id="' + value + '"]').trigger('click');
            });
        },

        /**
         * Returns an array/object's length
         * @param obj
         * @returns {number}
         * @private
         */
        _ObjectLength: function (obj) {
            var size = 0,
                key;

            for (key in obj) {
                if (obj.hasOwnProperty(key)) size++;
            }

            return size;
        }
    });
});
