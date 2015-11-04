/**
 * Copyright © 2015 Magento. All rights reserved.
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
            hideMagnifier,
            behaveOnHover,
            dragFlag,
            zoomShown = true,
            allowZoomOut = false,
            allowZoomIn = true;

        if (isTouchEnabled) {
            $(element).on('fotorama:showend fotorama:load', function () {
                $(magnifierSelector).remove();
                $(magnifierZoomSelector).remove();
            });
        }

        /**
         * Return width and height of original image
         * @param src path for original image
         * @returns {{rw: number, rh: number}}
         */
        function getImageSize(src) {
            var img = new Image(),
                imgSize = {
                    rw: 0,
                    rh: 0
                };

            img.src = src;
            imgSize.rw = img.width;
            imgSize.rh = img.height;

            return imgSize;
        }

        function toggleZoomable($image, flag) {
            if (flag) {
                $image.css({
                    width: $image.width(),
                    height: $image.height()
                }).addClass(imageZoommable);
            } else {
                $image.css({
                    width: '',
                    height: '',
                    top: '',
                    left: ''
                }).removeClass(imageZoommable);
            }
        }

        function resetVars($image) {
            allowZoomIn = true;
            allowZoomOut = dragFlag = false;
            $image.hasClass(imageDraggableClass) && $image.removeClass(imageDraggableClass);
            toggleZoomable($image, false);
        }

        function toggleZoomButtons($image) {
            var path = $image.attr('src'),
                imgSize;

            if (path) {
                imgSize = getImageSize(path);

                if (imgSize.rh > $image.parent().height() || imgSize.rw > $image.parent().width()) {
                    $(zoomInButtonSelector).show();
                    $(zoomOutButtonSelector).show();
                    zoomShown = true;
                } else {
                    $(zoomInButtonSelector).hide();
                    $(zoomOutButtonSelector).hide();
                    zoomShown = false;
                }
            } else {
                $(zoomInButtonSelector).hide();
                $(zoomOutButtonSelector).hide();
                zoomShown = false;
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
                ratio;

            if ($(gallerySelector).data('fotorama').fullScreen) {
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

                settings = $.extend(dimentions, {
                    left: left,
                    top: top
                });

                $image.css(settings);
            }
        }

        function zoomIn(e) {
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

            if (zoomShown && allowZoomIn) {

                $image = $(fullscreenImageSelector);
                imgOriginalSize = getImageSize($image[0].src);
                imageWidth = $image.width();
                imageHeight = $image.height();
                ratio = imageWidth / imageHeight;
                allowZoomOut = true;

                if (!$image.hasClass(imageZoommable)) {
                    toggleZoomable($image, true);
                }

                e.preventDefault();

                if (imageWidth >= imageHeight) {
                    zoomWidthStep = Math.ceil(imageWidth * parseFloat(config.magnifierOpts.fullscreenzoom) / 100);
                    widthResult = imageWidth + zoomWidthStep;

                    if (widthResult >= imgOriginalSize.rw) {
                        widthResult = imgOriginalSize.rw;
                        zoomWidthStep = widthResult - imageWidth;
                        allowZoomIn = false;
                    }
                    heightResult = widthResult / ratio;
                    zoomHeightStep = heightResult - imageHeight;
                } else {
                    zoomHeightStep = Math.ceil(imageHeight * parseFloat(config.magnifierOpts.fullscreenzoom) / 100);
                    heightResult = imageHeight + zoomHeightStep;

                    if (heightResult >= imgOriginalSize.rh) {
                        heightResult = imgOriginalSize.rh;
                        zoomHeightStep =  heightResult - imageHeight;
                        allowZoomIn = false;
                    }
                    widthResult = heightResult * ratio;
                    zoomWidthStep = widthResult - imageWidth;
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

        function zoomOut(e) {
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

            if (zoomShown && allowZoomOut) {
                allowZoomIn = true;
                $image = $(fullscreenImageSelector);
                parentWidth = $image.parent().width();
                parentHeight = $image.parent().height();
                imageWidth = $image.width();
                imageHeight = $image.height();
                ratio = imageWidth / imageHeight;

                e.preventDefault();

                if (imageWidth >= imageHeight) {
                    zoomWidthStep = Math.ceil(imageWidth * parseFloat(config.magnifierOpts.fullscreenzoom) / 100);
                    widthResult = imageWidth - zoomWidthStep;
                    heightResult = widthResult / ratio;
                    zoomHeightStep = imageHeight - heightResult;
                } else {
                    zoomHeightStep = Math.ceil(imageHeight * parseFloat(config.magnifierOpts.fullscreenzoom) / 100);
                    heightResult = imageHeight - zoomHeightStep;
                    widthResult = heightResult * ratio;
                    zoomWidthStep = imageWidth - widthResult;
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
                    } else {
                        if (heightResult > parentHeight) {
                            dimentions = {
                                width: widthResult,
                                height: 'auto'
                            };
                            checkFullscreenImagePosition($image, dimentions, zoomWidthStep, zoomHeightStep);
                        } else {
                            allowZoomOut = dragFlag = false;
                            fitIntoParent();
                        }
                    }
                } else {
                    if (heightResult > parentHeight) {
                        dimentions = {
                            width: 'auto',
                            height: heightResult
                        };
                        checkFullscreenImagePosition($image, dimentions, zoomWidthStep, zoomHeightStep);
                    } else {
                        if (widthResult > parentWidth) {
                            dimentions = {
                                width: 'auto',
                                height: heightResult
                            };
                            checkFullscreenImagePosition($image, dimentions, zoomWidthStep, zoomHeightStep);
                        } else {
                            allowZoomOut = dragFlag = false;
                            fitIntoParent();
                        }
                    }
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
                    } else {
                        if (delta > 0) {
                            zoomIn(ev);
                        } else {
                            zoomOut(ev);
                        }
                    }

                    e.preventDefault ? e.preventDefault() : e.returnValue = false;
                }
            }

            if (!$fotoramaStage.hasClass('magnify-wheel-loaded')) {
                if (fotoramaStage && fotoramaStage.addEventListener) {
                    if ('onwheel' in document) {
                        fotoramaStage.addEventListener('wheel', onWheel);
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
        function magnifierFullscreen() {
            var isDragActive = false,
                startX,
                startY,
                endX,
                imagePosX,
                imagePosY,
                touch,
                $gallery = $(gallerySelector),
                $image = $(fullscreenImageSelector, $gallery),
                $imageContainer = $('[data-gallery-role="stage-shaft"] [data-active="true"]'),
                gallery = $gallery.data('fotorama');

            var swipeSlide = _.throttle(function (direction) {
                $(gallerySelector).data('fotorama').show(direction)
            }, 500, {trailing: false});

            function shiftImage(dx, dy, e) {
                var top = +imagePosY + dy,
                    left = +imagePosX + dx;

                dragFlag = true;

                if (($image.offset().left === $imageContainer.offset().left + $imageContainer.width() - $image.width() && e.keyCode === 39) ||
                    (endX === $imageContainer.offset().left + $imageContainer.width() - $image.width() && dx < 0 && 
                    (e.type === 'mousemove' || e.type === 'touchmove' || e.type === 'pointermove' || e.type === 'MSPointerMove'))) {
                    endX = null;
                    swipeSlide('>');
                    return;
                }

                if (($image.offset().left === $imageContainer.offset().left && dx !== 0 && e.keyCode === 37) ||
                    (endX === $imageContainer.offset().left && dx > 0 && 
                    (e.type === 'mousemove' || e.type === 'touchmove' || e.type === 'pointermove' || e.type === 'MSPointerMove'))) {
                    endX = null;
                    swipeSlide('<');
                    return;
                }

                if ($image.height() > $imageContainer.height()) {

                    if ($imageContainer.offset().top + $imageContainer.height() > top + $image.height()) {
                        top = $imageContainer.offset().top + $imageContainer.height() - $image.height();
                    } else {
                        top = $imageContainer.offset().top < top ? 0 : top;
                    }
                    $image.offset({
                        'top': top
                    });
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
                } else if (Math.abs(dy) === 0 &&  
                    !(e.type === 'mousemove' || e.type === 'touchmove' || e.type === 'pointermove' || e.type === 'MSPointerMove')) {
                    dx < 0 ? $(gallerySelector).data('fotorama').show('>') : $(gallerySelector).data('fotorama').show('<');
                }

                if ($image.width() <= $imageContainer.width() && 
                    (e.type === 'mousemove' || e.type === 'touchmove' || e.type === 'pointermove' || e.type === 'MSPointerMove') && 
                    Math.abs(dx) > Math.abs(dy)) {
                    dx < 0 ? swipeSlide('>') : swipeSlide('<');
                }
            }

            /**
             * Sets image size to original
             * @param e - event object
             */
            function setImageFullSize(e) {
                var imgOriginalSize = getImageSize($image[0].src),
                    zoomWidthStep = imgOriginalSize.rw - $image.width(),
                    zoomHeightStep = imgOriginalSize.rh - $image.height(),
                    ratio = $image.width() / $image.height(),
                    dimentions = {};

                if (zoomShown && allowZoomIn) {
                    if (!$image.hasClass(imageZoommable)) {
                        toggleZoomable($image, true);
                    };

                    if (ratio >= 1) {
                        dimentions = {
                            width: imgOriginalSize.rw,
                            height: 'auto'
                        };
                    } else {
                        dimentions = {
                            height: imgOriginalSize.rh,
                            width: 'auto'
                        };
                    }

                    checkFullscreenImagePosition($image, dimentions, -zoomWidthStep, -zoomHeightStep);

                    allowZoomIn = false;
                    allowZoomOut = true;
                }
            }

            $image.unbind('dblclick');
            $image.dblclick(setImageFullSize);

            toggleZoomButtons($image);

            $image.off(isTouchEnabled ? 'touchstart' : 'pointerdown mousedown MSPointerDown')
            $image.on(isTouchEnabled ? 'touchstart' : 'pointerdown mousedown MSPointerDown', function (e) {
                if (gallery.fullScreen) {
                    e.preventDefault();

                    if (zoomShown && allowZoomOut) {
                        $image.addClass(imageDraggableClass);
                    }
                    imagePosY = $image.offset().top;
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
            });

            $image.off(isTouchEnabled ? 'touchmove' : 'mousemove pointermove MSPointerMove');
            $image.on(isTouchEnabled ? 'touchmove' : 'mousemove pointermove MSPointerMove', function (e) {
                var clientX,
                    clientY;

                if (gallery.fullScreen && isDragActive) {

                    clientX = e.clientX || e.originalEvent.clientX;
                    clientY = e.clientY || e.originalEvent.clientY;

                    e.preventDefault();

                    if (isTouchEnabled) {
                        touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
                        e.clientX = touch.pageX;
                        e.clientY = touch.pageY;
                    }

                    shiftImage(clientX - startX, clientY - startY, e);
                }
            });

            var keyboardNavigation = function (e) {
                var step = 40,
                    isFullScreen = $(gallerySelector).data('fotorama').fullScreen,
                    initVars = function () {
                        imagePosX = $(fullscreenImageSelector, $gallery).offset().left;
                        imagePosY = $(fullscreenImageSelector, $gallery).offset().top;
                    };

                if (isFullScreen) {
                    imagePosX = $(fullscreenImageSelector, $(gallerySelector)).offset().left;
                    imagePosY = $(fullscreenImageSelector, $(gallerySelector)).offset().top;
                }

                if (e.keyCode === 39) {
                    initVars();

                    if (isFullScreen) {
                        shiftImage(-step, 0, e);
                    } else {
                        $(gallerySelector).data('fotorama').show('>');
                    }
                }

                if (e.keyCode === 38) {
                    initVars();

                    if (isFullScreen) {
                        shiftImage(0, step, e);
                    } else {
                        $(gallerySelector).data('fotorama').show('<');
                    }
                }

                if (e.keyCode === 37) {
                    initVars();

                    if (isFullScreen) {
                        shiftImage(step, 0, e);
                    } else {
                        $(gallerySelector).data('fotorama').show('<');
                    }
                }

                if (e.keyCode === 40) {
                    e.preventDefault();
                    initVars();

                    if (isFullScreen) {
                        shiftImage(0, -step, e);
                    } else {
                        $(gallerySelector).data('fotorama').show('>');
                    }
                }
            };

            $(document).unbind('keydown');
            $(document).keydown(keyboardNavigation);

            $(document).on(isTouchEnabled ? 'touchend' : 'mouseup pointerup MSPointerUp', function (e) {

                if (gallery.fullScreen) {

                    if ($image.offset()) {
                        endX = $image.offset().left;
                    }

                    isDragActive = false;
                    $image.removeClass(imageDraggableClass);
                }
            });

            if (zoomShown) {
                toggleZoomable($image, true);
            }

            $(window).resize(function () {
                if ($image.hasClass(imageZoommable) && !allowZoomOut) {
                    resetVars($image);
                }
            });
        }

        /**
         * Hides magnifier preview and zoom blocks.
         */
        hideMagnifier = function () {
            $(magnifierSelector).empty().hide();
            $(magnifierZoomSelector).remove();
        };

        /**
         * Hides magnifier on drag and while arrow click.
         */
        behaveOnHover = function (e, initPos) {
            var pos = [e.pageX, e.pageY],
                isArrow = $(e.target).data('gallery-role') === 'arrow',
                isClick = initPos[0] ===  pos[0] && initPos[1] ===  pos[1],
                isImg = $(e.target).parent().data('active');

            if ((isImg && !isClick) || isArrow) {
                hideMagnifier();
            }
        };

        if (config.magnifierOpts.eventType === 'click') {
            config.options.swipe = false;
        } else if (config.magnifierOpts.eventType === 'hover') {
            $(element).on('pointerdown mousedown MSPointerDown', function (e) {
                var pos = [e.pageX, e.pageY];
                $(element).on('mousemove pointermove MSPointerMove', function (ev) {
                    navigator.msPointerEnabled ? hideMagnifier() : behaveOnHover(ev, pos);
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
            hideMagnifier();
            config.magnifierOpts.large = $(gallerySelector).data('fotorama').activeFrame.img;
            config.magnifierOpts.full = fotorama.data[fotorama.activeIndex].original;
            $($(gallerySelector).data('fotorama').activeFrame.$stageFrame).magnify(config.magnifierOpts);
        });
        $(element).on('gallery:loaded', function (e) {
            $(element).find(gallerySelector)
                .on('fotorama:ready', function (e, fotorama) {
                    var $zoomIn = $(zoomInButtonSelector),
                        $zoomOut = $(zoomOutButtonSelector);

                    if (!$zoomIn.hasClass('zoom-in-loaded')) {
                        $zoomIn.on('click touchstart', zoomIn);

                        $zoomIn.keyup(function (e) {

                            if (e.keyCode === 13) {
                                zoomIn(e);
                            }
                        });

                        $(window).keyup(function (e) {

                            if (e.keyCode === 107 || fotorama.fullscreen) {
                                zoomIn(e);
                            }
                        });

                        $zoomIn.addClass('zoom-in-loaded');
                    }

                    if (!$zoomOut.hasClass('zoom-out-loaded')) {
                        $zoomOut.on('click touchstart', zoomOut);

                        $zoomOut.keyup(function (e) {

                            if (e.keyCode === 13) {
                                zoomOut(e);
                            }
                        });

                        $(window).keyup(function (e) {

                            if (e.keyCode === 109 || fotorama.fullscreen) {
                                zoomOut(e);
                            }
                        });

                        $zoomOut.addClass('zoom-out-loaded');
                    }
                })
                .on('fotorama:fullscreenenter fotorama:showend', function (e, fotorama) {
                    $(element).data('gallery').updateOptions({
                        swipe: false
                    });
                    hideMagnifier();
                    resetVars($(fullscreenImageSelector));
                    magnifierFullscreen(e, fotorama);
                    mousewheel(e, fotorama, element);
                    resetVars($(fullscreenImageSelector));
                })
                .on('fotorama:load', function (e, fotorama) {
                    toggleZoomButtons($(fullscreenImageSelector));
                    magnifierFullscreen();
                })
                .on('fotorama:show fotorama:fullscreenexit', function (e, fotorama) {
                    hideMagnifier();
                    resetVars($(fullscreenImageSelector));
                });
        });

        return config;
    }
});
