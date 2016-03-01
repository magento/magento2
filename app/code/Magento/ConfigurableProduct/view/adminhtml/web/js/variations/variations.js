// jscs:disable requireDotNotation
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
// jscs:disable jsDoc
define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore',
    'Magento_Ui/js/modal/alert',
    'uiRegistry'
], function (Component, $, ko, _, alert, registry) {
    'use strict';

    function UserException(message) {
        this.message = message;
        this.name = 'UserException';
    }
    UserException.prototype = Object.create(Error.prototype);

    return Component.extend({
        defaults: {
            opened: false,
            attributes: [],
            usedAttributes: [],
            attributesData: {},
            productMatrix: [],
            variations: [],
            productAttributes: [],
            disabledAttributes: [],
            fullAttributes: [],
            rowIndexToEdit: false,
            productAttributesMap: null,
            value: [],
            modules: {
                associatedProductGrid: '${ $.configurableProductGrid }',
                wizardButtonElement: '${ $.wizardModalButtonName }',
            },
            links: {
                value: '${ $.provider }:${ $.dataScopeVariations }',
                usedAttributes: '${ $.provider }:${ $.dataScopeAttributes }',
                attributesData: '${ $.provider }:${ $.dataScopeAttributesData }'

            }
        },
        initialize: function () {
            this._super();
            if (this.variations.length) {
                this.render(this.variations, this.productAttributes);
            }
            this.initProductAttributesMap();
        },
        initObservable: function () {
            this._super().observe('actions opened attributes productMatrix value usedAttributes attributesData');

            return this;
        },
        showGrid: function (rowIndex) {
            var product = this.productMatrix()[rowIndex],
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
        changeProduct: function (newProducts) {
            var oldProduct = this.productMatrix()[this.rowIndexToEdit],
                newProduct = this._makeProduct(_.extend(oldProduct, newProducts[0]));
            this.productAttributesMap[this.getVariationKey(newProduct.options)] = newProduct.productId;
            this.productMatrix.splice(this.rowIndexToEdit, 1, newProduct);
        },
        appendProducts: function (newProducts) {
            this.productMatrix.push.apply(
                this.productMatrix,
                _.map(
                    newProducts,
                    _.wrap(
                        this._makeProduct.bind(this),
                        function (func, product) {
                            var newProduct = func(product);
                            this.productAttributesMap[this.getVariationKey(newProduct.options)] = newProduct.productId;

                            return newProduct;
                        }.bind(this)
                    )
                )
            );
        },
        _makeProduct: function (product) {
            var productId = product['entity_id'] || product.productId || null,
                attributes = _.pick(product, this.attributes.pluck('code')),
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
                productUrl: this.buildProductUrl(productId),
                quantity: product.quantity || null,
                sku: product.sku,
                status: product.status === undefined ? 1 : parseInt(product.status, 10),
                variationKey: this.getVariationKey(options),
                weight: product.weight || null
            };
        },
        getProductValue: function (name) {
            name = name.split('/').join('][');

            return $('[name="product[' + name + ']"]:enabled:not(.ignore-validate)', this.productForm).val();
        },
        getRowId: function (data, field) {
            var key = data.variationKey;

            return 'variations-matrix-' + key + '-' + field;
        },
        getVariationRowName: function (variation, field) {
            var result;

            if (variation.productId) {
                result = 'configurations[' + variation.productId + '][' + field.split('/').join('][') + ']';
            } else {
                result = 'variations-matrix[' + variation.variationKey + '][' + field.split('/').join('][') + ']';
            }

            return result;
        },
        render: function (variations, attributes) {
            this.changeButtonWizard();
            this.populateVariationMatrix(variations);
            this.attributes(attributes);
            this.disableConfigurableAttributes(attributes);
            this.handleValue(variations);
            this.handleAttributes();
        },
        changeButtonWizard: function () {
            this.wizardButtonElement().title(this.wizardModalButtonTitle);
        },
        handleValue: function (variations) {
            var tmpArray = [];

            _.each(variations, function (variation) {
                var attributes = _.reduce(variation.options, function (memo, option) {
                    var attribute = {};
                    attribute[option['attribute_code']] = option.value;

                    return _.extend(memo, attribute);
                }, {});
                tmpArray.push(_.extend(variation, {
                    productId: variation.productId || null,
                    name: variation.name || variation.sku,
                    priceCurrency: this.currencySymbol,
                    weight: variation.weight,
                    attribute: JSON.stringify(attributes),
                    variationKey: this.getVariationKey(variation.options),
                    editable: variation.editable === undefined ? !variation.productId : variation.editable,
                    productUrl: this.buildProductUrl(variation.productId),
                    status: variation.status === undefined ? 1 : parseInt(variation.status, 10)
                }));
            }, this);

            this.value(tmpArray);
        },
        handleAttributes: function () {
            var tmpArray = [];
            var tmpOptions = {};
            var option = {};
            var position = 0;
            var values = {};

            _.each(this.attributes(), function (attribute) {
                tmpArray.push(attribute.id);
                values = {};
                _.each(attribute.chosen, function (row) {
                    values[row.value] = {
                        "include": "1",
                        "value_index": row.value
                    };
                }, this);
                option = {
                    "attribute_id": attribute.id,
                    "code": attribute.code,
                    "label": attribute.label,
                    "position": position,
                    "values": values
                };
                tmpOptions[attribute.id] = option;
                position++;
            }, this);

            this.attributesData(tmpOptions);
            this.usedAttributes(tmpArray);
        },


        /**
         * Get attributes options
         * @see use in matrix.phtml
         * @function
         * @event
         * @returns {array}
         */
        getAttributesOptions: function () {
            return this.showVariations() ? this.productMatrix()[0].options : [];
        },
        showVariations: function () {
            return this.productMatrix().length > 0;
        },
        populateVariationMatrix: function (variations) {
            this.productMatrix([]);
            _.each(variations, function (variation) {
                var attributes = _.reduce(variation.options, function (memo, option) {
                    var attribute = {};
                    attribute[option['attribute_code']] = option.value;

                    return _.extend(memo, attribute);
                }, {});
                this.productMatrix.push(_.extend(variation, {
                    productId: variation.productId || null,
                    name: variation.name || variation.sku,
                    weight: variation.weight,
                    attribute: JSON.stringify(attributes),
                    variationKey: this.getVariationKey(variation.options),
                    editable: variation.editable === undefined ? !variation.productId : variation.editable,
                    productUrl: this.buildProductUrl(variation.productId),
                    status: variation.status === undefined ? 1 : parseInt(variation.status, 10)
                }));
            }, this);
        },
        buildProductUrl: function (productId) {
            return this.productUrl.replace('%id%', productId);
        },
        removeProduct: function (rowIndex) {
            this.opened(false);
            var removedProduct = this.productMatrix.splice(rowIndex, 1);
            delete this.productAttributesMap[this.getVariationKey(removedProduct[0].options)];

            if (this.productMatrix().length === 0) {
                this.attributes.each(function (attribute) {
                    $('[data-attribute-code="' + attribute.code + '"] select').removeProp('disabled');
                });
            }
            this.showPrice();
        },
        toggleProduct: function (rowIndex) {
            var product, row, productChanged = {};

            if (this.productMatrix()[rowIndex].editable) {
                row = $('[data-row-number=' + rowIndex + ']');
                _.each(['name','sku','qty','weight','price'], function (column) {
                    productChanged[column] = $(
                        'input[type=text]',
                        row.find($('[data-column="%s"]'.replace('%s', column)))
                    ).val();
                });
            }
            product = this.productMatrix.splice(rowIndex, 1)[0];
            product = _.extend(product, productChanged);
            product.status = +!product.status;
            this.productMatrix.splice(rowIndex, 0, product);
        },
        toggleList: function (rowIndex) {
            var state = false;

            if (rowIndex !== this.opened()) {
                state = rowIndex;
            }
            this.opened(state);

            return this;
        },
        closeList: function (rowIndex) {
            if (this.opened() === rowIndex()) {
                this.opened(false);
            }

            return this;
        },
        getVariationKey: function (options) {
            return _.pluck(options, 'value').sort().join('-');
        },
        getProductIdByOptions: function (options) {
            return this.productAttributesMap[this.getVariationKey(options)] || null;
        },
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
         * @function
         * @event
         * @param {object} variation
         * @returns {*|boolean}
         */
        isShowPreviewImage: function (variation) {
            return variation.images.preview && (!variation.editable || variation.images.file);
        },
        generateImageGallery: function (variation) {
            var gallery = [],
                imageFields = ['position', 'file', 'disabled', 'label'];
            _.each(variation.images.images, function (image) {
                _.each(imageFields, function (field) {
                    gallery.push(
                        '<input type="hidden" name="' +
                        this.getVariationRowName(variation, 'media_gallery/images/' + image['file_id'] + '/' + field) +
                        '" value="' + (image[field] || '') + '" />'
                    );
                }, this);
                _.each(image.galleryTypes, function (imageType) {
                    gallery.push(
                        '<input type="hidden" name="' + this.getVariationRowName(variation, imageType) +
                        '" value="' + image.file + '" />'
                    );
                }, this);
            }, this);

            return gallery.join('\n');
        },
        disableConfigurableAttributes: function (attributes) {
            var element;
            _.each(this.disabledAttributes, function (attribute) {
                registry.get('index = ' + attribute).disabled(false);
            });
            this.disabledAttributes = [];

            _.each(attributes, function (attribute) {
                element = registry.get('index = ' + attribute.code);
                if (!_.isUndefined(element)) {
                    element.disabled(true);
                    this.disabledAttributes.push(attribute.code);
                }
            }, this);
        },

        /**
         * Get currency symbol
         * @returns {*}
         */
        getCurrencySymbol: function () {
            return this.currencySymbol;
        }
    });
});
