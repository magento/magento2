/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 @version 0.0.1
 @requires jQuery & jQuery UI
 */
define([
    'jquery',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';

    var videoRegister = {
        _register: {},

        /**
         * Checks, if api is already registered
         *
         * @param {String} api
         * @returns {bool}
         */
        isRegistered: function (api) {
            return this._register[api] !== undefined;
        },

        /**
         * Checks, if api is loaded
         *
         * @param {String} api
         * @returns {bool}
         */
        isLoaded: function (api) {
            return this._register[api] !== undefined && this._register[api] === true;
        },

        /**
         * Register new video api
         * @param {String} api
         * @param {bool} loaded
         */
        register: function (api, loaded) {
            loaded = loaded || false;
            this._register[api] = loaded;
        }
    };

    $.widget('mage.productVideoLoader', {

        /**
         * @private
         */
        _create: function () {
            switch (this.element.data('type')) {
                case 'youtube':
                    this.element.videoYoutube();
                    this._player = this.element.data('mageVideoYoutube');
                    break;

                case 'vimeo':
                    this.element.videoVimeo();
                    this._player = this.element.data('mageVideoVimeo');
                    break;
                default:
                    throw {
                        name: 'Video Error',
                        message: 'Unknown video type',

                        /**
                         * join name with message
                         */
                        toString: function () {
                            return this.name + ': ' + this.message;
                        }
                    };
            }
        },

        /**
         * Initializes variables
         * @private
         */
        _initialize: function () {
            this._params = this.element.data('params') || {};
            this._code = this.element.data('code');
            this._width = this.element.data('width');
            this._height = this.element.data('height');
            this._autoplay = !!this.element.data('autoplay');
            this._playing = this._autoplay || false;
            this._loop = this.element.data('loop');
            this._rel = this.element.data('related');
            this.useYoutubeNocookie = this.element.data('youtubenocookie') || false;

            this._responsive = this.element.data('responsive') !== false;

            if (this._responsive === true) {
                this.element.addClass('responsive');
            }

            this._calculateRatio();
        },

        /**
         * Abstract play command
         */
        play: function () {
            this._player.play();
        },

        /**
         * Abstract pause command
         */
        pause: function () {
            this._player.pause();
        },

        /**
         * Abstract stop command
         */
        stop: function () {
            this._player.stop();
        },

        /**
         * Abstract playing command
         */
        playing: function () {
            return this._player.playing();
        },

        /**
         * Destroyer
         */
        destroy: function () {
            this._player.destroy();
        },

        /**
         * Calculates ratio for responsive videos
         * @private
         */
        _calculateRatio: function () {
            if (!this._responsive) {
                return;
            }
            this.element.css('paddingBottom', this._height / this._width * 100 + '%');
        }
    });

    $.widget('mage.videoYoutube', $.mage.productVideoLoader, {

        /**
         * Initialization of the Youtube widget
         * @private
         */
        _create: function () {
            var self = this;

            this._initialize();

            this.element.append('<div/>');

            this._on(window, {

                /**
                 * Handle event
                 */
                'youtubeapiready': function () {
                    var host = 'https://www.youtube.com';

                    if (self.useYoutubeNocookie) {
                        host = 'https://www.youtube-nocookie.com';
                    }

                    if (self._player !== undefined) {
                        return;
                    }
                    self._autoplay = true;

                    if (self._autoplay) {
                        self._params.autoplay = 1;
                    }

                    if (!self._rel) {
                        self._params.rel = 0;
                    }

                    self._player = new window.YT.Player(self.element.children(':first')[0], {
                        height: self._height,
                        width: self._width,
                        videoId: self._code,
                        playerVars: self._params,
                        host: host,
                        events: {

                            /**
                             * Get duration
                             */
                            'onReady': function onPlayerReady() {
                                self._player.getDuration();
                                self.element.closest('.fotorama__stage__frame')
                                    .addClass('fotorama__product-video--loaded');
                            },

                            /**
                             * Event observer
                             */
                            onStateChange: function (data) {
                                switch (window.parseInt(data.data, 10)) {
                                    case 1:
                                        self._playing = true;
                                        break;
                                    default:
                                        self._playing = false;
                                        break;
                                }

                                self._trigger('statechange', {}, data);

                                if (data.data === window.YT.PlayerState.ENDED && self._loop) {
                                    self._player.playVideo();
                                }
                            }
                        }

                    });
                }
            });

            this._loadApi();
        },

        /**
         * Loads Youtube API and triggers event, when loaded
         * @private
         */
        _loadApi: function () {
            var element,
                scriptTag;

            if (videoRegister.isRegistered('youtube')) {
                if (videoRegister.isLoaded('youtube')) {
                    $(window).trigger('youtubeapiready');
                }

                return;
            }
            videoRegister.register('youtube');

            element = document.createElement('script');
            scriptTag = document.getElementsByTagName('script')[0];

            element.async = true;
            element.src = 'https://www.youtube.com/iframe_api';
            scriptTag.parentNode.insertBefore(element, scriptTag);

            /**
             * Event observe and handle
             */
            window.onYouTubeIframeAPIReady = function () {
                $(window).trigger('youtubeapiready');
                videoRegister.register('youtube', true);
            };
        },

        /**
         * Play command for Youtube
         */
        play: function () {
            this._player.playVideo();
            this._playing = true;
        },

        /**
         * Pause command for Youtube
         */
        pause: function () {
            this._player.pauseVideo();
            this._playing = false;
        },

        /**
         * Stop command for Youtube
         */
        stop: function () {
            this._player.stopVideo();
            this._playing = false;
        },

        /**
         * Playing command for Youtube
         */
        playing: function () {
            return this._playing;
        },

        /**
         * stops and unloads player
         * @private
         */
        destroy: function () {
            this.stop();
            this._player.destroy();
        }
    });

    $.widget('mage.videoVimeo', $.mage.productVideoLoader, {

        /**
         * Initialize the Vimeo widget
         * @private
         */
        _create: function () {
            var timestamp,
                additionalParams = '',
                src;

            this._initialize();
            timestamp = new Date().getTime();
            this._autoplay = true;

            if (this._autoplay) {
                additionalParams += '&autoplay=1';
            }

            if (this._loop) {
                additionalParams += '&loop=1';
            }
            src = 'https://player.vimeo.com/video/' +
                this._code + '?api=1&player_id=vimeo' +
                this._code +
                timestamp +
                additionalParams;
            this.element.append(
                $('<iframe/>')
                    .attr('frameborder', 0)
                    .attr('id', 'vimeo' + this._code + timestamp)
                    .attr('width', this._width)
                    .attr('height', this._height)
                    .attr('src', src)
                    .attr('webkitallowfullscreen', '')
                    .attr('mozallowfullscreen', '')
                    .attr('allowfullscreen', '')
                    .attr('referrerPolicy', 'origin')
            );
            this._player = window.$f(this.element.children(':first')[0]);

            // Froogaloop throws error without a registered ready event
            this._player.addEvent('ready', function (id) {
                $('#' + id).closest('.fotorama__stage__frame').addClass('fotorama__product-video--loaded');
            });
        },

        /**
         * Play command for Vimeo
         */
        play: function () {
            this._player.api('play');
            this._playing = true;
        },

        /**
         * Pause command for Vimeo
         */
        pause: function () {
            this._player.api('pause');
            this._playing = false;
        },

        /**
         * Stop command for Vimeo
         */
        stop: function () {
            this._player.api('unload');
            this._playing = false;
        },

        /**
         * Playing command for Vimeo
         */
        playing: function () {
            return this._playing;
        }
    });
});
