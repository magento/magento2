/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global FORM_KEY*/
require([
      "jquery",
      "jquery/ui"
    ],
    function ($) {
      'use strict';
      var videoRegister = {
        _register: {},

        /**
         * Checks, if api is already registered
         *
         * @param api
         * @returns {boolean}
         */
        isRegistered: function (api) {
          return this._register[api] !== undefined;
        },

        /**
         * Checks, if api is loaded
         *
         * @param api
         * @returns {boolean}
         */
        isLoaded: function (api) {
          return this._register[api] !== undefined && this._register[api] === true;
        },

        /**
         * Register new video api
         * @param api
         * @param loaded
         */
        register: function (api, loaded) {
          loaded = loaded || false;
          this._register[api] = loaded;
        }
      };


      $.widget('mage.productVideoLoader', {
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
                toString: function () {
                  return this.name + ": " + this.message;
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

          this._responsive = true;

          if (this.element.data('responsive') === false) {
            this._responsive = false;
          }

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
          this.element.css('paddingBottom', (this._height / this._width * 100) + '%');
        }
      });

      $.widget('mage.videoYoutube', $.mage.productVideoLoader, {
        /**
         * Initialization of the Youtube widget
         * @private
         */
        _create: function () {
          var self = this;
          var duration;
          var done = false;
          this._initialize();

          this.element.append('<div/>');

          this._on(window, {
            'youtubeapiready': function () {
              if (self._player !== undefined) {
                return;
              }
              if (self._autoplay) {
                self._params.autoplay = 1;
              }
              self._params.rel = 0;

              self._player = new YT.Player(self.element.children(':first')[0], {
                height: self._height,
                width: self._width,
                videoId: self._code,
                playerVars: self._params,
                events: {
                  'onReady': function onPlayerReady(event) {
                    duration = self._player.getDuration();
                  },
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
          if (videoRegister.isRegistered('youtube')) {
            if (videoRegister.isLoaded('youtube')) {
              $(window).trigger('youtubeapiready');
            }
            return;
          }
          videoRegister.register('youtube');

          var element = document.createElement('script'),
              scriptTag = document.getElementsByTagName('script')[0];

          element.async = true;
          element.src = "http://www.youtube.com/iframe_api";
          scriptTag.parentNode.insertBefore(element, scriptTag);

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
          this._initialize();
          var timestamp = new Date().getTime(),
              additionalParams = '';

          if (this._autoplay) {
            additionalParams += '&autoplay=1';
          }

          this.element.append(
              $('<iframe/>')
                  .attr('frameborder', 0)
                  .attr('id', 'vimeo' + this._code + timestamp)
                  .attr('width', this._width)
                  .attr('height', this._height)
                  .attr('src', 'http://player.vimeo.com/video/' + this._code + '?api=1&player_id=vimeo' + this._code + timestamp + additionalParams)
          );

        }
      });

      $.widget('mage.getVideoInformation', {
        options: {
          youtubeKey: 'AIzaSyDwqDWuw1lra-LnpJL2Mr02DYuFmkuRSns' //sample data, change later!!!!!!!!
        },

        _UPDATE_VIDEO_INFORMATION_TRIGGER: 'update_video_information',

        _ERROR_UPDATE_INFORMATION_TRIGGER: 'error_update_information',

        _videoInformation: null,

        getVideoInformation: function() {
          return this._videoInformation;
        },

        onFocusOut : function (func) {

        },

        _init : function () {
          jQuery(this.element).on("focusout", $.proxy(this._onBlurhandler, this));
        },


        _onBlurhandler: function() {
          var url = this.element.val();
          if(!url) {
            this._videoInformation = null;
            this.element.trigger(this._UPDATE_VIDEO_INFORMATION_TRIGGER, null);
            return;
          }

          var videoInfo = this._validateURL(url);
          if(!videoInfo) {
            this._videoInformation = null;
            this.element.trigger(this._ERROR_UPDATE_INFORMATION_TRIGGER, "Invalid video url");
            return;
          }

          function _onYouTubeLoaded(data) {
            var tmp       = data.items[0];
            var respData  = {
              duration: tmp.contentDetails.duration,
              channel: tmp.snippet.channelTitle,
              channelId:  tmp.snippet.channelId,
              uploaded: tmp.snippet.publishedAt,
              title: tmp.snippet.localized.title,
              description: tmp.snippet.description,
              thumbnail: tmp.snippet.thumbnails.high.url
            };
            this._videoInformation  = respData;
            this.element.trigger(this._UPDATE_VIDEO_INFORMATION_TRIGGER, respData);
          }

          function _onVimeoLoaded(data) {
            var tmp = data[0];
            var respData = {
              duration: tmp.duration,
              user: tmp.user_name,
              user_url: tmp.user_url,
              uploaded: tmp.upload_date,
              title: tmp.title,
              description: tmp.description,
              thumbnail: tmp.thumbnail_large
            };
            this._videoInformation  = respData;
            this.element.trigger(this._UPDATE_VIDEO_INFORMATION_TRIGGER, respData);
          }

          var type = videoInfo.type;
          if (type == 'youtube') {
            $.get('https://www.googleapis.com/youtube/v3/videos?id=' + videoInfo.id + '&part=snippet,contentDetails,statistics,status&key=' + this.options.youtubeKey, $.proxy(_onYouTubeLoaded, this))
          } else if (type == 'vimeo') {
            $.get("http://vimeo.com/api/v2/video/" + videoInfo.id + ".json", $.proxy(_onVimeoLoaded, this));
          }
          this.onFocusOut();
        },

        _formatYoutubeDuration: function (duration) {
          var hours = duration.match("PT(.*)H");
          var video_timing;
          if (hours === null) {
            hours = '00';
          } else {
            hours = duration.match("PT(.*)H")[1];
          }

          var minutes = duration.match("H(.*)M");
          if (minutes === null) {
            minutes = duration.match("PT(.*)M");
            if (minutes === null) {
              minutes = '00';
            } else {
              minutes = duration.match("PT(.*)M")[1];
            }
          } else {
            minutes = duration.match("H(.*)M")[1];
          }

          var seconds = duration.match("M(.*)S");
          if (seconds === null) {
            seconds = duration.match("PT(.*)S")[1];
          } else {
            seconds = duration.match("M(.*)S")[1];
          }

          if (hours === '00') {
            if (parseInt(seconds) < 10) {
              video_timing = minutes + ':0' + seconds;
            } else {
              video_timing = minutes + ':' + seconds;
            }
          } else {
            if (parseInt(minutes) < 10) {
              minutes = '0' + minutes;
            }
            if (parseInt(seconds) < 10) {
              video_timing = hours + ':' + minutes + ':0' + seconds;
            } else {
              video_timing = hours + ':' + minutes + ':' + seconds;
            }
          }

          if (hours === '00' && minutes === '00') {
            if (parseInt(seconds) < 10) {
              video_timing = '00:0' + seconds;
            } else {
              video_timing = '00:' + seconds;
            }
          }
          return video_timing;
        },

        _formatVimeoDuration: function (seconds) {
          var MainSeconds = parseInt(seconds);
          var MainMinutes = parseInt(MainSeconds / 60);
          var MainHours = parseInt(MainMinutes / 60);
          var ResultMinutes = MainMinutes - (MainHours * 60);
          var ResultSeconds = MainSeconds - ((MainHours * 3600) + (ResultMinutes * 60));

          if (ResultMinutes.toString().length == 1) {
            ResultMinutes = '0' + ResultMinutes;
          }
          if (ResultSeconds.toString().length == 1) {
            ResultSeconds = '0' + ResultSeconds;
          }
          if (MainMinutes > 0 && MainHours > 0) {
            return MainHours + ':' + ResultMinutes + ':' + ResultSeconds;
          }
          if (MainMinutes > 0 && MainHours <= 0) {
            return ResultMinutes + ':' + ResultSeconds;
          }
          if (MainMinutes <= 0 && MainHours > 0) {
            return MainHours + ':' + '00' + ':' + ResultSeconds;
          }
          if (MainMinutes <= 0 && MainHours <= 0) {
            return '00' + ':' + ResultSeconds;
          }
        },

        _loadVimeoAPI: function () {
          var element = document.createElement('script'),
              scriptTag = document.getElementsByTagName('script')[0];

          element.async = false;
          element.src = "https://f.vimeocdn.com/js/froogaloop2.min.js";
          scriptTag.parentNode.insertBefore(element, scriptTag);
        },

        _parseHref: function (href) {
          var a = document.createElement('a');
          a.href = href;
          return a;
        },

        _validateURL: function (href, forceVideo) {
          if (typeof href !== 'string') return href;
          href = this._parseHref(href);

          var id,
              type;

          if (href.host.match(/youtube\.com/) && href.search) {
            //.log();
            id = href.search.split('v=')[1];
            if (id) {
              var ampersandPosition = id.indexOf('&');
              if (ampersandPosition !== -1) {
                id = id.substring(0, ampersandPosition);
              }
              type = 'youtube';
            }
          } else if (href.host.match(/youtube\.com|youtu\.be/)) {
            id = href.pathname.replace(/^\/(embed\/|v\/)?/, '').replace(/\/.*/, '');
            type = 'youtube';
          } else if (href.host.match(/vimeo\.com/)) {
            type = 'vimeo';
            id = href.pathname.replace(/^\/(video\/)?/, '').replace(/\/.*/, '');
          }

          if ((!id || !type) && forceVideo) {
            id = href.href;
            type = 'custom';
          }

          return id ? {id: id, type: type, s: href.search.replace(/^\?/, '')} : false;
        }
      });
    });
