/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'fotorama/fotorama',
    'underscore',
    'matchMedia',
    'text!mage/gallery/gallery.html',
    'Magento_Ui/js/lib/class'
], function ($, fotorama, _, mediaCheck, template, Class) {
    'use strict';

    return Class.extend({

        defaults: {
            settings: {},
            config: {},
            startConfig: {}
        },

        /**
         * Checks if device has touch interface.
         * @return {Boolean} The result of searching touch events on device.
         */
        isTouchEnabled: (function () {
            return 'ontouchstart' in document.documentElement;
        })(),

        /**
         * Initializes gallery.
         * @param {Object} config - Gallery configuration.
         * @param {String} element - String selector of gallery DOM element.
         */
        initialize: function (config, element) {
            this._super();

            this.config = config;

            this.settings = {
                $element: $(element),
                currentConfig: config,
                defaultConfig: _.clone(config),
                fullscreenConfig: _.clone(config.allowfullscreen),
                breakpoints: config.breakpoints,
                activeBreakpoint: {},
                fotoramaApi: null,
                isFullscreen: false,
                api: null
            };

            config.options.ratio = config.options.width / config.options.height;
            config.options.height = null;
            config.options.allowfullscreen = false;

            $.extend(true, this.startConfig, config);

            this.initGallery();
            this.initApi();
            this.setupBreakpoints();
            this.initFullscreenSettings();
        },

        /**
         * Gallery fullscreen settings.
         */
        initFullscreenSettings: function () {
            var settings = this.settings;
            settings.$element.on('fotorama:fullscreenenter', function () {
                settings.api.updateOptions(settings.defaultConfig.options, true);
                settings.api.updateOptions(settings.fullscreenConfig, true);

                if (!_.isEqual(settings.activeBreakpoint, {})) {
                    settings.api.updateOptions(settings.activeBreakpoint.options, true);
                }
                settings.isFullscreen = true;
            });

            settings.$element.on('fotorama:fullscreenexit', function () {
                settings.api.updateOptions(settings.defaultConfig.options, true);

                if (!_.isEqual(settings.activeBreakpoint, {})) {
                    settings.api.updateOptions(settings.activeBreakpoint.options, true);
                }
                settings.isFullscreen = false;
            });
        },

        /**
         * Initializes gallery with configuration options.
         */
        initGallery: function () {
            var breakpoints = {},
                settings = this.settings,
                config = this.config,
                mainImage;

            if (settings.breakpoints) {
                _.each(_.values(settings.breakpoints), function (breakpoint) {
                    var conditions;
                    _.each(_.pairs(breakpoint.conditions), function (pair) {
                        conditions = conditions ? conditions + ' and (' + pair[0] + ': ' + pair[1] + ')' :
                            '(' + pair[0] + ': ' + pair[1] + ')';
                    });
                    breakpoints[conditions] = breakpoint.options;
                });
                settings.breakpoints = breakpoints;
            }

            _.extend(config, config.options);
            config.options = undefined;

            if (this.isTouchEnabled) {
                config.arrows = false;
            }

            /**
             * Returns index of main image.
             */
            mainImage = _.findIndex(config.data, function (obj) {
                return obj.isMain === true;
            });

            config.click = false;
            config.breakpoints = null;
            config.startindex = mainImage;
            settings.currentConfig = config;
            settings.$element.html(template);
            settings.$element = $(settings.$element.children()[0]);
            settings.$element.fotorama(config);
            settings.fotoramaApi = settings.$element.data('fotorama');
            $.extend(true, config, this.startConfig);
        },

        /**
         * Creates breakpoints for gallery.
         */
        setupBreakpoints: function () {
            var pairs,
                settings = this.settings,
                config = this.config,
                startConfig = this.startConfig,
                triggeredBreakpoints = 0;

            if (_.isObject(settings.breakpoints)) {
                pairs = _.pairs(settings.breakpoints);
                _.each(pairs, function (pair) {
                    mediaCheck({
                        media: pair[0],

                        /**
                         * Is triggered when breakpoint enties.
                         */
                        entry: function () {
                            triggeredBreakpoints++;
                            $.extend(true, config, _.clone(startConfig));

                            settings.api.updateOptions(settings.defaultConfig.options, true);

                            if (settings.isFullscreen) {
                                settings.api.updateOptions(settings.fullscreenConfig, true);
                            }
                            settings.api.updateOptions(settings.breakpoints[pair[0]].options, true);
                            $.extend(true, config, settings.breakpoints[pair[0]]);
                            settings.activeBreakpoint = settings.breakpoints[pair[0]];
                        },

                        /**
                         * Is triggered when breakpoint exits.
                         */
                        exit: function () {
                            if (triggeredBreakpoints < 1) {
                                $.extend(true, config, _.clone(startConfig));
                                settings.api.updateOptions(settings.defaultConfig.options, true);

                                if (settings.isFullscreen) {
                                    settings.api.updateOptions(settings.fullscreenConfig, true);
                                }
                                settings.activeBreakpoint = {};
                            }
                            triggeredBreakpoints--;
                        }
                    });
                });
            }
        },

        /**
         * Creates gallery's API.
         */
        initApi: function () {
            var settings = this.settings,
                config = this.config,
                api = {

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
                     * @param {Object} configuration - Standart gallery configuration object.
                     * @param {Boolean} isInternal - Is this function called via breakpoints.
                     */
                    updateOptions: function (configuration, isInternal) {
                        if (_.isObject(configuration)) {
                            if (this.isTouchEnabled) {
                                configuration.arrows = false;
                            }
                            configuration.click = false;
                            configuration.breakpoints = null;

                            if (!isInternal) {
                                !_.isEqual(settings.activeBreakpoint, {}) ?
                                    $.extend(true, settings.activeBreakpoint.options, configuration) :

                                    settings.isFullscreen ?
                                        $.extend(true, settings.fullscreenConfig, configuration) :
                                        $.extend(true, settings.defaultConfig.options, configuration);

                            }
                            $.extend(true, settings.currentConfig.options, configuration);
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
        }
    });
});
