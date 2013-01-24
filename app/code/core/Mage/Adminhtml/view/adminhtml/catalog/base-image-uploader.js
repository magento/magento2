/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*global media_gallery_contentJsObject*/
// @todo: refactor as widget
function BaseImageUploader(id, maxFileSize) {
    (function ($) {
        var $container = $('#' + id + '-container'),
            $template = $('#' + id + '-template'),
            $dropPlaceholder = $('#' + id + '-upload-placeholder'),
            images = $container.data('images'),
            mainImage = $container.data('main'),
            mainClass = 'base-image',
            currentImageCount = 0,
            maximumImageCount = 5,
            isInitialized = false;

        $container.on('add', function(event, data) {
            if (currentImageCount < maximumImageCount) {
                var $element = $template.tmpl(data);
                $element.insertBefore($dropPlaceholder)
                    .data('image', data);
                if (isInitialized && !currentImageCount) {
                    $.each('image,small_image,thumbnail'.split(','), function () {
                        if ($('input[name="product[' + this + ']"][value=no_selection]').is(':checked')) {
                            media_gallery_contentJsObject.imagesValues[this] = data.file;
                            if (this == 'image') {
                                mainImage = data.file;
                            }
                        }
                    });
                }
                if (data.file == mainImage) {
                    $element.addClass(mainClass);
                }
                currentImageCount++;
            }
            if (currentImageCount >= maximumImageCount) {
                $dropPlaceholder.hide();
            }
            $('input[name="product[name]"]').focus().blur(); // prevent just inserted image selection
        });

        $container.on('click', '.container', function (event) {
            $(this).toggleClass('active').siblings().removeClass('active');
        });
        $container.on('click', '.make-main', function (event) {
            var $imageContainer = $(this).closest('.container'),
                image = $imageContainer.data('image');

            $container.find('.container').removeClass(mainClass);
            $imageContainer.addClass(mainClass);
            mainImage = image.file;

            var $galleryContainer = $('#media_gallery_content_grid'),
                $currentImage = $galleryContainer.find('input[name="product[image]"]:checked'),
                $currentSmallImage = $galleryContainer.find('input[name="product[small_image]"]:checked'),
                $currentThumbnail = $galleryContainer.find('input[name="product[thumbnail]"]:checked'),
                radiosToSwitch = 'input[name="product[image]"]';
            if ($currentImage.attr('onclick') == $currentSmallImage.attr('onclick')
                && $currentImage.attr('onclick') == $currentThumbnail.attr('onclick')
            ) {
                radiosToSwitch += ',input[name="product[small_image]"],input[name="product[thumbnail]"]';
            }
            _getGalleryRowByImage(image).find(radiosToSwitch).trigger('click');
        });

        $container.on('click', '.close', function (event) {
            var $imageContainer = $(this).closest('.container'),
                image = $imageContainer.data('image'),
                $galleryRow = _getGalleryRowByImage(image);

            $galleryRow.find('.cell-remove input[type=checkbox]').prop('checked', true).trigger('click');
            $.each('image,small_image,thumbnail'.split(','), function () {
                if ($galleryRow.find('input[name="product[' + this + ']"]').is(':checked')) {
                    $('input[name="product[' + this + ']"][value=no_selection]').prop('checked', true).trigger('click');
                }
            });
            media_gallery_contentJsObject.updateImages();
            $imageContainer.remove();

            currentImageCount--;
            if (currentImageCount < maximumImageCount) {
                $dropPlaceholder.css('display', 'inline-block');
            }
        });

        function _getGalleryRowByImage(image)
        {
            var escapedFileName = image.file.replace(/([ #;&,.+*~\':"!^$[\]()=>|\/@])/g, '\\$1');
            return $('input[onclick*="\'' + escapedFileName + '\'"]').closest('tr');
        }

        $container.sortable({
            axis: 'x',
            handle: '.container'
        });

        $dropPlaceholder.on('click', function(e) {
            $('#' + id + '-upload').trigger(e);
        });
        $('#' + id + '-upload').fileupload({
            dataType: 'json',
            dropZone: $dropPlaceholder,
            acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
            maxFileSize: maxFileSize,
            done: function (event, data) {
                if (!data.result) {
                    return;
                }
                if (!data.result.error) {
                    $container.trigger('add', data.result);
                    if (typeof media_gallery_contentJsObject != 'undefined') {
                        media_gallery_contentJsObject.handleUploadComplete(data.result);
                        media_gallery_contentJsObject.updateImages();
                    }
                } else {
                    alert(jQuery.mage.__('File extension not known or unsupported type.'));
                }
            },
            add: function(event, data) {
                $(this).fileupload('process', data).done(function () {
                    data.submit();
                });
            }
        });

        $.each(images.items || [], function() {
            $container.trigger('add', this);
        });
        isInitialized = true;

        if ($('label[for=image]').text() == 'Base Image') {
            $('label[for=image]').text('Images');
        }
    })(jQuery);
}
