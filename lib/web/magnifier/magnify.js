define([
    'jquery',
    'magnifier/magnifier',
    'underscore'
], function ($, magnifier, _) {
    'use strict';

    return function (config, element) {

        var isTouchEnabled = 'ontouchstart' in document.documentElement;

        if (isTouchEnabled) {
            $(element).on('fotorama:showend fotorama:load', function (e, fotorama) {
                $("[data-gallery-role='magnifier']").remove();
            });
            return config;
        }

        if (config.magnifierOpts.eventType === 'click') {
            config.options.swipe = false;
        }

        $.extend(config.magnifierOpts, {
            zoomable: false,
            thumb: ".fotorama__img",
            largeWrapper: "[data-gallery-role='magnifier']"
        });

        $(element).on('fotorama:showend fotorama:load', function (e, fotorama) {
            $("[data-gallery-role='magnifier']").empty().hide();
            $("[data-gallery-role='magnifier-zoom']").remove();
            config.magnifierOpts.large = $("[data-gallery-role='gallery']").data('fotorama').activeFrame.img;
            $($("[data-gallery-role='gallery']").data('fotorama').activeFrame.$stageFrame).magnify(config.magnifierOpts);
        });
        $(element).on('fotorama:show', function (e, fotorama) {
            $("[data-gallery-role='magnifier']").empty().hide();
            $("[data-gallery-role='magnifier-zoom']").remove();
        });
        return config;
    };
});
