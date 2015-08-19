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
                $('.magnifier-preview').remove();
            });
            return config;
        }

        if (config.magnifierOpts.eventType === 'click') {
            config.options.swipe = false;
        }

        $(element).on('fotorama:showend fotorama:load', function (e, fotorama) {
            $('.magnifier-preview').empty().hide();
            $('.magnify-lens').remove();
            config.magnifierOpts.large = $('.fotorama-item').data('fotorama').activeFrame.img;
            $($('.fotorama-item').data('fotorama').activeFrame.$stageFrame).magnify(config.magnifierOpts);
        });
        $(element).on('fotorama:show', function (e, fotorama) {
            $('.magnifier-preview').empty().hide();
            $('.magnify-lens').remove();
        });
        return config;
    };
});
