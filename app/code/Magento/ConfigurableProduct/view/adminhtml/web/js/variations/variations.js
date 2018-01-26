/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore',
    'Magento_Ui/js/modal/alert',
    'uiRegistry',
    'mage/translate'
], function (Component, $, ko, _, alert, registry, $t) {
    'use strict';

    /**
     * @param {*} message
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
            usedAttributes: [],
            attributeCodes: [],
            attributesData: {},
            productMatrix: [],
            variations: [],
            formSaveParams: [],
            productAttributes: [],
            disabledAttributes: [],
            fullAttributes: [],
            rowIndexToEdit: false,
            productAttributesMap: null,
            value: [],
            modules: {
                associatedProductGrid: '${ $.configurableProductGrid }',
                wizardButtonElement: '${ $.wizardModalButtonName }',
                formElement: '${ $.formName }',
                attributeSetHandlerModal: '${ $.attributeSetHandler }'
            },
            imports: {
                attributeSetName: '${ $.provider }:configurableNewAttributeSetName',
                attributeSetId: '${ $.provider }:configurableExistingAttributeSetId',
                attributeSetSelection: '${ $.provider }:configurableAffectedAttributeSet',
                productPrice: '${ $.provider }:data.product.price'
            },
            links: {
                value: '${ $.provider }:${ $.dataScopeVariations }',
                usedAttributes: '${ $.provider }:${ $.dataScopeAttributes }',
                attributesData: '${ $.provider }:${ $.dataScopeAttributesData }',
                attributeCodes: '${ $.provider }:${ $.dataScopeAttributeCodes }',
                skeletonAttributeSet: '${ $.provider }:data.new-variations-attribute-set-id'
            }
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();

            this.changeButtonWizard();
            this.initProductAttributesMap();
            this.disableConfigurableAttributes(this.productAttributes);
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super().observe(
                'actions opened attributes productMatrix value usedAttributes attributesData attributeCodes'
            );

            return this;
        },

        /**
         * @param {Object} product
         * @return {Object}
         * @private
         */
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
                price: parseFloat(Math.round(product.price.replace(/[^\d.]+/g, '') + 'e+4') + 'e-4').toFixed(4),
                productId: productId,
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
         * @return {String|Number|Array}
         */
        getProductValue: function (name) {
            name = name.split('/').join('][');

            return $('[name="product[' + name + ']"]:enabled:not(.ignore-validate)', this.productForm).val();
        },

        /**
         * @param {Object} data
         * @param {String} field
         * @return {String}
         */
        getRowId: function (data, field) {
            var key = data.variationKey;

            return 'variations-matrix-' + key + '-' + field;
        },

        /**
         * @param {Object} variation
         * @param {String} field
         * @return {String}
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
         * @param {*} variations
         * @param {*} attributes
         */
        render: function (variations, attributes) {
            this.changeButtonWizard();
            this.populateVariationMatrix(variations);
            this.attributes(attributes);
            this.disableConfigurableAttributes(attributes);
            this.handleValue(variations);
            this.handleAttributes();
        },

        /**
         * Change button wizard.
         */
        changeButtonWizard: function () {
            if (this.variations.length) {
                this.wizardButtonElement().title(this.wizardModalButtonTitle);
            }
        },

        /**
         * @param {Array} variations
         */
        handleValue: function (variations) {
            var tmpArray = [];

            _.each(variations, function (variation) {
                var attributes = _.reduce(variation.options, function (memo, option) {
                    var attribute = {};

                    attribute[option['attribute_code']] = option.value;

                    return _.extend(memo, attribute);
                }, {}),
                    gallery = {
                        images: {}
                    },
                    types = {};

                _.each(variation.images.images, function (image) {
                    gallery.images[image['file_id']] = {
                        position: image.position,
                        file: image.file,
                        disabled: image.disabled,
                        label: image.label || ''
                    };
                    _.each(image.galleryTypes, function (type) {
                        types[type] = image.file;
                    });
                }, this);

                tmpArray.push(_.extend(variation, types, {
                    productId: variation.productId || null,
                    name: variation.name || variation.sku,
                    priceCurrency: this.currencySymbol,
                    weight: variation.weight,
                    attribute: JSON.stringify(attributes),
                    variationKey: this.getVariationKey(variation.options),
                    editable: variation.editable === undefined ? 0 : 1,
                    productUrl: this.buildProductUrl(variation.productId),
                    status: variation.status === undefined ? 1 : parseInt(variation.status, 10),
                    newProduct: variation.productId ? 0 : 1,
                    'media_gallery': gallery
                }));
            }, this);

            this.value(tmpArray);
        },

        /**
         * Handle attributes.
         */
        handleAttributes: function () {
            var tmpArray = [],
                codesArray = [],
                tmpOptions = {},
                option = {},
                position = 0,
                values = {};

            _.each(this.attributes(), function (attribute) {
                tmpArray.push(attribute.id);
                codesArray.push(attribute.code);
                values = {};
                _.each(attribute.chosen, function (row) {
                    values[row.value] = {
                        'include': '1',
                        'value_index': row.value
                    };
                }, this);
                option = {
                    'attribute_id': attribute.id,
                    'code': attribute.code,
                    'label': attribute.label,
                    'position': position,
                    'values': values
                };
                tmpOptions[attribute.id] = option;
                position++;
            }, this);

            this.attributesData(tmpOptions);
            this.usedAttributes(tmpArray);
            this.attributeCodes(codesArray);
        },

        /**
         * Get attributes options
         * @see use in matrix.phtml
         *
         * @returns {Array}
         */
        getAttributesOptions: function () {
            return this.showVariations() ? this.productMatrix()[0].options : [];
        },

        /**
         * @return {Boolean}
         */
        showVariations: function () {
            return this.productMatrix().length > 0;
        },

        /**
         * @param {Array} variations
         */
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

        /**
         * @param {*} productId
         */
        buildProductUrl: function (productId) {
            return this.productUrl.replace('%id%', productId);
        },

        /**
         * @param {Object} options
         * @return {String}
         */
        getVariationKey: function (options) {
            return _.pluck(options, 'value').sort().join('-');
        },

        /**
         * @param {*} options
         * @return {*|null}
         */
        getProductIdByOptions: function (options) {
            return this.productAttributesMap[this.getVariationKey(options)] || null;
        },

        /**
         * Init product attributes map
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
         * @param {Array} attributes
         */
        disableConfigurableAttributes: function (attributes) {
            var element;

            _.each(this.disabledAttributes, function (attribute) {
                registry.get('code = ' + attribute, 'index = ' + attribute).disabled(false);
            });
            this.disabledAttributes = [];

            _.each(attributes, function (attribute) {
                element = registry.get('code = ' + attribute.code, 'index = ' + attribute.code);

                if (!_.isUndefined(element)) {
                    element.disabled(true);
                    this.disabledAttributes.push(attribute.code);
                }
            }, this);
        },

        /**
         * Get currency symbol
         * @returns {String}
         */
        getCurrencySymbol: function () {
            return this.currencySymbol;
        },

        /**
         * Chose action for the form save button
         */
        saveFormHandler: function () {
            this.serializeData();

            if (this.checkForNewAttributes()) {
                this.formSaveParams = arguments;
                this.attributeSetHandlerModal().openModal();
            } else {
                this.formElement().save(arguments[0], arguments[1]);
            }
        },

        /**
         * Serialize data for specific form fields
         *
         * Get data from outdated fields, serialize it and produce new form fields.
         *
         * Outdated fields:
         *   - configurable-matrix;
         *   - associated_product_ids.
         *
         * New fields:
         *   - configurable-matrix-serialized;
         *   - associated_product_ids_serialized.
         */
        serializeData: function () {
            this.source.data['configurable-matrix-serialized'] =
                JSON.stringify(this.source.data['configurable-matrix']);

            delete this.source.data['configurable-matrix'];

            this.source.data['associated_product_ids_serialized'] =
                JSON.stringify(this.source.data['associated_product_ids']);

            delete this.source.data['associated_product_ids'];
        },

        /**
         * Check for newly added attributes
         * @returns {Boolean}
         */
        checkForNewAttributes: function () {
            var element, newAttributes = false;

            _.each(this.source.get('data.attribute_codes'), function (attribute) {
                element = registry.get('index = ' + attribute);

                if (_.isUndefined(element)) {
                    newAttributes = true;
                }
            }, this);

            return newAttributes;
        },

        /**
         * New attributes handler
         * @returns {Boolean}
         */
        addNewAttributeSetHandler: function () {
            var choosenAttributeSetOption;

            this.formElement().validate();

            if (this.formElement().source.get('params.invalid') === false) {
                choosenAttributeSetOption = this.attributeSetSelection;

                if (choosenAttributeSetOption === 'new') {
                    this.createNewAttributeSet();

                    return false;
                }

                if (choosenAttributeSetOption === 'existing') {
                    this.set(
                        'skeletonAttributeSet',
                        this.attributeSetId
                    );
                }

                this.closeDialogAndProcessForm();

                return true;
            }
        },

        /**
         * Handles new attribute set creation
         * @returns {Boolean}
         */
        createNewAttributeSet: function () {
            var messageBoxElement = registry.get('index = affectedAttributeSetError');

            messageBoxElement.visible(false);

            $.ajax({
                type: 'POST',
                url: this.attributeSetCreationUrl,
                data: {
                    gotoEdit: 1,
                    'attribute_set_name': this.attributeSetName,
                    'skeleton_set': this.skeletonAttributeSet,
                    'return_session_messages_only': 1
                },
                dataType: 'json',
                showLoader: true,
                context: this
            }).success(function (data) {
                if (!data.error) {
                    this.set(
                        'skeletonAttributeSet',
                        data.id
                    );
                    messageBoxElement.content(data.messages);
                    messageBoxElement.visible(true);
                    this.closeDialogAndProcessForm();
                } else {
                    messageBoxElement.content(data.messages);
                    messageBoxElement.visible(true);
                }

                return false;
            }).error(function (xhr) {
                if (xhr.statusText === 'abort') {
                    return;
                }

                alert({
                    content: $t('Something went wrong.')
                });
            });

            return false;
        },

        /**
         * Closes attribute set handler modal and process product form
         */
        closeDialogAndProcessForm: function () {
            this.attributeSetHandlerModal().closeModal();
            this.formElement().save(this.formSaveParams[0], this.formSaveParams[1]);
        },

        /**
         * Retrieves product price
         * @returns {*}
         */
        getProductPrice: function () {
            return this.productPrice;
        }
    });
});
