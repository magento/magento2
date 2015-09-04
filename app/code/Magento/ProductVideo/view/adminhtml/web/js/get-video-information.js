/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global FORM_KEY*/
require(["jquery", "jquery/ui"], function ($) {
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
      eventTarget: '#new_video_get',
      eventType: 'click',
      field: {
        url: '#video_url',
        title: '#video_title',
        description: '#video_description',
        preview: '.field-new_video_screenshot_preview .admin__field-control' //preview must be appended as img to this container
      },
      addButtonCall: '#add_video_button', //we need to erase data when new Edit Button called
      editButtonCall: '#media_gallery_content .image, #image-container .image',
      youtubeKey: 'AIzaSyDwqDWuw1lra-LnpJL2Mr02DYuFmkuRSns', //sample data, change later!!!!!!!!
      vimeoKey: '' //nah, we don't really need it
    },
    _init: function () {
      var self = this;
      //add video icon to items
      $('.video-create-button').on('click', function(){
        $('#media_gallery_content, #image-container').find('.video-item').parent().addClass('video-item');
      });
      $('#media_gallery_content, #image-container').find('.video-item').parent().addClass('video-item');
      //end add

      $(this.options.eventTarget).on(this.options.eventType, function () {
        var getURL = $(self.options.field.url).val();
        self._loadVimeoAPI();
        if (getURL) {
          var getVideoInfo = self._validateURL(getURL); //{}
          if (getVideoInfo) {
            var $videoContainer = $('#video-player-preview-location .video-player-container');

            $videoContainer.find('.product-video').remove();
            $videoContainer.append('<div class="product-video" data-type="' + getVideoInfo.type + '" data-code="' + getVideoInfo.id + '" data-width="100%" data-height="100%"></div>');
            $videoContainer.find('.product-video').productVideoLoader();
            self._getVideoData(getVideoInfo.type, getVideoInfo.id);
          } else throw('Error: URL is not valid');
        } else throw('Error: $(urlField) has empty value');
      });
      $(this.options.addButtonCall).on('click', function () {
        self._eraseFields();
      });
      $(this.options.editButtonCall).on('click', function () {
        if ($(this).find('.video-item')) {
          var getVideoInfo = self._validateURL($(self.options.field.url).val());
          var $videoContainer = $('#video-player-preview-location .video-player-container');

          $videoContainer.find('.product-video').remove();
          $videoContainer.append('<div class="product-video" data-type="' + getVideoInfo.type + '" data-code="' + getVideoInfo.id + '" data-width="100%" data-height="100%"></div>');
          $videoContainer.find('.product-video').productVideoLoader();
          self._getVideoData(getVideoInfo.type, getVideoInfo.id);

          if (getVideoInfo.type == 'youtube') {
            $.get('https://www.googleapis.com/youtube/v3/videos?id=' + getVideoInfo.id + '&part=snippet,contentDetails,statistics,status&key=' + self.options.youtubeKey, function (data) {
              var duration = data.items[0].contentDetails.duration;
              var user = data.items[0].snippet.channelTitle;
              var channelId = data.items[0].snippet.channelId;
              var uploaded = data.items[0].snippet.publishedAt;
              var title = data.items[0].snippet.localized.title;
              var date = uploaded.split('T');
              $('.video-information').fadeIn();
              $('.video-information.title span').text(title);
              $('.video-information.uploaded span').text(date[0] + ' ' + date[1].slice(0, -5));
              $('.video-information.uploader span').html('<a href="http://youtube.com/channel/' + channelId + '">' + user + '</a>');
              $('.video-information.duration span').text(self._formatYoutubeDuration(duration));
              $(self.options.field.title).val(title);
            });
          }
          if (getVideoInfo.type == 'vimeo') {
            $.get("http://vimeo.com/api/v2/video/" + getVideoInfo.id + ".json", function (data) {
              var duration = data[0].duration;
              var user = data[0].user_name;
              var user_url = data[0].user_url;
              var uploaded = data[0].upload_date;
              var title = data[0].title;
              $('.video-information').fadeIn();
              $('.video-information.title span').text(title);
              $('.video-information.uploaded span').text(uploaded);
              $('.video-information.uploader span').html('<a href="' + user_url + '">' + user + '</a>');
              $('.video-information.duration span').text(self._formatVimeoDuration(duration));
            });
          }
        }
      })
    },
    _eraseFields: function () { //deletes video player, video thumbnail, and metadata
      $('.video-information').hide();
      $('.video-information.title span').text('');
      $('.video-information.uploaded span').text('');
      $('.video-information.uploader span').text('');
      $('.video-information.duration span').text('');
      $(this.options.field.preview).find('img').remove();
      $('#video-player-preview-location .video-player-container .product-video').remove();
    },
    _getVideoData: function (type, id) {
      var self = this;
      if (type == 'youtube') {
        $.get('https://www.googleapis.com/youtube/v3/videos?id=' + id + '&part=snippet,contentDetails,statistics,status&key=' + this.options.youtubeKey, function (data) {
          var duration = data.items[0].contentDetails.duration;
          var user = data.items[0].snippet.channelTitle;
          var channelId = data.items[0].snippet.channelId;
          var uploaded = data.items[0].snippet.publishedAt;
          var title = data.items[0].snippet.localized.title;
          var description = data.items[0].snippet.description;
          var date = uploaded.split('T');
          var thumbnail = data.items[0].snippet.thumbnails.maxres.url;
          $('.video-information').fadeIn();
          $('.video-information.title span').text(title);
          $('.video-information.uploaded span').text(date[0] + ' ' + date[1].slice(0, -5));
          $('.video-information.uploader span').html('<a href="http://youtube.com/channel/' + channelId + '">' + user + '</a>');
          $('.video-information.duration span').text(self._formatYoutubeDuration(duration));
          $(self.options.field.title).val(title);
          $(self.options.field.description).val(description);
          $(self.options.field.preview).find('img').remove();
          $(self.options.field.preview).append('<img src="' + thumbnail + '" />');
        });
      }
      if (type == 'vimeo') {
        $.get("http://vimeo.com/api/v2/video/" + id + ".json", function (data) {
          var duration = data[0].duration;
          var user = data[0].user_name;
          var user_url = data[0].user_url;
          var uploaded = data[0].upload_date;
          var title = data[0].title;
          var description = data[0].description;
          var thumbnail = data[0].thumbnail_large;
          $('.video-information').fadeIn();
          $('.video-information.title span').text(title);
          $('.video-information.uploaded span').text(uploaded);
          $('.video-information.uploader span').html('<a href="' + user_url + '">' + user + '</a>');
          $('.video-information.duration span').text(self._formatVimeoDuration(duration));
          $(self.options.field.title).val(title);
          $(self.options.field.description).val(description);
          $(self.options.field.preview).find('img').remove();
          $(self.options.field.preview).append('<img src="' + thumbnail + '" />');
        });
      }
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
  var waitForModal = setInterval(function () {
    if ($('.modal-slide.mage-new-video-dialog').length > 0) {
      clearInterval(waitForModal);
      $('.modal-slide.mage-new-video-dialog').getVideoInformation();
    }
  }, 50);
});