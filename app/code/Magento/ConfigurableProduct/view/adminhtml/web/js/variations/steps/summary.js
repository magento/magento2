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
        sections: ko.observableArray([]),
        attributes: ko.observableArray([]),
        grid: ko.observableArray([]),
        attributesName: ko.observableArray([]),
        productMatrix: ko.observableArray([]),
        nextLabel: $.mage.__('Generate Products'),
        productForm: $('[data-form=edit-product]'),
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
            return 'product[configurable_attributes_data][' + attribute.id  + '][values][' + option.value + '][' + field + ']';
        },
        /**
         * @param attributes example [['b1', 'b2'],['a1', 'a2', 'a3'],['c1', 'c2', 'c3'],['d1']]
         * @returns {*} example [['b1','a1','c1','d1'],['b1','a1','c2','d1']...]
         */
        generateVariation: function (attributes) {
            return _.reduce(attributes, function(matrix, attribute) {
                var tmp = [];
                _.each(matrix, function(variations){
                    _.each(attribute.chosen, function(option){
                        option.attribute_code = attribute.code;
                        option.attribute_label = attribute.label;
                        tmp.push(_.union(variations, [option]));
                    });
                });
                if (!tmp.length) {
                    return _.map(attribute.chosen, function(option){
                        option.attribute_code = attribute.code;
                        option.attribute_label = attribute.label;
                        return [option];
                    });
                }
                return tmp;
            }, []);
        },
        generateGrid: function (variations, getSectionValue) {
            //['a1','b1','c1','d1'] option = {label:'a1', value:'', section:{img:'',inv:'',pri:''}}
            var productName = this.getProductValue('name');
            this.productMatrix([]);
            return _.map(variations, function (options) {
                var variation = [], images, sku, inventory, price;
                images = getSectionValue('images', options);
                variation.push(images);
                sku = _.reduce(options, function (memo, option) {
                    return productName + memo + '-' + option.label;
                }, '');
                variation.push(sku);
                inventory = getSectionValue('inventory', options);
                variation.push(inventory);
                //attributes
                _.each(options, function (option) {
                    variation.push(option.label);
                });
                price = getSectionValue('pricing', options);
                variation.push(price);
                this.populateVariationMatrix(options, images, sku, inventory, price);
                return variation;
            }, this);
        },
        populateVariationMatrix: function(options, images, sku, inventory, price) {
            var attributes = _.reduce(options, function (memo, option) {
                var attribute = {};
                attribute[option.attribute_code] = option.value;
                return _.extend(memo, attribute);
            }, {});
            this.productMatrix.push({
                associated_product_ids: null,
                images: images,
                sku: sku,
                inventory: inventory,
                price: price || this.getProductValue('price'),
                options: options,
                attribute: JSON.stringify(attributes),
                variationKey: _.values(attributes).join('-'),
                weight: this.getProductValue('weight')
            });
        },
        render: function (wizard) {
            this.sections(wizard.data.sections());
            this.attributes(wizard.data.attributes());

            this.attributesName([$.mage.__('Images'), $.mage.__('SKU'), $.mage.__('Inventory'), $.mage.__('Price')]);
            this.attributes.each(function (attribute, index) {
                this.attributesName.splice(3 + index, 0, attribute.label);
            }, this);

            this.grid(this.generateGrid(this.generateVariation(this.attributes()), wizard.data.sectionHelper));
        },
        force: function (wizard) {
            var $dialog = $('[data-role=step-wizard-dialog]');
            var $form = $('[data-form=edit-product]');
            if (!$form.valid()) {
                $form.data('validator').focusInvalid();
                $dialog.trigger('closeModal');
                return;
            }
            $('[data-role=configurable-attributes-container]').html('');
            $('[data-role=product-variations-matrix]').html($('[data-role=product-variations-matrix-tmp]').html());
            this.initImageUpload();
            $dialog.trigger('closeModal');
        },
        back: function (wizard) {
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
                                    parentElement.find('[data-toggle=dropdown]').show();
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
