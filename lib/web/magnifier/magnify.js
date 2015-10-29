/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'magnifier/magnifier'
], function ($) {
    'use strict';

    return function (config, element) {

        var isTouchEnabled = 'ontouchstart' in document.documentElement,
            gallerySelector = '[data-gallery-role="gallery"]',
            magnifierSelector = '[data-gallery-role="magnifier"]',
            magnifierZoomSelector = '[data-gallery-role="magnifier-zoom"]',
            zoomInButtonSelector = '[data-gallery-role="fotorama__zoom-in"]',
            zoomOutButtonSelector = '[data-gallery-role="fotorama__zoom-out"]',
            fullscreenImageSelector = '[data-gallery-role="stage-shaft"] [data-active="true"] .fotorama__img--full',
            hideMagnifier,
            behaveOnHover,
            zoomWidthStep,
            zoomHeightStep,
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

        function resetVars($image) {
            zoomWidthStep = zoomHeightStep = 0;
            allowZoomIn = true;
            allowZoomOut = dragFlag = false;
            $image.css({
                top: 0,
                left: 0,
                cursor: '',
                width: 'auto',
                height: 'auto',
                maxWidth: '100%',
                maxHeight: '100%',
                margin: 'auto'
            });
        }

        function clickAnimation(e) {

            var roundRadius = 80,
                clickAnimationDuration = 400;

            $('<div>').addClass('circle').offset({
                left: e.pageX - e.currentTarget.offsetLeft,
                top: e.pageY - e.currentTarget.offsetTop
            }).appendTo(e.currentTarget).animate(
                {
                    height: roundRadius,
                    width: roundRadius,
                    left: e.pageX - roundRadius / 2 - e.currentTarget.offsetLeft,
                    top: e.pageY - roundRadius / 2 - e.currentTarget.offsetTop,
                    opacity: 0
                },
                clickAnimationDuration,
                'swing',
                function () {
                    $(this).remove();
                }
            );
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

        function checkFullscreenImagePosition($image, dimentions, widthStep, heightStep) {
            var $imageContainer,
                settings,
                top,
                left,
                ratio;

            if ($(gallerySelector).data('fotorama').fullScreen) {
                $imageContainer = $image.parent();
                top = $image.position().top;
                left = $image.position().left;
                ratio = $image.width() / $image.height();
                dimentions.height = isNaN(dimentions.height) ? dimentions.width / ratio : dimentions.height;
                dimentions.width = isNaN(dimentions.width) ? dimentions.height * ratio : dimentions.width;

                if (dimentions.height >= $imageContainer.height()) {
                    if (parseInt($image.css('marginTop')) || parseInt($image.css('marginLeft'))) {
                        top = dragFlag ? top - heightStep/4 : 0;
                        top = top < $imageContainer.height() - dimentions.height ?
                            $imageContainer.height() - dimentions.height : top;
                        top = top > dimentions.height - $imageContainer.height() ?
                            dimentions.height - $imageContainer.height() : top;
                    } else {
                        top += heightStep / 2;
                        top = top < $imageContainer.height() - dimentions.height ?
                        $imageContainer.height() - dimentions.height : top;
                        top = top > 0 ? 0 : top;
                    }
                } else {
                    top = 0;
                }

                if (dimentions.width >= $imageContainer.width()) {
                    left += widthStep / 2;
                    left = left < $imageContainer.width() - dimentions.width ?
                    $imageContainer.width() - dimentions.width : left;
                    left = left > 0 ? 0 : left;
                } else {
                    left = 0;
                }

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
                widthResult,
                heightResult,
                dimentions = {};

            if (zoomShown && allowZoomIn) {
                $image = $(fullscreenImageSelector);
                imgOriginalSize = getImageSize($image[0].src);
                allowZoomOut = true;

                e.preventDefault();

                if (e.type === 'click' || e.type === 'touchstart') {
                    clickAnimation(e);
                }

                if (!zoomWidthStep) {
                    zoomWidthStep = Math.ceil((imgOriginalSize.rw - $image.width()) /
                        parseFloat(config.magnifierOpts.fullscreenzoom));
                    zoomHeightStep = Math.ceil((imgOriginalSize.rh - $image.height()) /
                        parseFloat(config.magnifierOpts.fullscreenzoom));
                }

                widthResult = $image.width() + zoomWidthStep;
                heightResult = $image.height() + zoomHeightStep;

                if (widthResult >= imgOriginalSize.rw) {
                    widthResult = imgOriginalSize.rw;
                }

                if (heightResult >= imgOriginalSize.rh) {
                    heightResult = imgOriginalSize.rh;
                }

                if (heightResult === imgOriginalSize.rh || widthResult === imgOriginalSize.rw) {
                    allowZoomIn = false;
                }

                if ($image.width() >= $image.height() && $image.width() !== imgOriginalSize.rw) {
                    dimentions = $.extend(dimentions, {
                        width: widthResult,
                        height: 'auto'
                    });
                    checkFullscreenImagePosition($image, dimentions, -zoomWidthStep, -zoomHeightStep);

                } else if ($image.width() < $image.height() && $image.height() !== imgOriginalSize.rh) {
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
                fitIntoParent;

            if (zoomShown && allowZoomOut) {
                allowZoomIn = true;
                $image = $(fullscreenImageSelector);
                widthResult = $image.width() - zoomWidthStep;
                heightResult = $image.height() - zoomHeightStep;
                parentWidth = $image.parent().width();
                parentHeight = $image.parent().height();
                imageWidth = $image.width();
                imageHeight = $image.height();

                e.preventDefault();

                if (e.type === 'click' || e.type === 'touchstart') {
                    clickAnimation(e);
                }

                fitIntoParent = function () {
                    if (parentHeight - imageHeight > parentWidth - imageWidth) {
                        widthResult = parentWidth;
                        dimentions = {
                            width: widthResult,
                            height: 'auto'
                        };
                    } else {
                        heightResult = parentHeight;
                        dimentions = {
                            width: 'auto',
                            height: heightResult
                        };
                    }
                    checkFullscreenImagePosition($image, dimentions, zoomWidthStep, zoomHeightStep);
                };

                if (imageWidth > imageHeight) {

                    if (widthResult > parentWidth || heightResult > parentHeight) {
                        dimentions = {
                            width: widthResult,
                            height: 'auto'
                        };
                        checkFullscreenImagePosition($image, dimentions, zoomWidthStep, zoomHeightStep);
                    } else {
                        allowZoomOut = dragFlag = false;
                        fitIntoParent();
                    }
                } else {

                    if (heightResult > parentHeight || widthResult > parentWidth) {
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

                    e.preventDefault ? e.preventDefault() : (e.returnValue = false);
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
                imagePosX,
                imagePosY,
                touch,
                $gallery = $(gallerySelector),
                $image = $(fullscreenImageSelector, $gallery),
                $imageContainer = $('[data-gallery-role="stage-shaft"] [data-active="true"]'),
                gallery = $gallery.data('fotorama');

            function shiftImage(dx, dy) {
                var top = +imagePosY + dy;
                var left = +imagePosX + dx;

                dragFlag = true;

                if ($image.height() > $imageContainer.height()) {

                    if (($imageContainer.offset().top + $imageContainer.height()) > (top + $image.height())) {
                        top = $imageContainer.offset().top + $imageContainer.height() - $image.height();
                    } else {
                        top = ($imageContainer.offset().top < top) ? 0 : top;
                    }
                    $image.offset({
                        'top': top
                    });
                }

                if ($image.width() > $imageContainer.width()) {

                    if (($imageContainer.offset().left + $imageContainer.width()) > (left + $image.width())) {
                        left = $imageContainer.offset().left + $imageContainer.width() - $image.width();
                    } else {
                        left = ($imageContainer.offset().left < left) ? $imageContainer.offset().left : left;
                    }
                    $image.offset({
                        'left': left
                    });
                }
            }

            toggleZoomButtons($image);

            $image.on(isTouchEnabled ? 'touchstart' : 'pointerdown mousedown MSPointerDown', function (e) {
                if (gallery.fullScreen) {
                    e.preventDefault();

                    $image.css('cursor', 'move');
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

                    $image.css({
                        transitionProperty: 'width, height'
                    });

                }
            });

            $image.on(isTouchEnabled ? 'touchmove' : 'mousemove pointermove MSPointerMove', function (e) {
                var top, left, clientX, clientY;

                if (gallery.fullScreen && isDragActive) {

                    clientX = e.clientX || e.originalEvent.clientX;
                    clientY = e.clientY || e.originalEvent.clientY;

                    e.preventDefault();

                    if (isTouchEnabled) {
                        touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
                        e.clientX = touch.pageX;
                        e.clientY = touch.pageY;
                    }
                    shiftImage((clientX - startX), (clientY - startY));
                }
            });

            $(window).keyup(function (e) {
                imagePosX = $(fullscreenImageSelector, $gallery).offset().left;
                imagePosY = $(fullscreenImageSelector, $gallery).offset().top;
                var step = 20;

                if (e.keyCode === 102) {
                    shiftImage(-step, 0);
                }
                if (e.keyCode === 98) {
                    shiftImage(0, step);
                }
                if (e.keyCode === 100) {
                    shiftImage(step, 0);
                }
                if (e.keyCode === 104) {
                    shiftImage(0, -step);
                }

            });

            $(document).on(isTouchEnabled ? 'touchend' : 'mouseup pointerup MSPointerUp', function () {
                if (gallery.fullScreen) {
                    isDragActive = false;
                    $image.css({
                        cursor: 'pointer',
                        transitionProperty: 'width, height, top, left'
                    });
                }
            });

            if (zoomShown) {
                $image.css({
                    width: $image.width(),
                    height: $image.height(),
                    maxWidth: 'none',
                    maxHeight: 'none'
                });
            }
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
                isClick = initPos[0] ===  pos[0] && initPos[1] ===  pos[1];

            if (isArrow || !isClick) {
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
                    hideMagnifier();
                    $(element).data('gallery').updateOptions({
                        swipe: false
                    });
                    magnifierFullscreen(e, fotorama);
                    mousewheel(e, fotorama, element);
                })
                .on('fotorama:load', function (e, fotorama) {
                    toggleZoomButtons($(fullscreenImageSelector));
                    magnifierFullscreen();
                })
                .on('fotorama:show', function (e, fotorama) {
                    resetVars($(fullscreenImageSelector));
                });
        });

        return config;
    }
});
