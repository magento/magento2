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
            hideMagnifier,
            behaveOnHover;

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

        function zoomIn(e) {
            var $image = $('[data-gallery-role="stage-shaft"] [data-active="true"] .magnify-fullimage'),
                gallery = $(gallerySelector),
                imgMaxSize,
                setedResult;
            e.preventDefault();
            if ($image.length && $image[0].src) {
                imgMaxSize = getImageSize($image[0].src);
                setedResult = Math.round($image.width() + 10);
                $image.css({'width': setedResult, height: 'auto'});
            }
            checkFullscreenImagePosition();
        }

        function zoomOut(e) {
            var $image = $('[data-gallery-role="stage-shaft"] [data-active="true"] .magnify-fullimage'),
                gallery = $(gallerySelector),
                imgMaxSize,
                setedResult;
            e.preventDefault();
            if ($image.length && $image[0].src) {
                imgMaxSize = getImageSize($image[0].src);
                setedResult = Math.round($image.width() - 10);
                $image.css({'width': setedResult, height: 'auto'});
            }
            checkFullscreenImagePosition();
        }

        /**
         * Bind event on scroll on active item in fotorama
         * @param e
         * @param fotorama - object of fotorama
         */
        function mousewheel(e, fotorama, element) {
            var $fotoramaStage = $(element).find('[data-fotorama-stage="fotorama__stage"]'),
                fotoramaStage = $fotoramaStage.get(0);
            if (fotoramaStage.addEventListener) {
                if ('onwheel' in document) {
                    fotoramaStage.addEventListener("wheel", onWheel);
                } else if ('onmousewheel' in document) {
                    fotoramaStage.addEventListener("mousewheel", onWheel);
                } else {
                    fotoramaStage.addEventListener("MozMousePixelScroll", onWheel);
                }
            }

            function onWheel(e) {
                if ($('[data-gallery-role="gallery"]').data('fotorama').fullScreen) {
                    e = e || window.event;
                    var delta = e.deltaY || e.detail || e.wheelDelta;
                    if (delta > 0) {
                        zoomOut(e);
                    } else {
                        zoomIn(e);
                    }

                    e.preventDefault ? e.preventDefault() : (e.returnValue = false);
                }
            }

        }

        function checkFullscreenImagePosition() {
            if ($('[data-gallery-role="gallery"]').data('fotorama').fullScreen) {

                var $preview = $('[data-gallery-role="stage-shaft"] [data-active="true"] img'),
                    $image = $('[data-gallery-role="stage-shaft"] [data-active="true"] .magnify-fullimage'),
                    $imageContainer = $preview.parent(),
                    gallery = $('[data-gallery-role="gallery"]'),
                    top, left;

                if (($imageContainer.offset().top + $imageContainer.height()) > ($image.offset().top + $image.height())) {
                    top = $imageContainer.offset().top + $imageContainer.height() - $image.height();
                } else {
                    top = ($imageContainer.offset().top < $image.offset().top) ? 0 : top;
                }

                if (top !== undefined) {
                    $image.css('top', top);
                }

                if (($imageContainer.offset().left + $imageContainer.width()) > ($image.offset().left + $image.width())) {
                    left = $imageContainer.offset().left + $imageContainer.width() - $image.width();
                } else {
                    left = ($imageContainer.offset().left < $image.offset().left) ? 0 : left;
                }

                if (left !== undefined) {
                    $image.css('left', left);
                }

                if ($image.width() < $imageContainer.width()) {
                    $image.css('left', '');
                }

                if ($image.height() < $imageContainer.height()) {
                    $image.css('top', '');
                }
            }
        }

        /**
         * Metod which makes draggable picture. Also work
         * on tauch devices.
         * @param e - event object
         * @param fotorama - fotorama object
         */
        function magnifierFullscreen (e, fotorama) {
            var isDragActive = false,
                startX,
                startY,
                imagePosX,
                imagePosY,
                touch,
                isTouchEnabled = 'ontouchstart' in document.documentElement;

            var $gallery = $('[data-gallery-role="gallery"]'),
                $preview = $('[data-gallery-role="stage-shaft"] [data-active="true"] img', $gallery),
                $image = $('[data-gallery-role="stage-shaft"] [data-active="true"] .magnify-fullimage', $gallery),
                $imageContainer = $preview.parent(),
                gallery = $gallery.data('fotorama');
            console.log("asdasdad");
            if (gallery.fullScreen) {
                if (!$imageContainer.find('.magnify-fullimage').length) {
                    $imageContainer.append('<img class="magnify-fullimage" src ="' + gallery.options.data[gallery.activeIndex].original + '"/>');
                }
            }

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
                }
            });



            $image.on(isTouchEnabled ? 'touchmove' : 'mousemove pointermove MSPointerMove', function (e) {
                if (gallery.fullScreen && isDragActive) {

                    var top,
                        left,
                        clientX = e.clientX || e.originalEvent.clientX,
                        clientY = e.clientY || e.originalEvent.clientY;


                    e.preventDefault();

                    if (isTouchEnabled) {
                        touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
                        e.clientX = touch.pageX;
                        e.clientY = touch.pageY;
                    }
                    top = +imagePosY + (clientY - startY);
                    left = +imagePosX + (clientX - startX);

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
            });

            $image.on(isTouchEnabled ? 'touchend' : 'mouseup pointerup MSPointerUp', function (e) {
                if (gallery.fullScreen) {
                    isDragActive = false;
                    $image.css('cursor', 'pointer');
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
        $(element).on('gallery:loaded', function (e, fotorama) {
            $(element).find(gallerySelector)
                .on('fotorama:ready', function (e, fotorama) {
                    var $zoomIn = $('[data-zoom-in="fotorama__zoom-in"]'),
                        $zoomOut = $('[data-zoom-out="fotorama__zoom-out"]');
                    if ( !$zoomIn.hasClass('zoom-in-loaded') ) {
                        $zoomIn.on('click touchstart', zoomIn);
                        $zoomIn.addClass('zoom-in-loaded');
                    }
                    if ( !$zoomOut.hasClass('zoom-out-loaded') ) {
                        $zoomOut.on('click touchstart', zoomOut);
                        $zoomOut.addClass('zoom-out-loaded');
                    }

                })
                .on('fotorama:fullscreenenter', function (e, fotorama) {
                    magnifierFullscreen(e, fotorama);
                    mousewheel(e, fotorama, element);
                    fotorama.setOptions({swipe: false});
                })
                .on('fotorama:load', function (e, fotorama) {
                    magnifierFullscreen(e, fotorama);
                    mousewheel(e, fotorama, element);
                })
                .on('fotorama:show fotorama:showend', function (e, fotorama) {
                    magnifierFullscreen(e, fotorama);
                });
        });

        return config;
    };
});
