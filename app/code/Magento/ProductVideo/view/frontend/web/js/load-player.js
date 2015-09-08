/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 @version 0.0.1
 @requires jQuery & jQuery UI
*/
require([ "jquery", "jquery/ui", "mage/gallery" ], function( $ ) {
  "use strict";
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
          self._autoplay = true;
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
      this._autoplay = true;
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
      this._player = $f(this.element.children(":first")[0]);

      // Froogaloop throws error without a registered ready event
      this._player.addEvent('ready', function () {
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

  $.widget('mage.videoLoaderDOM', {
    options : {
      $fotoramaPreview : '.fotorama__stage__shaft.fotorama__grab',
      $fotoramaMain : '.fotorama__stage__frame',
      $fotoramaThumbnails : '.fotorama__nav.fotorama__nav--thumbs',
      $fotoramaThumb : '.fotorama__nav__frame.fotorama__nav__frame--thumb'
    },
    _create : function () {
      console.log('creating...');


      var video_width = $(this.options.$fotoramaMain).width();
      var video_height = $(this.options.$fotoramaMain).height();

      //this.element.find(this.options.$fotoramaMain).append('<div style="position:absolute;" class="product-video" data-type="youtube" data-code="ubKinQvpc6w" data-width="'+video_width+'" data-height="'+video_height+'"></div>');
      this._eventsDOM();
    },
    _eventsDOM : function () {
      $('.fotorama__nav__shaft .fotorama__nav__frame').on('click', function (){
        if ($(this).hasClass('fotorama__active')) {
          console.log($(this).index());
          setTimeout(function(){
            $('.fotorama__stage__shaft .fotorama__active').css('border-left','10px solid black');
          }, 400);
        }
      });
    }
  });

  /*var waitForFotorama = setInterval(function () {
    if ($('.fotorama__stage__frame').length > 0) {

      clearInterval(waitForFotorama);
      $('body').videoLoaderDOM();
      $('.product-video').productVideoLoader();

    }
  }, 50);*/


});