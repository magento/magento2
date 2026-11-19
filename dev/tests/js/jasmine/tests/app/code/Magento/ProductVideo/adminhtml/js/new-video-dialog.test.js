/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

/*eslint-disable max-nested-callbacks*/
/*jscs:disable jsDoc*/
define([
    'jquery',
    'Magento_ProductVideo/js/new-video-dialog',
    'Magento_Catalog/js/product-gallery'
], function ($, NewVideoDialog) {
    'use strict';

    describe('Magento_ProductVideo/js/new-video-dialog replacement position', function () {
        var gallery,
            dialog,
            originalImages;

        function images() {
            return gallery.find('[data-role=image]:not(.removed)');
        }

        function replace(index) {
            var oldImage = images().eq(index).data('imageData'),
                newImage = {
                    file: '/replacement.jpg',
                    oldFile: oldImage.file,
                    url: 'replacement.jpg',
                    size: 1024
                };

            dialog.element.find('.item-id').val('[' + oldImage.value_id + ']');
            dialog._replaceImage(oldImage.file, newImage.file, newImage);
        }

        beforeEach(function () {
            originalImages = [2, 3, 5].map(function (position) {
                return {
                    file: '/video-' + position + '.jpg',
                    position: position,
                    value_id: position,
                    size: 1024
                };
            });
            gallery = $('<div>' +
                '<form data-form="edit-product"></form>' +
                '<script data-template="image" type="text/x-magento-template">' +
                    '<div class="image item" data-role="image">' +
                        '<input class="position" value="<%- data.position %>"/>' +
                        '<input class="is-removed" value="0"/>' +
                        '<input name="product[media_gallery][images][<%- data.file_id %>][file]"' +
                            ' value="<%- data.file %>"/>' +
                        '<input name="product[media_gallery][images][<%- data.file_id %>][value_id]"' +
                            ' value="<%- data.value_id %>"/>' +
                    '</div>' +
                '</script>' +
            '</div>').appendTo(document.body).productGallery({images: originalImages, types: {}});
            dialog = Object.create(NewVideoDialog.prototype);
            dialog.element = $('<div><input class="item-id"/></div>').appendTo(document.body);
            dialog._gallery = gallery;
            dialog._itemIdSelector = '.item-id';
            dialog._images = {};
            originalImages.forEach(function (image) {
                dialog._images[image.file] = image;
            });
            dialog._addVideoClass = jasmine.createSpy('addVideoClass');
        });

        afterEach(function () {
            gallery.remove();
            dialog.element.remove();
        });

        it('keeps the middle slot through remove, add and setPosition with saved position gaps', function () {
            replace(1);

            expect(images().map(function () {
                return $(this).data('imageData').file;
            }).get()).toEqual(['/video-2.jpg', '/replacement.jpg', '/video-5.jpg']);
            expect(images().find('.position').map(function () {
                return $(this).val();
            }).get()).toEqual(['0', '1', '2']);
        });

        it('keeps the first surviving slot when an earlier image was already removed', function () {
            gallery.trigger('removeItem', originalImages[0]);
            replace(0);

            expect(images().map(function () {
                return $(this).data('imageData').file;
            }).get()).toEqual(['/replacement.jpg', '/video-5.jpg']);
            expect(images().find('.position').map(function () {
                return $(this).val();
            }).get()).toEqual(['0', '1']);
        });
    });
});
