/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
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
        $(galleryEl).productGallery($.extend({}, $.extend(true, {}, defaultConfig), config || {}));
    }

    function images() {
        return $(galleryEl).find('[data-role=image]:not(.removed)');
    }

    function positions() {
        return images().find('.position').map(function () {
            return $(this).val();
        }).get();
    }

    function files(elements, key) {
        return elements.map(function () {
            return $(this).data(key).file;
        }).get();
    }

    function removeImage(index) {
        $(galleryEl).trigger('removeItem', images().eq(index).data('imageData'));
    }

    function upload() {
        $(galleryEl).trigger('addItem', {
            file: '/s/a/saturn.jpg.tmp',
            name: 'saturn.jpg',
            size: 1024,
            type: 'image/jpeg',
            url: 'http://localhost/media/tmp/catalog/product/s/a/saturn.jpg'
        });
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
                        '<input type="hidden" class="is-removed" value=""/>' +
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
        $(galleryEl).closest('form').remove();
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
        describe('templates without position classes', function () {
            beforeEach(function () {
                var template = $(galleryEl).find('[data-template=image]');

                template.html(template.html().replace(' class="position"', ''));
            });

            it('appends after the saved position when there is no position-class input', function () {
                init();
                upload();

                expect(images().last().data('imageData').position).toBe(6);
            });

            it('appends after position zero when there is no position-class input', function () {
                init({images: [$.extend({}, defaultConfig.images[0], {position: 0})]});
                upload();

                expect(images().last().data('imageData').position).toBe(1);
            });
        });
        describe('position changes', function () {
            it('renumbers survivors and keeps removed inputs marked for deletion', function () {
                var moved = jasmine.createSpy('moveElement'),
                    removed;

                init();
                removed = images().eq(1);
                $(galleryEl).on('moveElement', moved);
                removeImage(1);

                expect(positions()).toEqual(['0', '1']);
                expect(removed.find('.is-removed').val()).toBe('1');
                expect(removed.find('.position').val()).toBe('3');
                expect(moved.calls.allArgs().map(function (args) {
                    return args[1].position;
                })).toEqual([0, 1]);
                expect(images().last().data('imageData').position).toBe(1);
                expect($(galleryEl).productGallery('option', 'types').image.value).toBe('no_selection');
            });

            it('appends after survivors when the last DOM image was removed', function () {
                init();
                removeImage(2);
                upload();

                expect(positions()).toEqual(['0', '1', '2']);
                expect(files(images(), 'imageData')).toEqual([
                    '/e/a/earth.jpg', '/m/a/mars.jpg', '/s/a/saturn.jpg.tmp'
                ]);
                expect($(galleryEl).find('[data-role=image]').last().hasClass('removed')).toBe(true);
            });

            it('appends after the current position following a drag reorder', function () {
                var last;

                init();
                last = images().last();
                last.prependTo(galleryEl);
                $(galleryEl).trigger('resort');
                upload();

                expect(positions()).toEqual(['0', '1', '2', '3']);
                expect(files(images(), 'imageData')).toEqual([
                    '/j/u/jupiter.jpg', '/e/a/earth.jpg', '/m/a/mars.jpg', '/s/a/saturn.jpg.tmp'
                ]);
            });

            it('starts a new gallery after every image has been removed', function () {
                init();
                removeImage(0);
                removeImage(0);
                removeImage(0);
                upload();

                expect(positions()).toEqual(['1']);
                expect(images().data('imageData').file).toBe('/s/a/saturn.jpg.tmp');
            });

            it('uses surviving indexes for setPosition in both directions', function () {
                var first;

                init();
                removeImage(1);
                upload();
                first = images().first().data('imageData');
                $(galleryEl).trigger('setPosition', {imageData: first, position: 2});

                expect(files(images(), 'imageData')).toEqual([
                    '/j/u/jupiter.jpg', '/s/a/saturn.jpg.tmp', '/e/a/earth.jpg'
                ]);
                expect(positions()).toEqual(['0', '1', '2']);
                $(galleryEl).trigger('setPosition', {imageData: first, position: 0});
                expect(files(images(), 'imageData')).toEqual([
                    '/e/a/earth.jpg', '/j/u/jupiter.jpg', '/s/a/saturn.jpg.tmp'
                ]);
                expect(positions()).toEqual(['0', '1', '2']);
            });

            it('initializes a new widget from submitted position data', function () {
                var saved;

                init();
                removeImage(1);
                upload();
                saved = images().map(function () {
                    return $.extend({}, $(this).data('imageData'), {
                        position: Number($(this).find('.position').val())
                    });
                }).get();
                $(galleryEl).productGallery('destroy').find('[data-role=image]').remove();
                init({images: saved});

                expect(positions()).toEqual(['0', '1', '2']);
                expect(files(images(), 'imageData')).toEqual([
                    '/e/a/earth.jpg', '/j/u/jupiter.jpg', '/s/a/saturn.jpg.tmp'
                ]);
            });
        });

    });
});
