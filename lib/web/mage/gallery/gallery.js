/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'fotorama/fotorama',
    'underscore',
    'matchMedia',
    'mage/template',
    'text!mage/gallery/gallery.html',
    'uiClass',
    'mage/translate'
], function ($, fotorama, _, mediaCheck, template, galleryTpl, Class, $t) {
    'use strict';

    /**
     * Set main item first in order.
     * @param {Array.<Object>} data - Set of gallery items to update.
     */
    var pushMainFirst = function (data) {
            var mainIndex;

            if (!_.every(data, function (item) {
                    return _.isObject(item);
                })
            ) {
                return data;
            }

            mainIndex = _.findIndex(data, function (item) {
                return item.isMain;
            });

            if (mainIndex > -1) {
                data.unshift(data.splice(mainIndex, 1)[0]);
            }

            return data;
        },

        /**
         * Helper for parse translate property
         *
         * @param {Element} el - el that to parse
         * @returns {Array} - array of properties.
         */
        getTranslate = function (el) {
            var slideTransform = $(el).attr('style').split(';');

            slideTransform = $.map(slideTransform, function (style) {
                style = style.trim();

                if (style.startsWith('transform: translate3d')) {
                    return style.match(/transform: translate3d\((.+)px,(.+)px,(.+)px\)/);
                }

                return false;
            });

            return slideTransform.filter(Boolean);
        };

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
            var self = this;

            this._super();

            _.bindAll(this,
                '_focusSwitcher'
            );

            config.options.swipe = true;
            this.config = config;

            this.settings = {
                $element: $(element),
                $pageWrapper: $('body>.page-wrapper'),
                currentConfig: config,
                defaultConfig: _.clone(config),
                fullscreenConfig: _.clone(config.fullscreen),
                breakpoints: config.breakpoints,
                activeBreakpoint: {},
                fotoramaApi: null,
                isFullscreen: false,
                api: null,
                data: _.clone(pushMainFirst(config.data))
            };
            config.options.ratio = config.options.width / config.options.height;
            config.options.height = null;

            $.extend(true, this.startConfig, config);

            this.initGallery();
            this.initApi();
            this.setupBreakpoints();
            this.initFullscreenSettings();

            this.settings.$element.on('mouseup', '.fotorama__stage__frame', function () {
                if (
                    !$(this).parents('.fotorama__shadows--left, .fotorama__shadows--right').length &&
                    !$(this).hasClass('fotorama-video-container')
                ) {
                    self.openFullScreen();
                }
            });

            if (this.isTouchEnabled) {
                this.settings.$element.on('tap', '.fotorama__stage__frame', function () {
                    var translate = getTranslate($(this).parents('.fotorama__stage__shaft'));

                    if (translate[1] === '0' && !$(this).hasClass('fotorama-video-container')) {
                        self.openFullScreen();
                        self.settings.$pageWrapper.hide();
                    }
                });
            }
        },

        /**
         * Open gallery fullscreen
         */
        openFullScreen: function () {
            this.settings.api.fotorama.requestFullScreen();
            this.settings.$fullscreenIcon.css({
                opacity: 1,
                visibility: 'visible',
                display: 'block'
            });
        },

        /**
         * Gallery fullscreen settings.
         */
        initFullscreenSettings: function () {
            var settings = this.settings,
                self = this;

            settings.$gallery = this.settings.$element.find('[data-gallery-role="gallery"]');
            settings.$fullscreenIcon = this.settings.$element.find('[data-gallery-role="fotorama__fullscreen-icon"]');
            settings.focusableStart = this.settings.$element.find('[data-gallery-role="fotorama__focusable-start"]');
            settings.focusableEnd = this.settings.$element.find('[data-gallery-role="fotorama__focusable-end"]');
            settings.closeIcon = this.settings.$element.find('[data-gallery-role="fotorama__fullscreen-icon"]');
            settings.fullscreenConfig.swipe = true;

            settings.$gallery.on('fotorama:fullscreenenter', function () {
                settings.$gallery.focus();
                settings.focusableStart.bind('focusin', self._focusSwitcher);
                settings.focusableEnd.bind('focusin', self._focusSwitcher);
                settings.api.updateOptions(settings.defaultConfig.options, true);
                settings.api.updateOptions(settings.fullscreenConfig, true);

                if (!_.isEqual(settings.activeBreakpoint, {})) {
                    settings.api.updateOptions(settings.activeBreakpoint.options, true);
                }
                settings.isFullscreen = true;
            });

            settings.$gallery.on('fotorama:fullscreenexit', function () {
                settings.$pageWrapper.show();
                settings.api.updateOptions(settings.defaultConfig.options, true);
                settings.focusableStart.unbind('focusin', this._focusSwitcher);
                settings.focusableEnd.unbind('focusin', this._focusSwitcher);

                if (!_.isEqual(settings.activeBreakpoint, {})) {
                    settings.api.updateOptions(settings.activeBreakpoint.options, true);
                }
                settings.isFullscreen = false;
                settings.$fullscreenIcon.hide();
            });
        },

        /**
         * Switcher focus.
         */
        _focusSwitcher: function (e) {
            var target = $(e.target),
                settings = this.settings;

            if (target.is(settings.focusableStart)) {
                this._setFocus('start');
            } else if (target.is(settings.focusableEnd)) {
                this._setFocus('end');
            }
        },

        /**
         * Set focus to element.
         * @param {String} position - can be "start" and "end"
         *      positions.
         *      If position is "end" - sets focus to first
         *      focusable element in modal window scope.
         *      If position is "start" - sets focus to last
         *      focusable element in modal window scope
         */
        _setFocus: function (position) {
            var settings = this.settings,
                focusableElements,
                infelicity;

            if (position === 'end') {
                settings.$gallery.find(settings.closeIcon).focus();
            } else if (position === 'start') {
                infelicity = 2; //Constant for find last focusable element
                focusableElements = settings.$gallery.find(':focusable');
                focusableElements.eq(focusableElements.length - infelicity).focus();
            }
        },

        /**
         * Initializes gallery with configuration options.
         */
        initGallery: function () {
            var breakpoints = {},
                settings = this.settings,
                config = this.config,
                tpl = template(galleryTpl, {
                    next: $t('Next'),
                    previous: $t('Previous')
                });

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

            config.click = false;
            config.breakpoints = null;
            settings.currentConfig = config;
            settings.$element.html(tpl);
            settings.$element.removeClass('_block-content-loading');
            settings.$elementF = $(settings.$element.children()[0]);
            settings.$elementF.fotorama(config);
            settings.fotoramaApi = settings.$elementF.data('fotorama');
            $.extend(true, config, this.startConfig);
        },

        /**
         * Creates breakpoints for gallery.
         */
        setupBreakpoints: function () {
            var pairs,
                settings = this.settings,
                config = this.config,
                startConfig = this.startConfig;

            if (_.isObject(settings.breakpoints)) {
                pairs = _.pairs(settings.breakpoints);
                _.each(pairs, function (pair) {
                    mediaCheck({
                        media: pair[0],

                        /**
                         * Is triggered when breakpoint enties.
                         */
                        entry: function () {
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
                            $.extend(true, config, _.clone(startConfig));
                            settings.api.updateOptions(settings.defaultConfig.options, true);

                            if (settings.isFullscreen) {
                                settings.api.updateOptions(settings.fullscreenConfig, true);
                            }
                            settings.activeBreakpoint = {};
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
                        settings.fotoramaApi.show('>>');
                    },

                    /**
                     * Displays the first image on preview.
                     */
                    first: function () {
                        settings.fotoramaApi.show('<<');
                    },

                    /**
                     * Displays previous element on preview.
                     */
                    prev: function () {
                        settings.fotoramaApi.show('<');
                    },

                    /**
                     * Displays next element on preview.
                     */
                    next: function () {
                        settings.fotoramaApi.show('>');
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
                            settings.fotoramaApi.show(index);
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
                            settings.fotoramaApi.setOptions(settings.currentConfig.options);
                        }
                    },

                    /**
                     * Updates gallery with specific set of items.
                     * @param {Array.<Object>} data - Set of gallery items to update.
                     */
                    updateData: function (data) {
                        if (_.isArray(data)) {
                            settings.fotoramaApi.load(data);

                            $.extend(false, settings, {
                                data: data,
                                defaultConfig: data
                            });
                            $.extend(false, config, {
                                data: data
                            });
                        }
                    },

                    /**
                     * Returns current images list
                     *
                     * @returns {Array}
                     */
                    returnCurrentImages: function () {
                        var images = [];

                        _.each(this.fotorama.data, function (item) {
                            images.push(_.omit(item, '$navThumbFrame', '$navDotFrame', '$stageFrame'));
                        });

                        return images;
                    }
                };

            settings.$element.data('gallery', api);
            settings.api = settings.$element.data('gallery');
            settings.$element.trigger('gallery:loaded');
        }
    });
});
