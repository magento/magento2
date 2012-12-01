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
 * @category    frontend image zoom
 * @package     mage
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint evil:true browser:true jquery:true*/
(function ($) {
    $(document).ready(function () {
        // Default zoom variables
        var zoomInit = {
            imageSelector: '#image',
            sliderSelector: '#slider',
            sliderSpeed: 10,
            zoomNoticeSelector: '#track_hint',
            zoomInSelector: '#zoom_in',
            zoomOutSelector: '#zoom_out'
        };
        $.mage.event.trigger("mage.zoom.initialize", zoomInit);

        var slider, intervalId = null;
        var sliderMax = $(zoomInit.sliderSelector).width();
        var image = $(zoomInit.imageSelector);
        var imageWidth = image.width();
        var imageHeight = image.height();
        var imageParent = image.parent();
        var imageParentWidth = imageParent.width();
        var imageParentHeight = imageParent.height();
        var ceilingZoom, imageInitTop, imageInitLeft;
        var showFullImage = false;

        // Image is small than parent container, no need to see full picutre or zoom slider
        if (imageWidth < imageParentWidth && imageHeight < imageParentHeight) {
            $(zoomInit.sliderSelector).parent().hide();
            $(zoomInit.zoomNoticeSelector).hide();
            return;
        }
        // Resize Image to fit parent container
        if (imageWidth > imageHeight) {
            ceilingZoom = imageWidth / imageParentWidth;
            image.width(imageParentWidth);
            image.css('top', ((imageParentHeight - image.height()) / 2) + 'px');
        } else {
            ceilingZoom = imageHeight / imageParentHeight;
            image.height(imageParentHeight);
            image.css('left', ((imageParentWidth - image.width()) / 2) + 'px');
        }
        // Remember Image original position
        imageInitTop = image.position().top;
        imageInitLeft = image.position().left;

        // Make Image Draggable
        function draggableImage() {
            var topX = image.offset().left,
                topY = image.offset().top,
                bottomX = image.offset().left,
                bottomY = image.offset().top;
            // Calculate x offset if image width is greater than image container width
            if (image.width() > imageParentWidth) {
                topX = image.width() - (imageParent.offset().left - image.offset().left) - imageParentWidth;
                topX = image.offset().left - topX;
                bottomX = imageParent.offset().left - image.offset().left;
                bottomX = image.offset().left + bottomX;
            }
            // Calculate y offset if image height is greater than image container height
            if (image.height() > imageParentHeight) {
                topY = image.height() - (imageParent.offset().top - image.offset().top) - imageParentHeight;
                topY = image.offset().top - topY;
                bottomY = imageParent.offset().top - image.offset().top;
                bottomY = image.offset().top + bottomY;
            }
            // containment field is used because image is larger than parent container
            $(zoomInit.imageSelector).draggable({
                containment: [topX, topY, bottomX, bottomY],
                scroll: false
            });
        }

        // Image zooming bases on slider position
        function zoom(sliderPosition, sliderLength) {
            var ratio = sliderPosition / sliderLength;
            ratio = ratio > 1 ? 1 : ratio;
            var imageOldLeft = image.position().left;
            var imageOldTop = image.position().top;
            var imageOldWidth = image.width();
            var imageOldHeight = image.height();
            var overSize = (imageWidth > imageParentWidth || imageHeight > imageParentHeight);
            var floorZoom = 1;
            var imageZoom = floorZoom + (ratio * (ceilingZoom - floorZoom));
            // Zoomed image is larger than container, and resize image based on zoom ratio
            if (overSize) {
                if (imageWidth > imageHeight) {
                    image.width(imageZoom * imageParentWidth);
                } else {
                    image.height(imageZoom * imageParentHeight);
                }
            } else {
                $(zoomInit.sliderSelector).hide();
            }
            // Position zoomed image properly
            var imageNewLeft = imageOldLeft - (image.width() - imageOldWidth) / 2;
            var imageNewTop = imageOldTop - (image.height() - imageOldHeight) / 2;
            // Image can't be positioned more left than original left
            if (imageNewLeft > imageInitLeft || image.width() < imageParentWidth) {
                imageNewLeft = imageInitLeft;
            }
            // Image can't be positioned more right than the difference between parent width and image current width
            if (Math.abs(imageNewLeft) > Math.abs(imageParentWidth - image.width())) {
                imageNewLeft = imageParentWidth - image.width();
            }
            // Image can't be positioned more down than original top
            if (imageNewTop > imageInitTop || image.height() < imageParentHeight) {
                imageNewTop = imageInitTop;
            }
            // Image can't be positioned more top than the difference between parent height and image current height
            if (Math.abs(imageNewTop) > Math.abs(imageParentHeight - image.height())) {
                imageNewTop = imageParentHeight - image.height();
            }
            image.css('left', imageNewLeft + 'px');
            image.css('top', imageNewTop + 'px');
            // Because image size and position changed, we need to call recalculate draggable image containment
            draggableImage();
        }

        // Slide slider to zoom in or out the picture
        slider = $(zoomInit.sliderSelector).slider({
            value: 0,
            min: 0,
            max: sliderMax,
            slide: function (event, ui) {
                zoom(ui.value, sliderMax);
            },
            change: function (event, ui) {
                zoom(ui.value, sliderMax);
            }
        });

        // Mousedown on zoom in icon to zoom in picture
        $(zoomInit.zoomInSelector).on('mousedown',function () {
            intervalId = setInterval(function () {
                slider.slider('value', slider.slider('value') + 1);
            }, zoomInit.sliderSpeed);
        }).on('mouseup mouseleave', function () {
                clearInterval(intervalId);
            });

        // Mousedown on zoom out icon to zoom out picture
        $(zoomInit.zoomOutSelector).on('mousedown',function () {
            intervalId = setInterval(function () {
                slider.slider('value', slider.slider('value') - 1);
            }, zoomInit.sliderSpeed);
        }).on('mouseup mouseleave', function () {
                clearInterval(intervalId);
            });

        // Double-click image to see full picture
        $(zoomInit.imageSelector).on('dblclick', function () {
            showFullImage = !showFullImage;
            var ratio = showFullImage ? sliderMax : slider.slider('value');
            zoom(ratio, sliderMax);
            if (showFullImage) {
                $(zoomInit.sliderSelector).hide();
                $(zoomInit.zoomInSelector).hide();
                $(zoomInit.zoomOutSelector).hide();
                imageParent.css('overflow', 'visible');
                imageParent.css('zIndex', '1000');
            } else {
                $(zoomInit.sliderSelector).show();
                $(zoomInit.zoomInSelector).show();
                $(zoomInit.zoomOutSelector).show();
                imageParent.css('overflow', 'hidden');
                imageParent.css('zIndex', '9');
            }
        });

        // Window resize will change offset for draggable
        $(window).resize(draggableImage);
    });
}(jQuery));