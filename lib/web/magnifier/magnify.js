define([
    'jquery',
    'magnifier/magnifier'
], function ($) {
    'use strict';

    return function (config, element) {

        var isTouchEnabled = 'ontouchstart' in document.documentElement,
            gallerySelector = '[data-gallery-role="gallery"]',
            magnifierSelector = '[data-gallery-role="magnifier"]',
            magnifierZoomSelector = '[data-gallery-role="magnifier-zoom"]';

        if (isTouchEnabled) {
            $(element).on('fotorama:showend fotorama:load', function () {
                $(magnifierSelector).remove();
            });

            return config;
        }

        if (config.magnifierOpts.eventType === 'click') {
            config.options.swipe = false;
        }

        $.extend(config.magnifierOpts, {
            zoomable: false,
            thumb: '.fotorama__img',
            largeWrapper: '[data-gallery-role="magnifier"]',
            height: config.magnifierOpts.height || function () {
                return $('[data-active="true"]').width() / config.options.ratio;
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

        $(element).on('fotorama:showend fotorama:load', function (e, fotorama) {
            var originalImg = fotorama.data[fotorama.activeIndex].original;
            $(magnifierSelector).empty().hide();
            $(magnifierZoomSelector).remove();
            config.magnifierOpts.large = $(gallerySelector).data('fotorama').activeFrame.img;
            config.magnifierOpts.original = originalImg;
            $($(gallerySelector).data('fotorama').activeFrame.$stageFrame).magnify(config.magnifierOpts);
        });
        $(element).on('fotorama:show', function () {
            $(magnifierSelector).empty().hide();
            $(magnifierZoomSelector).remove();
        });

        return config;
    };
});
