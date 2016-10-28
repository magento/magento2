/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/grid/paging/paging'
], function (Component, $, ko, _, alert, paging) {
    'use strict';

    /**
     * @param {String} message
     * @constructor
     */
    function UserException(message) {
        this.message = message;
        this.name = 'UserException';
    }
    UserException.prototype = Object.create(Error.prototype);

    return Component.extend({
        defaults: {
            opened: false,
            attributes: [],
            productMatrix: [],
            productMatrixSerialized: ko.observable(''),
            associatedProducts: [],
            associatedProductsSerialized: ko.observable(''),
            configurations: [],
            configurationsSerialized: ko.observable(''),
            variations: [],
            productAttributes: [],
            fullAttributes: [],
            rowIndexToEdit: false,
            productAttributesMap: null,
            modules: {
                associatedProductGrid: '${ $.configurableProductGrid }'
            },
            paging: paging({
                name: 'configurableProductVariationsGrid.paging',
                sizesConfig: {
                    component: 'Magento_ConfigurableProduct/js/variations/paging/sizes',
                    name: 'configurableProductVariationsGrid_sizes'
                }
            })
        },

        /**
         * @override
         */
        initialize: function () {
            this._super();

            if (this.variations.length) {
                this.render(this.variations, this.productAttributes);
                this.disableConfigurableAttributes(this.attributes);
            }
            this.initProductAttributesMap();
        },

        /**
         * @override
         */
        initObservable: function () {
            var $form = $('[data-form="edit-product"]'),
                formSubmitHandlers = $form.data('events').submit,
                pagingObservables = {
                    current: ko.getObservable(this.paging, 'current'),
                    pageSize: ko.getObservable(this.paging, 'pageSize')
                };

            this._super().observe('actions opened attributes productMatrix');
            this.paging.totalRecords = this.variations.length;

            _.each(pagingObservables, function (observable) {
                observable.subscribe(function () {
                    if (this.variations.length > 0) {
                        this.render(this.variations);
                    }
                }, this);
            }, this);

            $form.submit(function (event) {
                var variations = this.prepareVariations(),
                    validationError = this.validateVariationPrices(this.variations) || false;

                this.productMatrixSerialized(JSON.stringify(variations));
                this.associatedProductsSerialized(JSON.stringify(this.associatedProducts));
                this.configurationsSerialized(JSON.stringify(this.configurations));

                if (validationError) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    pagingObservables.current(
                        Math.floor(this.variations.indexOf(validationError) / pagingObservables.pageSize() + 1)
                    );
                    $form.validation('isValid');
                }
            }.bind(this));
            formSubmitHandlers.splice(0, 0, formSubmitHandlers.pop());

            return this;
        },

        /**
         * Validate variations data.
         * @param {Array} variations
         */
        validateVariationPrices: function (variations) {
            return _.find(variations, function (variation) {
                return variation.hasOwnProperty('price') && variation.price === '';
            });
        },

        /**
         * @param {String} variationKey
         */
        showGrid: function (variationKey) {
            var rowIndex = _.findIndex(this.variations, function (variation) {
                    return variation.variationKey === variationKey;
                }),
                product = this.variations[rowIndex],
                attributes = JSON.parse(product.attribute),
                filterModifier = product.productId ? {
                    'entity_id': {
                        'condition_type': 'neq', value: product.productId
                    }
                } : {};

            this.rowIndexToEdit = rowIndex;

            filterModifier = _.extend(filterModifier, _.mapObject(attributes, function (value) {
                return {
                    'condition_type': 'eq',
                    'value': value
                };
            }));
            this.associatedProductGrid().open(
                {
                    'filters': attributes,
                    'filters_modifier': filterModifier
                },
                'changeProduct',
                false
            );
        },

        /**
         * @param {Array} newProducts
         */
        changeProduct: function (newProducts) {
            var oldProduct = this.variations[this.rowIndexToEdit],
                newProduct = this._makeProduct(_.extend(oldProduct, newProducts[0]));

            this.productAttributesMap[this.getVariationKey(newProduct.options)] = newProduct.productId;
            this.variations.splice(this.rowIndexToEdit, 1, newProduct);
            this.render(this.variations);
        },

        /**
         * @param {Array} newProducts
         */
        appendProducts: function (newProducts) {
            var newProduct = {};

            this.variations.push.apply(
                this.variations,
                _.map(
                    newProducts,
                    _.wrap(
                        this._makeProduct.bind(this),
                        function (func, product) {
                            product.associatedProductId = product.productId;
                            newProduct = func(product);

                            this.productAttributesMap[this.getVariationKey(newProduct.options)] = newProduct.productId;

                            return newProduct;
                        }.bind(this)
                    )
                )
            );
            this.associatedProducts.push(newProduct.productId);
            this.render(this.variations);
        },

        /**
         * @param {Object} product
         * @returns {Object}
         */
        _makeProduct: function (product) {
            var productId = product['entity_id'] || product.productId || null,
                attributes = _.pick(product, this.attributes().pluck('code')),
                options = _.map(attributes, function (option, attribute) {
                    var oldOptions = _.findWhere(this.attributes(), {
                            code: attribute
                        }).options,
                        result;

                    if (_.isFunction(oldOptions)) {
                        result = oldOptions.findWhere({
                            value: option
                        });
                    } else {
                        result = _.findWhere(oldOptions, {
                            value: option
                        });
                    }

                    return result;
                }.bind(this));

            return {
                attribute: JSON.stringify(attributes),
                editable: false,
                images: {
                    preview: product['thumbnail_src']
                },
                name: product.name || product.sku,
                options: options,
                price: parseFloat(product.price.replace(/[^\d.]+/g, '')).toFixed(4),
                productId: productId,
                associatedProductId: product.associatedProductId || null,
                productUrl: this.buildProductUrl(productId),
                quantity: product.quantity || null,
                sku: product.sku,
                status: product.status === undefined ? 1 : parseInt(product.status, 10),
                variationKey: this.getVariationKey(options),
                weight: product.weight || null
            };
        },

        /**
         * @param {String} name
         * @see use in matrix.phtml
         * @returns {jQuery}
         */
        getProductValue: function (name) {
            return $('[name="product[' + name.split('/').join('][') + ']"]', this.productForm).val();
        },

        /**
         * @param {Object} data
         * @param {String} field
         * @see use in matrix.phtml
         * @returns {String}
         */
        getRowId: function (data, field) {
            var key = data.variationKey;

            return 'variations-matrix-' + key + '-' + field;
        },

        /**
         * @param {Object} variation
         * @param {String} field
         * @returns {String}
         */
        getVariationRowName: function (variation, field) {
            var result;

            if (variation.productId) {
                result = 'configurations[' + variation.productId + '][' + field.split('/').join('][') + ']';
            } else {
                result = 'variations-matrix[' + variation.variationKey + '][' + field.split('/').join('][') + ']';
            }

            return result;
        },

        /**
         * @param {Object} attribute
         * @param {String} field
         * @see use in matrix.phtml
         * @returns {String}
         */
        getAttributeRowName: function (attribute, field) {
            return 'product[configurable_attributes_data][' + attribute.id + '][' + field + ']';
        },

        /**
         * @param {Object} attribute
         * @param {Object} option
         * @param {String} field
         * @see use in matrix.phtml
         * @returns {String}
         */
        getOptionRowName: function (attribute, option, field) {
            return 'product[configurable_attributes_data][' + attribute.id + '][values][' +
                option.value + '][' + field + ']';
        },

        /**
         * @param {Array} variations
         * @param {Array} attributes
         */
        render: function (variations, attributes) {
            var variationsPage;

            this.variations = variations;

            if (!_.isUndefined(attributes)) {
                this.attributes(attributes);
                this.disableConfigurableAttributes(this.attributes);
            }
            this.paging.totalRecords = this.variations.length;

            this.changeButtonWizard();
            variationsPage = this.prepareRenderPage();
            this.populateVariationMatrix(variationsPage);
            this.initImageUpload();
            this.showPrice();
            this.productMatrixSerialized(JSON.stringify(this.prepareVariations()));
        },

        /**
         * Maps internal object structure to the server-accepted object structure.
         * @returns {Object}
         */
        prepareVariations: function () {
            var mappedVariations = {},
                configurations = {};

            this.associatedProducts = _.intersection(this.variations.pluck('productId'), this.associatedProducts);

            _.each(this.variations, function (variation) {
                var attributes;

                if (variation.productId) {
                    configurations[variation.productId] = {
                        'status': variation.status || '1'
                    };

                    if (this.associatedProducts.indexOf(variation.productId) === -1) {
                        this.associatedProducts.push(variation.productId);
                    }

                    return;
                }

                attributes = _.reduce(variation.options, function (memo, option) {
                    var attribute = {};

                    attribute[option['attribute_code']] = option.value;

                    return _.extend(memo, attribute);
                }, {});

                this.generateImageGallery(variation);
                mappedVariations[this.getVariationKey(variation.options)] = {
                    'image': variation.image || '',
                    'media_gallery': variation['media_gallery'] || {},
                    'name': variation.name || variation.sku,
                    'configurable_attribute': JSON.stringify(attributes),
                    'status': variation.status || '1',
                    'sku': variation.sku,
                    'price': variation.price,
                    'weight': variation.weight,
                    'quantity_and_stock_status': {
                        'qty': variation.quantity || null
                    }
                };
                _.each(variation.imageTypes, function (imageFile, key) {
                    mappedVariations[this.getVariationKey(variation.options)][key] = imageFile;
                }, this);
            }, this);
            this.configurations = configurations;

            return mappedVariations;
        },

        /**
         * @returns {Array}
         */
        prepareRenderPage: function () {
            return this.variations.slice(
                (this.paging.current - 1) * this.paging.pageSize,
                this.paging.pageSize * this.paging.current
            );
        },

        /**
         * Changes label of variation generator button.
         */
        changeButtonWizard: function () {
            var $button = $('[data-action=open-steps-wizard] [data-role=button-label]');

            $button.text($button.attr('data-edit-label'));
        },

        /**
         * Get attributes options.
         * @see use in matrix.phtml
         * @returns {Array}
         */
        getAttributesOptions: function () {
            return this.showVariations() ? this.productMatrix()[0].options : [];
        },

        /**
         * @returns {Boolean}
         */
        showVariations: function () {
            return this.productMatrix().length > 0;
        },

        /**
         * @param {Array} variations
         */
        populateVariationMatrix: function (variations) {
            var tempMatrix = [];

            _.each(variations, function (variation) {
                var attributes = _.reduce(variation.options, function (memo, option) {
                    var attribute = {};

                    attribute[option['attribute_code']] = option.value;

                    return _.extend(memo, attribute);
                }, {});

                tempMatrix.push(_.extend(variation, {
                    productId: variation.productId || null,
                    name: variation.name || variation.sku,
                    weight: variation.weight,
                    attribute: JSON.stringify(attributes),
                    variationKey: this.getVariationKey(variation.options),
                    editable: variation.editable === undefined ? !variation.productId : variation.editable,
                    productUrl: this.buildProductUrl(variation.productId),
                    status: variation.status || '1',

                    /**
                     * Validates variation price.
                     */
                    validatePrice: function () {
                        $('[data-form="edit-product"]').validation('isValid');
                    }
                }));
            }, this);
            this.productMatrix([]);
            this.productMatrix(tempMatrix);
        },

        /**
         * @param {String} productId
         * @returns {String}
         */
        buildProductUrl: function (productId) {
            return this.productUrl.replace('%id%', productId);
        },

        /**
         * @param {Number} variationKey
         */
        removeProduct: function (variationKey) {
            var removedProduct, rowIndex;

            rowIndex = _.findIndex(this.variations, function (variation) {
                return variation.variationKey === variationKey;
            });

            removedProduct = this.variations.splice(rowIndex, 1);

            this.opened(false);
            delete this.productAttributesMap[this.getVariationKey(removedProduct[0].options)];

            if (this.variations.length === 0) {
                this.attributes().each(function (attribute) {
                    $('[data-attribute-code="' + attribute.code + '"] select').removeProp('disabled');
                });
            }

            if (removedProduct[0].productId) {
                rowIndex = this.associatedProducts.indexOf(removedProduct[0].productId);
                this.associatedProducts.splice(rowIndex, 1);
            }
            this.render(this.variations);
        },

        /**
         * @param {String} variationKey
         * @see use in matrix.phtml
         */
        toggleProduct: function (variationKey) {
            var rowIndex = _.findIndex(this.variations, function (variation) {
                    return variation.variationKey === variationKey;
                }),
                productRow = this.variations[rowIndex];

            if (productRow.editable) {
                _.each(['name', 'sku', 'quantity', 'weight', 'price'], function (column) {
                    productRow[column] = '';
                });
            }

            if (productRow.status === '0') {
                productRow.status = '1';
            } else {
                productRow.status = '0';
            }
            this.render(this.variations);
        },

        /**
         * @param {String} rowIndex
         * @see use in matrix.phtml
         * @returns {This}
         */
        toggleList: function (rowIndex) {
            var state = false;

            if (rowIndex !== this.opened()) {
                state = rowIndex;
            }
            this.opened(state);

            return this;
        },

        /**
         * @param {String} rowIndex
         * @returns {This}
         */
        closeList: function (rowIndex) {
            if (this.opened() === rowIndex()) {
                this.opened(false);
            }

            return this;
        },

        /**
         * @param {Array} options
         * @returns {String}
         */
        getVariationKey: function (options) {
            return _.pluck(options, 'value').sort().join('-');
        },

        /**
         * @param {Array} options
         * @returns {{Object}|null}
         */
        getProductIdByOptions: function (options) {
            return this.productAttributesMap[this.getVariationKey(options)] || null;
        },

        /**
         * Initialize product attributes map.
         */
        initProductAttributesMap: function () {
            if (this.productAttributesMap === null) {
                this.productAttributesMap = {};
                _.each(this.variations, function (product) {
                    this.productAttributesMap[this.getVariationKey(product.options)] = product.productId;
                }.bind(this));
            }
        },

        /**
         * Is show preview image
         * @see use in matrix.phtml
         * @param {Object} variation
         * @returns {Boolean}
         */
        isShowPreviewImage: function (variation) {
            return variation.images.preview &&
                (!variation.editable || variation.images.file || variation.imageData && variation.imageData.url);
        },

        /**
         * @param {Object} variation
         * @returns {String}
         */
        getVariationImage: function (variation) {
            if (variation.imageData && variation.imageData.url) {
                return variation.imageData.url;
            }

            return variation.images.preview;
        },

        /**
         * @param {Object} variation
         * @see use in matrix.phtml
         * @returns {String}
         */
        generateImageGallery: function (variation) {
            var gallery = [],
                imageFields = ['position', 'file', 'disabled', 'label'];

            _.extend(variation, {
                'media_gallery': {
                    'images': {}
                },
                'imageTypes': {}
            });
            _.each(variation.images.images, function (image) {
                variation['media_gallery'].images[image['file_id']] = {};
                _.each(imageFields, function (field) {
                    gallery.push(
                        '<input type="hidden" name="' +
                        this.getVariationRowName(variation, 'media_gallery/images/' + image['file_id'] + '/' + field) +
                        '" value="' + (image[field] || '') + '" />'
                    );

                    variation['media_gallery'].images[image['file_id']][field] = image[field] || '';
                }, this);
                _.each(image.galleryTypes, function (imageType) {
                    gallery.push(
                        '<input type="hidden" name="' + this.getVariationRowName(variation, imageType) +
                        '" value="' + image.file + '" />'
                    );

                    variation.imageTypes[imageType] = image.file;
                }, this);
            }, this);

            return gallery.join('\n');
        },

        /**
         * @param {String} variationKey
         * @return {Object}
         */
        getVariationByKey: function (variationKey) {
            return _.find(this.variations, function (variation) {
                return variation.variationKey === variationKey;
            });
        },

        /**
         * Initialize image uploader for variations.
         */
        initImageUpload: function () {
            require([
                'mage/template',
                'jquery/file-uploader',
                'mage/mage',
                'mage/translate',
                'domReady!'
            ], function (mageTemplate) {
                var matrix = $('[data-role=product-variations-matrix]'),
                    variations = this;

                matrix.find('[data-action=upload-image]').find('[name=image]').each(function () {
                    var imageColumn = $(this).closest('[data-column=image]'),
                        rowIndex = $(this).parents('tr').attr('data-row-number'),
                        variation = variations.getVariationByKey(rowIndex);

                    if (imageColumn.find('[data-role=image]').length) {
                        imageColumn.find('[data-toggle=dropdown]').dropdown().show();
                    }
                    $(this).fileupload({
                        dataType: 'json',
                        dropZone: $(this).closest('[data-role=row]'),
                        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,

                        /**
                         * @param {Object} event
                         * @param {Object} data
                         */
                        done: function (event, data) {
                            var template, parentElement, uploaderControl, imageElement;

                            if (!data.result) {
                                return;
                            }

                            if (!data.result.error) {
                                parentElement = $(event.target).closest('[data-column=image]');
                                uploaderControl = parentElement.find('[data-action=upload-image]');
                                imageElement = parentElement.find('[data-role=image]');

                                variation.image = data.result.file;
                                variation.imageData = data.result;

                                if (imageElement.length) {
                                    imageElement.attr('src', data.result.url);
                                } else {
                                    template = mageTemplate(matrix.find('[data-template-for=variation-image]').html());

                                    $(template({
                                        data: data.result
                                    })).prependTo(uploaderControl);
                                }
                                parentElement.find('[name$="[image]"]').val(data.result.file);
                                parentElement.find('[data-toggle=dropdown]').dropdown().show();
                            } else {
                                alert({
                                    content: $.mage.__('We don\'t recognize or support this file extension type.')
                                });
                            }
                        },

                        /**
                         * @param {Object} event
                         */
                        start: function (event) {
                            $(event.target).closest('[data-action=upload-image]').addClass('loading');
                        },

                        /**
                         * @param {Object} event
                         */
                        stop: function (event) {
                            $(event.target).closest('[data-action=upload-image]').removeClass('loading');
                        }
                    });
                });
                matrix.find('[data-action=no-image]').click(function (event) {
                    var parentElement = $(event.target).closest('[data-column=image]'),
                        rowIndex = $(this).parents('tr').attr('data-row-number'),
                        variation = variations.getVariationByKey(rowIndex);

                    delete variation.image;
                    delete variation.imageData;
                    parentElement.find('[data-role=image]').remove();
                    parentElement.find('[name$="[image]"]').val('');
                    parentElement.find('[data-toggle=dropdown]').trigger('close.dropdown').hide();
                });
            }.bind(this));
        },

        /**
         * @param {Array} attributes
         */
        disableConfigurableAttributes: function (attributes) {
            $('[data-attribute-code] select.disabled-configurable-elements')
                .removeClass('disabled-configurable-elements')
                .prop('disabled', false);
            _.each(attributes(), function (attribute) {
                $('[data-attribute-code="' + attribute.code + '"] select')
                    .addClass('disabled-configurable-elements')
                    .prop('disabled', true);
            });
        },

        /**
         * Toggle configurable product price input.
         */
        showPrice: function () {
            var priceContainer = $('[id="attribute-price-container"]');

            if (this.productMatrix().length !== 0) {
                priceContainer.hide();
                priceContainer.find('input').prop('disabled', true);
            } else {
                priceContainer.show();
                priceContainer.find('input').prop('disabled', false);
            }
        },

        /**
         * Get currency symbol
         * @returns {String}
         */
        getCurrencySymbol: function () {
            return this.currencySymbol;
        }
    });
});
