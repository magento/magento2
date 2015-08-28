/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'fotorama/fotorama',
    'underscore',
    'matchMedia',
    'text!mage/gallery/gallery.html'
], function ($, fotorama, _, mediaCheck, template) {
    'use strict';

    return function (config, element) {

        var triggeredBreakpoints = 0,
            settings = {},
            isTouchEnabled,
            initGallery,
            setupBreakpoints,
            initApi,
            initConfig = {};

        config.options.ratio = config.options.width / config.options.height;
        config.options.height = null;

        settings = {
            $element: $(element),
            currentConfig: config.options,
            defaultConfig: config.options,
            breakpoints: config.breakpoints,
            fotoramaApi: null,
            api: null
        };

        $.extend(true, initConfig, config);

        /**
         * Checks if device has touch interface.
         * @return {Boolean} The result of searching touch events on device.
         */
        isTouchEnabled = (function () {
            return 'ontouchstart' in document.documentElement;
        })();

        /**
         * Initializes gallery with configuration options.
         */
        initGallery = function () {
            var breakpoints = {},
                mainImage;

            if (settings.breakpoints) {
                _.each(_.values(settings.breakpoints), function (breakpoint) {
                    var conditions;
                    _.each(_.pairs(breakpoint.conditions), function (pair) {
                        conditions = conditions ? conditions + ' and (' + pair[0] + ': ' + pair[1] + ')' : '(' + pair[0] + ': ' + pair[1] + ')';
                    });
                    breakpoints[conditions] = breakpoint.options;
                });
                settings.breakpoints = breakpoints;
            }

            _.extend(config, config.options);
            config.options = undefined;

            if (isTouchEnabled) {
                config.arrows = false;
            }

            /**
             * Returns index of main image.
             */
            mainImage = function () {
                var mainImgIndex;
                config.data.forEach(function (item, index) {

                    if (item.is_main) {
                        mainImgIndex = index;
                    }
                });

                return mainImgIndex;
            };

            config.click = false;
            config.breakpoints = null;
            config.startindex = mainImage();
            settings.currentConfig = config;
            $.extend(true, settings.defaultConfig, config);
            settings.$element.html(template);
            settings.$element = $(settings.$element.children()[0]);
            settings.$element.fotorama(config);
            settings.fotoramaApi = settings.$element.data('fotorama');
            $.extend(true, config, initConfig);
        };

        /**
         * Creates breakpoints for gallery.
         * @param {Object} breakpoints - Object with keys as media queries and values as configurations.
         */
        setupBreakpoints = function (breakpoints) {
            var pairs;

            if (_.isObject(breakpoints)) {
                pairs = _.pairs(breakpoints);
                _.each(pairs, function (pair) {
                    var initialized = 0;
                    mediaCheck({
                        media: pair[0],

                        /**
                         * Is triggered when breakpoint enties.
                         */
                        entry: function () {
                            triggeredBreakpoints++;
                            initialized = initialized < pairs.length ? initialized++ : initialized;
                            settings.api.updateOptions(settings.defaultConfig, true);
                            $.extend(true, config, initConfig);

                            settings.api.updateOptions(settings.defaultConfig.options, true);
                            settings.currentConfig = settings.breakpoints[pair[0]];
                            settings.api.updateOptions(settings.currentConfig.options, true);

                            $.extend(true, config, settings.currentConfig);
                            settings.$element.trigger('gallery:updated', $('.fotorama-item').data('fotorama'));
                        },

                        /**
                         * Is triggered when breakpoint exits.
                         */
                        exit: function () {
                            triggeredBreakpoints = triggeredBreakpoints > 0 ? triggeredBreakpoints-- : 0;
                            initialized = initialized < pairs.length ? initialized++ : initialized;

                            if (!triggeredBreakpoints && initialized === pairs.length) {
                                settings.currentConfig = settings.defaultConfig;
                                settings.api.updateOptions(settings.currentConfig.options, true);
                                $.extend(true, config, initConfig);
                                settings.$element.trigger('gallery:updated', settings.fotoramaApi);
                            }
                        }
                    });
                });
            }
        };

        /**
         * Creates gallery's API.
         */
        initApi = function () {
            var api = {

                /**
                 * Contains fotorama's API methods.
                 */
                fotorama: settings.fotoramaApi,

                /**
                 * Displays the last image on preview.
                 */
                last: function () {
                    this.fotorama.show('>>');
                },

                /**
                 * Displays the first image on preview.
                 */
                first: function () {
                    this.fotorama.show('<<');
                },

                /**
                 * Displays previous element on preview.
                 */
                prev: function () {
                    this.fotorama.show('<');
                },

                /**
                 * Displays next element on preview.
                 */
                next: function () {
                    this.fotorama.show('>');
                },

                /**
                 * Displays image with appropriate count number on preview.
                 * @param {Number} index - Number of image that should be displayed.
                 */
                seek: function (index) {

                    if (_.isNumber(index) && index !== 0) {

                        if (index > 0) {
                            index -= 1;
                        }
                        this.fotorama.show(index);
                    }
                },

                /**
                 * Updates gallery with new set of options.
                 * @param {Object} config - Standart gallery configuration object.
                 * @param {Boolean} isInternal - Is this function called via breakpoints.
                 */
                updateOptions: function (config, isInternal) {

                    if (_.isObject(config)) {

                        if (isTouchEnabled) {
                            config.arrows = false;
                        }
                        config.click = false;
                        setupBreakpoints(config.breakpoints);
                        config.breakpoints = null;

                        if (!isInternal) {
                            $.extend(true, settings.currentConfig.options, config);
                        }
                        this.fotorama.setOptions(settings.currentConfig.options);
                    }
                },

                /**
                 * Updates gallery with specific set of items.
                 * @param {Array.<Object>} data - Set of gallery items to update.
                 */
                updateData: function (data) {

                    if (_.isArray(data)) {
                        this.fotorama.load(data);
                        //_.extend(settings.currentConfig, {data: data});
                        $.extend(false, settings.defaultConfig, {
                            data: data
                        });
                        $.extend(false, config, {
                            data: data
                        });
                    }
                }
            };
            settings.$element.data('gallery', api);
            settings.api = settings.$element.data('gallery');
            settings.$element.trigger('gallery:loaded');
        };

        initGallery();
        initApi();
        setupBreakpoints(settings.breakpoints);
    };
});
