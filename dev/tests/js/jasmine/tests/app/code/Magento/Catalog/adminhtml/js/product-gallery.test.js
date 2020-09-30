/*
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*eslint-disable max-nested-callbacks*/
/*jscs:disable jsDoc*/
define([
    'jquery',
    'Magento_Catalog/js/product-gallery'
], function ($) {
    'use strict';

    var galleryEl,
        defaultConfig = {
            images: [
                {
                    disabled: 0,
                    file: '/e/a/earth.jpg',
                    position: 2,
                    url: 'http://localhost/media/catalog/product/e/a/earth.jpg',
                    size: 2048,
                    'value_id': 2
                },
                {
                    disabled: 0,
                    file: '/m/a/mars.jpg',
                    position: 3,
                    url: 'http://localhost/media/catalog/product/m/a/mars.jpg',
                    size: 3072,
                    'value_id': 3
                },
                {
                    disabled: 0,
                    file: '/j/u/jupiter.jpg',
                    position: 5,
                    size: 5120,
                    url: 'http://localhost/media/catalog/product/j/u/jupiter.jpg',
                    'value_id': 5
                }
            ],
            types: {
                'image': {
                    code: 'image',
                    label: 'Base',
                    name: 'product[image]'
                },
                'small_image': {
                    code: 'small_image',
                    label: 'Small',
                    name: 'product[image]'
                },
                'thumbnail': {
                    code: 'thumbnail',
                    label: 'Thumbnail',
                    name: 'product[image]'
                }
            }
        };

    function init(config) {
        $(galleryEl).productGallery($.extend({}, defaultConfig, config || {}));
    }

    beforeEach(function () {
        $('<form>' +
            '<div id="media_gallery_content" class="gallery">' +
                '<script id="media_gallery_content-template" data-template="image" type="text/x-magento-template">' +
                    '<div class="image item <% if(data.disabled == 1){ %>hidden-for-front<% } %>" data-role="image">' +
                        '<input type="hidden" name="product[media_gallery][images][<%- data.file_id %>][position]"' +
                        ' value="<%- data.position %>" data-form-part="product_form" class="position"/>' +
                        '<input type="hidden" name="product[media_gallery][images][<%- data.file_id %>][file]"' +
                        ' value="<%- data.file %>" data-form-part="product_form"/>' +
                        '<input type="hidden" name="product[media_gallery][images][<%- data.file_id %>][label]"' +
                        ' value="<%- data.label %>" data-form-part="product_form"/>' +
                        '<div class="product-image-wrapper">' +
                            '<img class="product-image" data-role="image-element" src="<%- data.url %>" alt=""/>' +
                            '<div class="actions"></div>' +
                        '</div>' +
                    '</div>' +
                '</script>' +
            '</div>' +
        '</form>'
        ).appendTo(document.body);
        galleryEl = document.getElementById('media_gallery_content');
    });

    afterEach(function () {
        $(galleryEl).remove();
        galleryEl = undefined;
    });

    describe('Magento_Catalog/js/product-gallery', function () {
        describe('_create()', function () {
            it('check that existing images are rendered correctly', function () {
                init();
                expect($(galleryEl).find('[data-role=image]').length).toBe(3);
                expect($(galleryEl).find('[data-role=image]:nth-child(1) .position').val()).toBe('2');
                expect($(galleryEl).find('[data-role=image]:nth-child(2) .position').val()).toBe('3');
                expect($(galleryEl).find('[data-role=image]:nth-child(3) .position').val()).toBe('5');
            });
        });
        describe('_addItem()', function () {
            it('check that new image is inserted at the first position if there were no existing images', function () {
                init({
                    images: []
                });
                $(galleryEl).trigger('addItem', {
                    file: '/s/a/saturn.jpg.tmp',
                    name: 'saturn.jpg',
                    size: 1024,
                    type: 'image/jpeg',
                    url: 'http://localhost/media/tmp/catalog/product/s/a/saturn.jpg'
                });
                expect($(galleryEl).find('[data-role=image]').length).toBe(1);
                expect($(galleryEl).find('[data-role=image]:nth-child(1) .position').val()).toBe('1');
            });
            it('check that new image is inserted at the last position if there were existing images', function () {
                init();
                $(galleryEl).trigger('addItem', {
                    file: '/s/a/saturn.jpg.tmp',
                    name: 'saturn.jpg',
                    size: 1024,
                    type: 'image/jpeg',
                    url: 'http://localhost/media/tmp/catalog/product/s/a/saturn.jpg'
                });
                expect($(galleryEl).find('[data-role=image]').length).toBe(4);
                // check that new image position is the position of previous image in the list plus one
                expect($(galleryEl).find('[data-role=image]:nth-child(4) .position').val()).toBe('6');
            });
        });
    });
});
