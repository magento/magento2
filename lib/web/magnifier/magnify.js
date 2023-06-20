/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'magnifier/magnifier'
], function ($, _) {
    'use strict';

    return function (config, element) {

        var isTouchEnabled = 'ontouchstart' in document.documentElement,
            gallerySelector = '[data-gallery-role="gallery"]',
            magnifierSelector = '[data-gallery-role="magnifier"]',
            magnifierZoomSelector = '[data-gallery-role="magnifier-zoom"]',
            zoomInButtonSelector = '[data-gallery-role="fotorama__zoom-in"]',
            zoomOutButtonSelector = '[data-gallery-role="fotorama__zoom-out"]',
            fullscreenImageSelector = '[data-gallery-role="stage-shaft"] [data-active="true"] .fotorama__img--full',
            imageDraggableClass = 'fotorama__img--draggable',
            imageZoommable = 'fotorama__img--zoommable',
            zoomInLoaded = 'zoom-in-loaded',
            zoomOutLoaded = 'zoom-out-loaded',
            zoomInDisabled = 'fotorama__zoom-in--disabled',
            zoomOutDisabled = 'fotorama__zoom-out--disabled',
            keyboardNavigation,
            videoContainerClass = 'fotorama-video-container',
            hideMagnifier,
            dragFlag,
            endX,
            transitionEnabled,
            transitionActive = false,
            tapFlag = 0,
            allowZoomOut = false,
            allowZoomIn = true;

        transitionEnabled = document.documentElement.style.transition !== undefined ||
            document.documentElement.style.WebkitTransition !== undefined ||
            document.documentElement.style.MozTransition !== undefined ||
            document.documentElement.style.MsTransition !== undefined ||
            document.documentElement.style.OTransition !== undefined;

        /**
         * Return width and height of original image
         * @param img original image node
         * @returns {{rw: number, rh: number}}
         */
        function getImageSize(img) {
            return {
                rw: img.naturalWidth,
                rh: img.naturalHeight
            };
        }

        /**
         * Sets min-height and min-width for image to avoid transition bug
         * @param $image - fullscreen image
         */
        function calculateMinSize($image) {

            var minHeight,
                minWidth,
                height = $image.height(),
                width = $image.width(),
                parentHeight = $image.parent().height(),
                parentWidth = $image.parent().width();

            if (width > parentWidth || height > parentHeight) {

                if (width / height < parentWidth / parentHeight) {
                    minHeight = parentHeight;
                    minWidth = width * (parentHeight / height);
                } else {
                    minWidth = parentWidth;
                    minHeight = height * parentWidth / width;
                }
                $image.css({
                    'min-width': minWidth,
                    'min-height': minHeight
                });
            }
        }

        function toggleZoomable($image, flag) {
            if (flag) {
                $image.css({
                    'min-width': $image.width(),
                    'min-height': $image.height(),
                    'width': $image.width(),
                    'height': $image.height()
                }).addClass(imageZoommable);
            } else {
                $image.css({
                    width: '',
                    height: '',
                    top: '',
                    left: '',
                    right: '',
                    bottom: ''
                }).removeClass(imageZoommable);
                calculateMinSize($image);
            }
        }

        function resetVars($image) {
            allowZoomIn = true;
            allowZoomOut = dragFlag = transitionActive = false;
            $image.hasClass(imageDraggableClass) && $image.removeClass(imageDraggableClass);
            toggleZoomable($image, false);
        }

        /**
         * Set state for zoom controls.
         * If state is true, zoom controls will be visible.
         * IF state is false, zoom controls will be hidden.
         * @param isHide
         */
        function hideZoomControls(isHide) {
            if (isHide) {
                $(zoomInButtonSelector).addClass(zoomInDisabled);
                $(zoomOutButtonSelector).addClass(zoomOutDisabled);
            } else {
                $(zoomInButtonSelector).removeClass(zoomInDisabled);
                $(zoomOutButtonSelector).removeClass(zoomOutDisabled);
            }
        }

        /**
         * Asynchronus control visibility of zoom buttons.
         * If image bigger than her wrapper. Zoom controls must visible.
         * @param path - image source path
         * @param $image
         */
        function asyncToggleZoomButtons(path, $image) {
            var img = new Image();

            img.onload = function () {
                this.height > $image.parent().height() || this.width > $image.parent().width() ?
                    hideZoomControls(false) : hideZoomControls(true);
            };
            img.src = path;
        }

        /**
         * Control visibility of zoom buttons.
         * Zoom controls must be invisible for video content and touch devices.
         * On touch devices active pinchIn/pinchOut.
         * @param $image
         * @param isTouchScreen - true for touch devices
         * @param isVideoActiveFrame - true for active video frame
         */
        function toggleZoomButtons($image, isTouchScreen, isVideoActiveFrame) {
            var path = $image.attr('src');

            if (path && !isTouchScreen && !isVideoActiveFrame) {
                asyncToggleZoomButtons(path, $image);
            } else {
                hideZoomControls(true);
            }
        }

        /**
         * Handle resize event in fullscreen.
         * @param $image - Fullscreen image.
         * @param e - Event.
         */
        function resizeHandler(e, $image) {
            var imageSize,
                parentWidth,
                parentHeight,
                isImageSmall,
                isImageFit;

            if (!e.data.$image || !e.data.$image.length)
                return;

            imageSize = getImageSize($(fullscreenImageSelector)[0]);
            parentWidth = e.data.$image.parent().width();
            parentHeight = e.data.$image.parent().height();
            isImageSmall = parentWidth >= imageSize.rw && parentHeight >= imageSize.rh;
            isImageFit = parentWidth > e.data.$image.width() && parentHeight > e.data.$image.height();

            toggleZoomButtons(e.data.$image, isTouchEnabled, checkForVideo(e.data.fotorama.activeFrame.$stageFrame));
            calculateMinSize(e.data.$image);

            if (e.data.$image.hasClass(imageZoommable) && !allowZoomOut || isImageSmall || isImageFit) {
                resetVars(e.data.$image);
            }

            if (!isImageSmall) {
                toggleStandartNavigation();
            }
        }

        function getTopValue($image, topProp, step, height, containerHeight) {
            var top;

            if (parseInt($image.css('marginTop')) || parseInt($image.css('marginLeft'))) {
                top = dragFlag ? topProp - step / 4 : 0;
                top = top < containerHeight - height ? containerHeight - height : top;
                top = top > height - containerHeight ? height - containerHeight : top;
            } else {
                top = topProp + step / 2;
                top = top < containerHeight - height ? containerHeight - height : top;
                top = top > 0 ? 0 : top;

                if (!dragFlag && step < 0) {
                    top = top < (containerHeight - height) / 2 ? (containerHeight - height) / 2 : top;
                }
            }

            return top;
        }

        function getLeftValue(leftProp, step, width, containerWidth) {
            var left;

            left = leftProp + step / 2;
            left = left < containerWidth - width ? containerWidth - width : left;
            left = left > 0 ? 0 : left;

            if (!dragFlag && step < 0) {
                left = left < (containerWidth - width) / 2 ? (containerWidth - width) / 2 : left;
            }

            return left;
        }

        function checkFullscreenImagePosition($image, dimentions, widthStep, heightStep) {
            var $imageContainer,
                containerWidth,
                containerHeight,
                settings,
                top,
                left,
                right,
                bottom,
                ratio;

            if ($(gallerySelector).data('fotorama').fullScreen) {
                transitionActive = true;
                $imageContainer = $image.parent();
                containerWidth = $imageContainer.width();
                containerHeight = $imageContainer.height();
                top = $image.position().top;
                left = $image.position().left;
                ratio = $image.width() / $image.height();
                dimentions.height = isNaN(dimentions.height) ? dimentions.width / ratio : dimentions.height;
                dimentions.width = isNaN(dimentions.width) ? dimentions.height * ratio : dimentions.width;

                top = dimentions.height >= containerHeight ?
                    getTopValue($image, top, heightStep, dimentions.height, containerHeight) : 0;

                left = dimentions.width >= containerWidth ?
                    getLeftValue(left, widthStep, dimentions.width, containerWidth) : 0;

                right = dragFlag && left < (containerWidth - dimentions.width) / 2 ? 0 : left;
                bottom = dragFlag ? 0 : top;

                settings = $.extend(dimentions, {
                    top: top,
                    left: left,
                    right: right
                });

                $image.css(settings);
            }
        }

        /**
         * Toggles fotorama's keyboard and mouse/touch navigation.
         */
        function toggleStandartNavigation() {
            var $selectable =
                    $('a[href], area[href], input, select, textarea, button, iframe, object, embed, *[tabindex], *[contenteditable]')
                    .not('[tabindex=-1], [disabled], :hidden'),
                fotorama = $(gallerySelector).data('fotorama'),
                $focus = $(':focus'),
                index;

            if (fotorama.fullScreen) {

                $selectable.each(function (number) {

                    if ($(this).is($focus)) {
                        index = number;
                    }
                });

                fotorama.setOptions({
                    swipe: !allowZoomOut,
                    keyboard: !allowZoomOut
                });

                if (_.isNumber(index)) {
                    $selectable.eq(index).trigger('focus');
                }
            }
        }

        function zoomIn(e, xStep, yStep) {
            var $image,
                imgOriginalSize,
                imageWidth,
                imageHeight,
                zoomWidthStep,
                zoomHeightStep,
                widthResult,
                heightResult,
                ratio,
                dimentions = {};

            if (allowZoomIn && (!transitionEnabled || !transitionActive) && (isTouchEnabled ||
                !$(zoomInButtonSelector).hasClass(zoomInDisabled))) {
                $image = $(fullscreenImageSelector);
                imgOriginalSize = getImageSize($image[0]);
                imageWidth = $image.width();
                imageHeight = $image.height();
                ratio = imageWidth / imageHeight;
                allowZoomOut = true;
                toggleStandartNavigation();

                if (!$image.hasClass(imageZoommable)) {
                    toggleZoomable($image, true);
                }

                e.preventDefault();

                if (imageWidth >= imageHeight) {
                    zoomWidthStep = xStep || Math.ceil(imageWidth * parseFloat(config.magnifierOpts.fullscreenzoom) / 100);
                    widthResult = imageWidth + zoomWidthStep;

                    if (widthResult >= imgOriginalSize.rw) {
                        widthResult = imgOriginalSize.rw;
                        zoomWidthStep = xStep || widthResult - imageWidth;
                        allowZoomIn = false;
                    }
                    heightResult = widthResult / ratio;
                    zoomHeightStep = yStep || heightResult - imageHeight;
                } else {
                    zoomHeightStep = yStep || Math.ceil(imageHeight * parseFloat(config.magnifierOpts.fullscreenzoom) / 100);
                    heightResult = imageHeight + zoomHeightStep;

                    if (heightResult >= imgOriginalSize.rh) {
                        heightResult = imgOriginalSize.rh;
                        zoomHeightStep = yStep || heightResult - imageHeight;
                        allowZoomIn = false;
                    }
                    widthResult = heightResult * ratio;
                    zoomWidthStep = xStep || widthResult - imageWidth;
                }

                if (imageWidth >= imageHeight && imageWidth !== imgOriginalSize.rw) {
                    dimentions = $.extend(dimentions, {
                        width: widthResult,
                        height: 'auto'
                    });
                    checkFullscreenImagePosition($image, dimentions, -zoomWidthStep, -zoomHeightStep);

                } else if (imageWidth < imageHeight && imageHeight !== imgOriginalSize.rh) {
                    dimentions = $.extend(dimentions, {
                        width: 'auto',
                        height: heightResult
                    });
                    checkFullscreenImagePosition($image, dimentions, -zoomWidthStep, -zoomHeightStep);
                }
            }

            return false;
        }

        function zoomOut(e, xStep, yStep) {
            var $image,
                widthResult,
                heightResult,
                dimentions,
                parentWidth,
                parentHeight,
                imageWidth,
                imageHeight,
                zoomWidthStep,
                zoomHeightStep,
                ratio,
                fitIntoParent;

            if (allowZoomOut && (!transitionEnabled || !transitionActive) && (isTouchEnabled ||
                !$(zoomOutButtonSelector).hasClass(zoomOutDisabled))) {
                allowZoomIn = true;
                $image = $(fullscreenImageSelector);
                parentWidth = $image.parent().width();
                parentHeight = $image.parent().height();
                imageWidth = $image.width();
                imageHeight = $image.height();
                ratio = imageWidth / imageHeight;

                e.preventDefault();

                if (imageWidth >= imageHeight) {
                    zoomWidthStep = xStep || Math.ceil(imageWidth * parseFloat(config.magnifierOpts.fullscreenzoom) / 100);
                    widthResult = imageWidth - zoomWidthStep;
                    heightResult = widthResult / ratio;
                    zoomHeightStep = yStep || imageHeight - heightResult;
                } else {
                    zoomHeightStep = yStep || Math.ceil(imageHeight * parseFloat(config.magnifierOpts.fullscreenzoom) / 100);
                    heightResult = imageHeight - zoomHeightStep;
                    widthResult = heightResult * ratio;
                    zoomWidthStep = xStep || imageWidth - widthResult;
                }

                fitIntoParent = function () {
                    if (ratio > parentWidth / parentHeight) {
                        widthResult = parentWidth;
                        zoomWidthStep = imageWidth - widthResult;
                        heightResult = widthResult / ratio;
                        zoomHeightStep = imageHeight - heightResult;
                        dimentions = {
                            width: widthResult,
                            height: 'auto'
                        };
                    } else {
                        heightResult = parentHeight;
                        zoomHeightStep = imageHeight - heightResult;
                        widthResult = heightResult * ratio;
                        zoomWidthStep = imageWidth - widthResult;
                        dimentions = {
                            width: 'auto',
                            height: heightResult
                        };
                    }
                    checkFullscreenImagePosition($image, dimentions, zoomWidthStep, zoomHeightStep);
                };

                if (imageWidth >= imageHeight) {
                    if (widthResult > parentWidth) {
                        dimentions = {
                            width: widthResult,
                            height: 'auto'
                        };
                        checkFullscreenImagePosition($image, dimentions, zoomWidthStep, zoomHeightStep);
                    } else if (heightResult > parentHeight) {
                        dimentions = {
                            width: widthResult,
                            height: 'auto'
                        };
                        checkFullscreenImagePosition($image, dimentions, zoomWidthStep, zoomHeightStep);
                    } else {
                        allowZoomOut = dragFlag = false;
                        toggleStandartNavigation();
                        fitIntoParent();
                    }
                } else if (heightResult > parentHeight) {
                    dimentions = {
                        width: 'auto',
                        height: heightResult
                    };
                    checkFullscreenImagePosition($image, dimentions, zoomWidthStep, zoomHeightStep);
                } else if (widthResult > parentWidth) {
                    dimentions = {
                        width: 'auto',
                        height: heightResult
                    };
                    checkFullscreenImagePosition($image, dimentions, zoomWidthStep, zoomHeightStep);
                } else {
                    allowZoomOut = dragFlag = false;
                    toggleStandartNavigation();
                    fitIntoParent();
                }
            }

            return false;
        }

        /**
         * Bind event on scroll on active item in fotorama
         * @param e
         * @param fotorama - object of fotorama
         */
        function mousewheel(e, fotorama, element) {
            var $fotoramaStage = fotorama.activeFrame.$stageFrame,
                fotoramaStage = $fotoramaStage.get(0);

            function onWheel(e) {
                var delta = e.deltaY || e.wheelDelta,
                    ev = e || window.event;

                if ($(gallerySelector).data('fotorama').fullScreen) {

                    if (e.deltaY) {
                        if (delta > 0) {
                            zoomOut(ev);
                        } else {
                            zoomIn(ev);
                        }
                    } else if (delta > 0) {
                        zoomIn(ev);
                    } else {
                        zoomOut(ev);
                    }

                    e.preventDefault ? e.preventDefault() : e.returnValue = false;
                }
            }

            if (!$fotoramaStage.hasClass('magnify-wheel-loaded')) {
                if (fotoramaStage && fotoramaStage.addEventListener) {
                    if ('onwheel' in document) {
                        fotoramaStage.addEventListener('wheel', onWheel, { passive: false });
                    } else if ('onmousewheel' in document) {
                        fotoramaStage.addEventListener('mousewheel', onWheel);
                    } else {
                        fotoramaStage.addEventListener('MozMousePixelScroll', onWheel);
                    }
                    $fotoramaStage.addClass('magnify-wheel-loaded');
                }
            }
        }

        /**
         * Method which makes draggable picture. Also work on touch devices.
         */
        function magnifierFullscreen(fotorama) {
            var isDragActive = false,
                startX,
                startY,
                imagePosX,
                imagePosY,
                touch,
                swipeSlide,
                $gallery = $(gallerySelector),
                $image = $(fullscreenImageSelector, $gallery),
                $imageContainer = $('[data-gallery-role="stage-shaft"] [data-active="true"]'),
                gallery = $gallery.data('fotorama'),
                pinchDimention;

            swipeSlide = _.throttle(function (direction) {
                $(gallerySelector).data('fotorama').show(direction);
            }, 500, {
                trailing: false
            });

            /**
             * Returns top position value for passed jQuery object.
             *
             * @param $el
             * @return {number}
             */
            function getTop($el) {
                return parseInt($el.get(0).style.top);
            }

            function shiftImage(dx, dy, e) {
                var top = +imagePosY + dy,
                    left = +imagePosX + dx,
                    swipeCondition = $image.width() / 10 + 20;

                dragFlag = true;

                if ($image.offset().left === $imageContainer.offset().left + $imageContainer.width() - $image.width() && e.keyCode === 39 ||
                    endX - 1 < $imageContainer.offset().left + $imageContainer.width() - $image.width() && dx < 0 &&
                    _.isNumber(endX) &&
                    (e.type === 'mousemove' || e.type === 'touchmove' || e.type === 'pointermove' || e.type === 'MSPointerMove')) {
                    endX = null;
                    swipeSlide('>');

                    return;
                }

                if ($image.offset().left === $imageContainer.offset().left && dx !== 0 && e.keyCode === 37 ||
                    endX === $imageContainer.offset().left && dx > 0 &&
                    (e.type === 'mousemove' || e.type === 'touchmove' || e.type === 'pointermove' || e.type === 'MSPointerMove')) {
                    endX = null;
                    swipeSlide('<');

                    return;
                }

                if ($image.height() > $imageContainer.height()) {
                    if ($imageContainer.height() > $image.height() + top) {
                        $image.css('top', $imageContainer.height() - $image.height());
                    } else {
                        top = $image.height() - getTop($image) - $imageContainer.height();
                        dy = dy < top ? dy : top;
                        $image.css('top', getTop($image) + dy);
                    }
                }

                if ($image.width() > $imageContainer.width()) {

                    if ($imageContainer.offset().left + $imageContainer.width() > left + $image.width()) {
                        left = $imageContainer.offset().left + $imageContainer.width() - $image.width();
                    } else {
                        left = $imageContainer.offset().left < left ? $imageContainer.offset().left : left;
                    }
                    $image.offset({
                        'left': left
                    });
                    $image.css('right', '');
                } else if (Math.abs(dy) < 1 && allowZoomOut &&
                    !(e.type === 'mousemove' || e.type === 'touchmove' || e.type === 'pointermove' || e.type === 'MSPointerMove')) {
                    dx < 0 ? $(gallerySelector).data('fotorama').show('>') : $(gallerySelector).data('fotorama').show('<');
                }

                if ($image.width() <= $imageContainer.width() && allowZoomOut &&
                    (e.type === 'mousemove' || e.type === 'touchmove' || e.type === 'pointermove' || e.type === 'MSPointerMove') &&
                    Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > swipeCondition) {
                    dx < 0 ? swipeSlide('>') : swipeSlide('<');
                }
            }

            /**
             * Sets image size to original or fit in parent block
             * @param e - event object
             */
            function dblClickHandler(e) {
                var imgOriginalSize = getImageSize($image[0]),
                    proportions;

                if (imgOriginalSize.rh < $image.parent().height() && imgOriginalSize.rw < $image.parent().width()) {
                    return;
                }

                proportions = imgOriginalSize.rw / imgOriginalSize.rh;

                if (allowZoomIn) {
                    zoomIn(e, imgOriginalSize.rw - $image.width(), imgOriginalSize.rh - $image.height());
                } else if (proportions > $imageContainer.width() / $imageContainer.height()) {
                    zoomOut(e, imgOriginalSize.rw - $imageContainer.width(), imgOriginalSize.rw / proportions);
                } else {
                    zoomOut(e, imgOriginalSize.rw * proportions, imgOriginalSize.rh - $imageContainer.height());
                }
            }

            function detectDoubleTap(e) {
                var now = new Date().getTime(),
                    timesince = now - tapFlag;

                if (timesince < 400 && timesince > 0) {
                    transitionActive = false;
                    tapFlag = 0;
                    dblClickHandler(e);
                } else {
                    tapFlag = new Date().getTime();
                }
            }

            if (isTouchEnabled) {
                $image.off('tap');
                $image.on('tap', function (e) {
                    if (e.originalEvent.originalEvent.touches.length === 0) {
                        detectDoubleTap(e);
                    }
                });
            } else {
                $image.off('dblclick');
                $image.on('dblclick', dblClickHandler);
            }

            if (gallery.fullScreen) {
                toggleZoomButtons($image, isTouchEnabled, checkForVideo(fotorama.activeFrame.$stageFrame));
            }

            function getDimention(event) {
                return Math.sqrt(
                    (event.touches[0].clientX - event.touches[1].clientX) * (event.touches[0].clientX - event.touches[1].clientX) +
                    (event.touches[0].clientY - event.touches[1].clientY) * (event.touches[0].clientY - event.touches[1].clientY));
            }

            $image.off(isTouchEnabled ? 'touchstart' : 'pointerdown mousedown MSPointerDown');
            $image.on(isTouchEnabled ? 'touchstart' : 'pointerdown mousedown MSPointerDown', function (e) {
                if (e && e.originalEvent.touches && e.originalEvent.touches.length >= 2) {
                    e.preventDefault();
                    pinchDimention = getDimention(e.originalEvent);
                    isDragActive = false;

                    if ($image.hasClass(imageDraggableClass)) {
                        $image.removeClass(imageDraggableClass);
                    }
                } else if (gallery.fullScreen && (!transitionEnabled || !transitionActive)) {
                    imagePosY = getTop($image);
                    imagePosX = $image.offset().left;

                    if (isTouchEnabled) {
                        touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
                        e.clientX = touch.pageX;
                        e.clientY = touch.pageY;
                    }
                    startX = e.clientX || e.originalEvent.clientX;
                    startY = e.clientY || e.originalEvent.clientY;
                    isDragActive = true;
                }

                if ($image.offset() && $image.width() > $imageContainer.width()) {
                    endX = $image.offset().left;
                }
            });

            $image.off(isTouchEnabled ? 'touchmove' : 'mousemove pointermove MSPointerMove');
            $image.on(isTouchEnabled ? 'touchmove' : 'mousemove pointermove MSPointerMove', function (e) {
                if (e && e.originalEvent.touches && e.originalEvent.touches.length >= 2) {
                    e.preventDefault();
                    var currentDimention = getDimention(e.originalEvent);

                    if ($image.hasClass(imageDraggableClass)) {
                        $image.removeClass(imageDraggableClass);
                    }

                    if (currentDimention < pinchDimention) {
                        zoomOut(e);
                        pinchDimention = currentDimention;
                    } else if (currentDimention > pinchDimention) {
                        zoomIn(e);
                        pinchDimention = currentDimention;
                    }
                } else {
                    var clientX,
                        clientY;

                    if (gallery.fullScreen && isDragActive && (!transitionEnabled || !transitionActive)) {

                        if (allowZoomOut && !$image.hasClass(imageDraggableClass)) {
                            $image.addClass(imageDraggableClass);
                        }
                        clientX = e.clientX || e.originalEvent.clientX;
                        clientY = e.clientY || e.originalEvent.clientY;

                        e.preventDefault();

                        if (isTouchEnabled) {
                            touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
                            clientX = touch.pageX;
                            clientY = touch.pageY;
                        }

                        if (allowZoomOut) {
                            imagePosY = getTop($(fullscreenImageSelector, $gallery));
                            shiftImage(clientX - startX, clientY - startY, e);
                        }
                    }
                }
            });

            $image.off('transitionend webkitTransitionEnd mozTransitionEnd msTransitionEnd ');
            $image.on('transitionend webkitTransitionEnd mozTransitionEnd msTransitionEnd', function () {
                transitionActive = false;
            });

            if (keyboardNavigation) {
                $(document).off('keydown', keyboardNavigation);
            }

            /**
             * Replaces original navigations with better one
             * @param e - event object
             */
            keyboardNavigation = function (e) {
                var step = 40,
                    $focus = $(':focus'),
                    isFullScreen = $(gallerySelector).data('fotorama').fullScreen,
                    initVars = function () {
                        imagePosX = $(fullscreenImageSelector, $gallery).offset().left;
                        imagePosY = getTop($(fullscreenImageSelector, $gallery));
                    };

                if (($focus.attr('data-gallery-role') || !$focus.length) && allowZoomOut) {
                    if (isFullScreen) {
                        imagePosX = $(fullscreenImageSelector, $(gallerySelector)).offset().left;
                        imagePosY = getTop($(fullscreenImageSelector, $(gallerySelector)));
                    }

                    if (e.keyCode === 39) {

                        if (isFullScreen) {
                            initVars();
                            shiftImage(-step, 0, e);
                        }
                    }

                    if (e.keyCode === 38) {

                        if (isFullScreen) {
                            initVars();
                            shiftImage(0, step, e);
                        }
                    }

                    if (e.keyCode === 37) {

                        if (isFullScreen) {
                            initVars();
                            shiftImage(step, 0, e);
                        }
                    }

                    if (e.keyCode === 40) {

                        if (isFullScreen) {
                            e.preventDefault();
                            initVars();
                            shiftImage(0, -step, e);
                        }
                    }
                }

                if (e.keyCode === 27 && isFullScreen && allowZoomOut) {
                    $(gallerySelector).data('fotorama').cancelFullScreen();
                }
            };

            /**
             * @todo keyboard navigation through Fotorama Api.
             */
            $(document).on('keydown', keyboardNavigation);

            $(document).on(isTouchEnabled ? 'touchend' : 'mouseup pointerup MSPointerUp', function (e) {
                if (gallery.fullScreen) {

                    if ($image.offset() && $image.width() > $imageContainer.width()) {
                        endX = $image.offset().left;
                    }

                    isDragActive = false;
                    $image.removeClass(imageDraggableClass);
                }
            });

            $(window).off('resize', resizeHandler);
            $(window).on('resize', {
                $image: $image,
                fotorama: fotorama
            }, resizeHandler);
        }

        /**
         * Hides magnifier preview and zoom blocks.
         */
        hideMagnifier = function () {
            $(magnifierSelector).empty().hide();
            $(magnifierZoomSelector).remove();
        };

        /**
         * Check is active frame in gallery include video content.
         * If true activeFrame contain video.
         * @param $stageFrame - active frame in gallery
         * @returns {*|Boolean}
         */
        function checkForVideo($stageFrame) {
            return $stageFrame.hasClass(videoContainerClass);
        }

        /**
         * Hides magnifier on drag and while arrow click.
         */
        function behaveOnDrag(e, initPos) {
            var pos = [e.pageX, e.pageY],
                isArrow = $(e.target).data('gallery-role') === 'arrow',
                isClick = initPos[0] === pos[0] && initPos[1] === pos[1],
                isImg = $(e.target).parent().data('active');

            if (isArrow || isImg && !isClick) {
                hideMagnifier();
            }
        }

        if (config.magnifierOpts.enabled) {
            $(element).on('pointerdown mousedown MSPointerDown', function (e) {
                var pos = [e.pageX, e.pageY];

                $(element).on('mousemove pointermove MSPointerMove', function (ev) {
                    navigator.msPointerEnabled ? hideMagnifier() : behaveOnDrag(ev, pos);
                });
                $(document).on('mouseup pointerup MSPointerUp', function () {
                    $(element).off('mousemove pointermove MSPointerMove');
                });
            });
        }

        $.extend(config.magnifierOpts, {
            zoomable: false,
            thumb: '.fotorama__img',
            largeWrapper: '[data-gallery-role="magnifier"]',
            height: config.magnifierOpts.height || function () {
                return $('[data-active="true"]').height();
            },
            width: config.magnifierOpts.width || function () {
                var productMedia = $(gallerySelector).parent().parent();

                return productMedia.parent().width() - productMedia.width() - 20;
            },
            left: config.magnifierOpts.left || function () {
                return $(gallerySelector).offset().left + $(gallerySelector).width() + 20;
            },
            top: config.magnifierOpts.top || function () {
                return $(gallerySelector).offset().top;
            }
        });

        $(element).on('fotorama:load fotorama:showend fotorama:fullscreenexit fotorama:ready', function (e, fotorama) {
            var $activeStageFrame = $(gallerySelector).data('fotorama').activeFrame.$stageFrame;

            if (!$activeStageFrame.find(magnifierZoomSelector).length) {
                hideMagnifier();

                if (config.magnifierOpts) {
                    config.magnifierOpts.large = $(gallerySelector).data('fotorama').activeFrame.img;
                    config.magnifierOpts.full = fotorama.data[fotorama.activeIndex].original;
                    !checkForVideo($activeStageFrame) && $($activeStageFrame).magnify(config.magnifierOpts);
                }
            }
        });

        $(element).on('gallery:loaded', function (e) {
            var $prevImage;

            $(element).find(gallerySelector)
                .on('fotorama:ready', function (e, fotorama) {
                    var $zoomIn = $(zoomInButtonSelector),
                        $zoomOut = $(zoomOutButtonSelector);

                    if (!$zoomIn.hasClass(zoomInLoaded)) {
                        $zoomIn.on('click touchstart', zoomIn);
                        $zoomIn.on('mousedown', function (e) {
                            e.stopPropagation();
                        });

                        $zoomIn.on('keyup', function (e) {

                            if (e.keyCode === 13) {
                                zoomIn(e);
                            }
                        });

                        $(window).on('keyup', function (e) {

                            if (e.keyCode === 107 || fotorama.fullscreen) {
                                zoomIn(e);
                            }
                        });

                        $zoomIn.addClass(zoomInLoaded);
                    }

                    if (!$zoomOut.hasClass(zoomOutLoaded)) {
                        $zoomOut.on('click touchstart', zoomOut);
                        $zoomOut.on('mousedown', function (e) {
                            e.stopPropagation();
                        });

                        $zoomOut.on('keyup', function (e) {

                            if (e.keyCode === 13) {
                                zoomOut(e);
                            }
                        });

                        $(window).on('keyup', function (e) {

                            if (e.keyCode === 109 || fotorama.fullscreen) {
                                zoomOut(e);
                            }
                        });

                        $zoomOut.addClass(zoomOutLoaded);
                    }
                })
                .on('fotorama:fullscreenenter fotorama:showend', function (e, fotorama) {
                    hideMagnifier();

                    if (!$(fullscreenImageSelector).is($prevImage)) {
                        resetVars($(fullscreenImageSelector));
                    }
                    magnifierFullscreen(fotorama);
                    mousewheel(e, fotorama, element);

                    if ($prevImage) {
                        calculateMinSize($prevImage);

                        if (!$(fullscreenImageSelector).is($prevImage)) {
                            resetVars($prevImage);
                        }
                    }

                    toggleStandartNavigation();
                })
                .on('fotorama:load', function (e, fotorama) {
                    if ($(gallerySelector).data('fotorama').fullScreen) {
                        toggleZoomButtons($(fullscreenImageSelector), isTouchEnabled,
                            checkForVideo(fotorama.activeFrame.$stageFrame));
                    }
                    magnifierFullscreen(fotorama);
                })
                .on('fotorama:show', function (e, fotorama) {
                    $prevImage = _.clone($(fullscreenImageSelector));
                    hideMagnifier();
                })
                .on('fotorama:fullscreenexit', function (e, fotorama) {
                    resetVars($(fullscreenImageSelector));
                    hideMagnifier();
                    hideZoomControls(true);
                });
        });

        return config;
    };
});
