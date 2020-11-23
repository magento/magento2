/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Retrieves index if the main item.
     * @param {Array.<Object>} data - Set of gallery items.
     */
    var getMainImageIndex = function (data) {
            var mainIndex;

            if (_.every(data, function (item) {
                    return _.isObject(item);
                })
            ) {
                mainIndex = _.findIndex(data, function (item) {
                    return item.isMain;
                });
            }

            return mainIndex > 0 ? mainIndex : 0;
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
        },

        /**
         * @param {*} str
         * @return {*}
         * @private
         */
        _toNumber = function (str) {
            var type = typeof str;

            if (type === 'string') {
                return parseInt(str); //eslint-disable-line radix
            }

            return str;
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

            /*turn off arrows for touch devices*/
            if (this.isTouchEnabled) {
                config.options.arrows = false;

                if (config.fullscreen) {
                    config.fullscreen.arrows = false;
                }
            }

            config.options.width = _toNumber(config.options.width);
            config.options.height = _toNumber(config.options.height);
            config.options.thumbwidth = _toNumber(config.options.thumbwidth);
            config.options.thumbheight = _toNumber(config.options.thumbheight);

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
                data: _.clone(config.data)
            };
            config.options.ratio = config.options.width / config.options.height;
            config.options.height = null;

            $.extend(true, this.startConfig, config);

            this.initGallery();
            this.initApi();
            this.setupBreakpoints();
            this.initFullscreenSettings();

            this.settings.$element.on('click', '.fotorama__stage__frame', function () {
                if (
                    !$(this).parents('.fotorama__shadows--left, .fotorama__shadows--right').length &&
                    !$(this).hasClass('fotorama-video-container')
                ) {
                    self.openFullScreen();
                }
            });

            if (this.isTouchEnabled && this.settings.isFullscreen) {
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
                settings.closeIcon.show();
                settings.focusableStart.attr('tabindex', '0');
                settings.focusableEnd.attr('tabindex', '0');
                settings.focusableStart.bind('focusin', self._focusSwitcher);
                settings.focusableEnd.bind('focusin', self._focusSwitcher);
                settings.api.updateOptions(settings.defaultConfig.options, true);
                settings.api.updateOptions(settings.fullscreenConfig, true);

                if (!_.isEqual(settings.activeBreakpoint, {}) && settings.breakpoints) {
                    settings.api.updateOptions(settings.activeBreakpoint.options, true);
                }
                settings.isFullscreen = true;
            });

            settings.$gallery.on('fotorama:fullscreenexit', function () {
                settings.closeIcon.hide();
                settings.focusableStart.attr('tabindex', '-1');
                settings.focusableEnd.attr('tabindex', '-1');
                settings.api.updateOptions(settings.defaultConfig.options, true);
                settings.focusableStart.unbind('focusin', this._focusSwitcher);
                settings.focusableEnd.unbind('focusin', this._focusSwitcher);
                settings.closeIcon.hide();

                if (!_.isEqual(settings.activeBreakpoint, {}) && settings.breakpoints) {
                    settings.api.updateOptions(settings.activeBreakpoint.options, true);
                }
                settings.isFullscreen = false;
                settings.$element.data('gallery').updateOptions({
                    swipe: true
                });
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
                infelicity = 3; //Constant for find last focusable element
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
                }),
                mainImageIndex,
                $element = settings.$element,
                $fotoramaElement,
                $fotoramaStage;

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

            _.extend(config, config.options,
                {
                    options: undefined,
                    click: false,
                    breakpoints: null
                }
            );
            settings.currentConfig = config;

            $element
                .css('min-height', settings.$element.height())
                .append(tpl);

            $fotoramaElement = $element.find('[data-gallery-role="gallery"]');

            $fotoramaStage = $fotoramaElement.find('.fotorama__stage');
            $fotoramaStage.css('position', 'absolute');

            $fotoramaElement.fotorama(config);
            $fotoramaElement.find('.fotorama__stage__frame.fotorama__active')
                    .one('f:load', function () {
                        // Remove placeholder when main gallery image loads.
                        $element.find('.gallery-placeholder__image').remove();
                        $element
                            .removeClass('_block-content-loading')
                            .css('min-height', '');

                        $fotoramaStage.css('position', '');
                    });
            settings.$elementF = $fotoramaElement;
            settings.fotoramaApi = $fotoramaElement.data('fotorama');

            $.extend(true, config, this.startConfig);

            mainImageIndex = getMainImageIndex(config.data);

            if (mainImageIndex) {
                this.settings.fotoramaApi.show({
                    index: mainImageIndex,
                    time: 0
                });
            }
        },

        /**
         * Creates breakpoints for gallery.
         */
        setupBreakpoints: function () {
            var pairs,
                settings = this.settings,
                config = this.config,
                startConfig = this.startConfig,
                isInitialized = {},
                isTouchEnabled = this.isTouchEnabled;

            if (_.isObject(settings.breakpoints)) {
                pairs = _.pairs(settings.breakpoints);
                _.each(pairs, function (pair) {
                    var mediaQuery = pair[0];

                    isInitialized[mediaQuery] = false;
                    mediaCheck({
                        media: mediaQuery,

                        /**
                         * Is triggered when breakpoint enties.
                         */
                        entry: function () {
                            $.extend(true, config, _.clone(startConfig));

                            settings.api.updateOptions(settings.defaultConfig.options, true);

                            if (settings.isFullscreen) {
                                settings.api.updateOptions(settings.fullscreenConfig, true);
                            }

                            if (isTouchEnabled) {
                                settings.breakpoints[mediaQuery].options.arrows = false;

                                if (settings.breakpoints[mediaQuery].options.fullscreen) {
                                    settings.breakpoints[mediaQuery].options.fullscreen.arrows = false;
                                }
                            }

                            settings.api.updateOptions(settings.breakpoints[mediaQuery].options, true);
                            $.extend(true, config, settings.breakpoints[mediaQuery]);
                            settings.activeBreakpoint = settings.breakpoints[mediaQuery];

                            isInitialized[mediaQuery] = true;
                        },

                        /**
                         * Is triggered when breakpoint exits.
                         */
                        exit: function () {
                            if (isInitialized[mediaQuery]) {
                                $.extend(true, config, _.clone(startConfig));
                                settings.api.updateOptions(settings.defaultConfig.options, true);

                                if (settings.isFullscreen) {
                                    settings.api.updateOptions(settings.fullscreenConfig, true);
                                }
                                settings.activeBreakpoint = {};
                            } else {
                                isInitialized[mediaQuery] = true;
                            }
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

                        var $selectable = $('a[href], area[href], input, select, ' +
                                'textarea, button, iframe, object, embed, *[tabindex], *[contenteditable]')
                                .not('[tabindex=-1], [disabled], :hidden'),
                            $focus = $(':focus'),
                            index;

                        if (_.isObject(configuration)) {

                            //Saves index of focus
                            $selectable.each(function (number) {
                                if ($(this).is($focus)) {
                                    index = number;
                                }
                            });

                            if (this.isTouchEnabled) {
                                configuration.arrows = false;
                            }
                            configuration.click = false;
                            configuration.breakpoints = null;

                            if (!isInternal) {
                                !_.isEqual(settings.activeBreakpoint, {} && settings.brekpoints) ?
                                    $.extend(true, settings.activeBreakpoint.options, configuration) :

                                    settings.isFullscreen ?
                                        $.extend(true, settings.fullscreenConfig, configuration) :
                                        $.extend(true, settings.defaultConfig.options, configuration);

                            }
                            $.extend(true, settings.currentConfig.options, configuration);
                            settings.fotoramaApi.setOptions(settings.currentConfig.options);

                            if (_.isNumber(index)) {
                                $selectable.eq(index).focus();
                            }
                        }
                    },

                    /**
                     * Updates gallery with specific set of items.
                     * @param {Array.<Object>} data - Set of gallery items to update.
                     */
                    updateData: function (data) {
                        var mainImageIndex;

                        if (_.isArray(data)) {
                            settings.fotoramaApi.load(data);
                            mainImageIndex = getMainImageIndex(data);

                            if (settings.fotoramaApi.activeIndex !== mainImageIndex) {
                                settings.fotoramaApi.show({
                                    index: mainImageIndex,
                                    time: 0
                                });
                            }

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
                            images.push(_.omit(item, '$navThumbFrame', '$navDotFrame', '$stageFrame', 'labelledby'));
                        });

                        return images;
                    },

                    /**
                     * Updates gallery data partially by index
                     * @param {Number} index - Index of image in data array to be updated.
                     * @param {Object} item - Standart gallery image object.
                     *
                     */
                    updateDataByIndex: function (index, item) {
                        settings.fotoramaApi.spliceByIndex(index, item);
                    }
                };

            settings.$element.data('gallery', api);
            settings.api = settings.$element.data('gallery');
            settings.$element.trigger('gallery:loaded');
        }
    });
});
