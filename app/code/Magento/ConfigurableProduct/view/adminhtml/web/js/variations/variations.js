/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore'
], function (Component, $, ko, _) {
    'use strict';

    var viewModel;
    viewModel = Component.extend({
        variations: [],
        productAttributes: [],
        attributes: ko.observableArray([]),
        productMatrix: ko.observableArray([]),
        initialize: function () {
            this._super();
            if (this.variations.length) {
                this.attributes(this.productAttributes);
                _.each(this.variations, function(variation) {
                    this.populateVariationMatrix(
                        variation.options,
                        variation.images,
                        variation.sku,
                        variation.inventory,
                        variation.price,
                        variation.name,
                        variation.product_id
                    );
                }, this);
                this.render();
            }
        },
        getProductValue: function(name) {
            return $('[name="product[' + name + ']"]', this.productForm).val();
        },
        getRowId: function(data, field) {
            var key = data.variationKey;
            return 'variations-matrix-' + key + '-' + field;
        },
        getVariationRowName: function(data, field) {
            var key = data.variationKey;
            return 'variations-matrix[' + key + '][' + field.split('/').join('][') + ']';
        },
        getAttributeRowName: function(attribute, field) {
            return 'product[configurable_attributes_data][' + attribute.id  + '][' + field + ']';
        },
        getOptionRowName: function(attribute, option, field) {
            return 'product[configurable_attributes_data][' + attribute.id  + '][values][' + option.value + ']['
                + field + ']';
        },
        reset: function() {
            this.productMatrix([]);
        },
        render: function() {
            //$('[data-role=configurable-attributes-container]').html('');
            //$('[data-role=product-variations-matrix]').html($('[data-role=product-variations-matrix-tmp]').html());
            this.initImageUpload();

        },
        getAttributesOptions: function() {
            return this.productMatrix()[0].options;
        },
        showVariations: function() {
            return this.productMatrix().length > 0;
        },
        populateVariationMatrix: function(options, images, sku, inventory, price, name, productId) {
            var attributes = _.reduce(options, function (memo, option) {
                var attribute = {};
                attribute[option.attribute_code] = option.value;
                return _.extend(memo, attribute);
            }, {});
            this.productMatrix.push({
                product_id: productId || null,
                images: images,
                sku: sku,
                name: name || sku,
                inventory: inventory,
                price: price || this.getProductValue('price'),
                options: options,
                attribute: JSON.stringify(attributes),
                variationKey: _.values(attributes).join('-'),
                weight: this.getProductValue('weight'),
                readonly: productId > 0
            });
        },
        isReadonly: function(variation) {
            return variation.product_id !== null;
        },
        generateImageGallery: function(variation) {
            var gallery = [];
            var imageFields = ['position', 'file', 'disabled', 'label'];
            _.each(variation.images.images, function(image) {
                _.each(imageFields, function(field) {
                    gallery.push(
                        '<input type="hidden" name="'
                        + this.getVariationRowName(variation, 'media_gallery/images/' + image.file_id + '/' + field)
                        + '" value="' + (image[field] || '') + '" />'
                    );
                }, this);
                _.each(image.galleryTypes, function(imageType) {
                    gallery.push(
                        '<input type="hidden" name="' + this.getVariationRowName(variation, imageType)
                        + '" value="' + image.file + '" />'
                    );
                }, this);
            }, this);
            return gallery.join('\n');
        },
        initImageUpload: function() {
            require([
                "jquery",
                "mage/template",
                "jquery/file-uploader",
                "mage/mage",
                "mage/translate"
            ], function(jQuery, mageTemplate){

                jQuery(function ($) {
                    var matrix = $('[data-role=product-variations-matrix]');
                    matrix.find('[data-action=upload-image] [name=image]').each(function() {
                        $(this).fileupload({
                            dataType: 'json',
                            dropZone: $(this).closest('[data-role=row]'),
                            acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
                            done: function (event, data) {
                                var tmpl;

                                if (!data.result) {
                                    return;
                                }
                                if (!data.result.error) {
                                    var parentElement = $(event.target).closest('[data-column=image]'),
                                        uploaderControl = parentElement.find('[data-action=upload-image]'),
                                        imageElement = parentElement.find('[data-role=image]');

                                    if (imageElement.length) {
                                        imageElement.attr('src', data.result.url);
                                    } else {
                                        tmpl = mageTemplate(matrix.find('[data-template-for=variation-image]').html());

                                        $(tmpl({
                                            data: data.result
                                        })).prependTo(uploaderControl);
                                    }
                                    parentElement.find('[name$="[image]"]').val(data.result.file);
                                    parentElement.find('[data-toggle=dropdown]').dropdown().show();
                                } else {
                                    alert($.mage.__('We don\'t recognize or support this file extension type.'));
                                }
                            },
                            start: function(event) {
                                $(event.target).closest('[data-action=upload-image]').addClass('loading');
                            },
                            stop: function(event) {
                                $(event.target).closest('[data-action=upload-image]').removeClass('loading');
                            }
                        });
                    });
                    matrix.find('[data-action=no-image]').click(function (event) {
                        var parentElement = $(event.target).closest('[data-column=image]');
                        parentElement.find('[data-role=image]').remove();
                        parentElement.find('[name$="[image]"]').val('');
                        parentElement.find('[data-toggle=dropdown]').trigger('close.dropdown').hide();
                    });
                });
            });
        }
    });
    return viewModel;
});
