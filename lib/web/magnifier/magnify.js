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
            fullScreenIcon = '[data-gallery-role="fotorama__fullscreen-icon"]',
            hideMagnifier,
            behaveOnHover;

        if (isTouchEnabled) {
            $(element).on('fotorama:showend fotorama:load', function () {
                $(magnifierSelector).remove();
                $(magnifierZoomSelector).remove();
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
                isClick = initPos[0] === pos[0] && initPos[1] === pos[1];
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
            thumb: '.fotorama__img:not(".fotorama__img--full")',
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

        $(element).on('fotorama:showend fotorama:load fotorama:fullscreenexit fotorama:ready', function (e, fotorama) {
            hideMagnifier();
            config.magnifierOpts.large = $(gallerySelector).data('fotorama').activeFrame.img;
            config.magnifierOpts.full = fotorama.data[fotorama.activeIndex].full;
            $($(gallerySelector).data('fotorama').activeFrame.$stageFrame).magnify(config.magnifierOpts);
        });
        $(element).on('gallery:loaded', function () {
            $(element).find(gallerySelector).on('fotorama:show fotorama:fullscreenenter ', function () {
                hideMagnifier();
            });
        });

        return config;
    };
});
