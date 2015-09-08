require([ "jquery", "jquery/ui", "mage/gallery"], function( $ ) {
  //this section is for helper functions
  function YoutubeDurationToSeconds(duration) {
    var match = duration.match(/PT(\d+H)?(\d+M)?(\d+S)?/)

    var hours = (parseInt(match[1]) || 0);
    var minutes = (parseInt(match[2]) || 0);
    var seconds = (parseInt(match[3]) || 0);

    return hours * 3600 + minutes * 60 + seconds;
  }

  function ConvertSecondsToTimestamp(seconds) {
    var t = new Date(1970,0,1);
    t.setSeconds(seconds);
    var s = t.toTimeString().substr(0,8);
    if(seconds > 86399)
      s = Math.floor((t - Date.parse("1/1/70")) / 3600000) + s.substr(2);
    return s;
  }

  function parseHref (href) {
    var a = document.createElement('a');
    a.href = href;
    return a;
  }

  function parseURL (href, forceVideo) {
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

  //start to create VideoData from backend array
  function VideoData () {
    var videoData = [
      {
        type : 'video',
        role : 'small_image',
        id : '-f8qItRY_OA',
        provider : 'youtube',
        APIkey : '',
        timing : '00:00:05'
      },
      {
        type : 'video',
        role : 'small_image',
        id : 'bvIoMW0Y3pc',
        provider : 'youtube',
        key : '',
        timing : '00:00:05'
      },
      {
        type : 'video',
        role : 'small_image',
        id : 'NqqbbWeDszg',
        provider : 'youtube',
        APIkey : '',
        timing : '00:00:05'
      },
      {
        type : 'video',
        role : 'small_image',
        id : '8957328',
        provider : 'vimeo',
        APIkey : '',
        timing : '00:00:05'
      }
    ];
    return videoData;
  }

  function returnVideoDataByNumber (number) {
    var data = VideoData();
    return data[number];
  }

  //create AddFotoramaVideoEvents widget
  $.widget('mage.AddFotoramaVideoEvents', {

    PV : 'product-video',
    VID : 'video',
    VI : 'vimeo',
    VT : 'video-timing',
    attachTiming : 1, //turn this on (by setting 1) if you need to display block with video timing BEFORE the player is loaded in fotorama frame

    _init : function () {
      this._checkForVimeo(); //check for Vimeo, because we need to load external framework in order for Vimeo player to work
      this._initFotoramaVideo();
      this._attachFotoramaEvents();
    },

    _checkForVimeo : function () {
      var AllVideoData = VideoData();
      for (var VideoItem in AllVideoData) {
        if (AllVideoData[VideoItem].provider === this.VI) this._loadVimeoJSFramework();
      }
    },

    _loadVimeoJSFramework : function () {
        var element = document.createElement('script'),
          scriptTag = document.getElementsByTagName('script')[0];

        element.async = false;
        element.src = "https://f.vimeocdn.com/js/froogaloop2.min.js";
        scriptTag.parentNode.insertBefore(element, scriptTag);
    },

    _initFotoramaVideo : function (e) {
      var fotorama = $(this.element).data('fotorama'),
          $thumbsParent = fotorama.activeFrame.$navThumbFrame.parent(),
          $thumbs = $thumbsParent.find('.fotorama__nav__frame');

      this._startPrepareForPlayer(e, fotorama);

      for (var t = 0; t < $thumbs.length; t++) {
        if (returnVideoDataByNumber(t).type === this.VID) {
          $thumbsParent.find('.fotorama__nav__frame:eq('+t+')').addClass('video-thumb-icon');
        }
      }
    },

    _attachFotoramaEvents : function () {
      //when fotorama ends loading new preview - prepare data for player in container and load it if it exists
      $(this.element).on('fotorama:showend', $.proxy(function(e, fotorama) {
        this._startPrepareForPlayer(e, fotorama);
      }, this));
    },

    _startPrepareForPlayer : function (e, fotorama) {
      this._unloadVideoPlayer(fotorama.activeFrame.$stageFrame.parent());
      //we need to fire 3 events at once, because we need to check previous frame, current frame, and next frame
      this._checkForVideo(e, fotorama, -1);
      this._checkForVideo(e, fotorama, 0);
      this._checkForVideo(e, fotorama, 1);
    },

    _checkForVideo : function (e, fotorama, number) { //number is stands for the element number relatively to current active frame, +1 is to the next frame from the current active one, -1 is to previous
      var FrameNumber = parseInt(fotorama.activeFrame.i),
          videoData = returnVideoDataByNumber(FrameNumber - 1 + number),
          $image = fotorama.data[FrameNumber - 1 + number];

      if ($image) $image = $image.$stageFrame;

      if ($image && videoData && videoData.type === this.VID) {
        this._prepareForVideoContainer($image, videoData);
      }
    },

    _prepareForVideoContainer : function ($image, videoData) {
        if (!$image.hasClass('video-container')) $image.addClass('video-container').addClass('video-unplayed');
        this._createVideoContainer(videoData, $image);
        this._setVideoEvent($image, this.PV);
    },

    _createVideoContainer : function (videoData, $image) {
      if ($image.find('.'+this.PV).length === 0) {
        $image.append('<div style="position:absolute;top:0;" class="'+this.PV+'" data-type="'+videoData.provider+'" data-code="'+videoData.id+'" data-width="100%" data-height="100%"></div>');
        if (this.attachTiming) {
          $image.append('<div class="'+this.VT+'">'+videoData.timing+'</div>');
          $image.find('.'+this.VT).addClass('fadeIn');
        }
      }
    },

    _setVideoEvent : function ($image, PV) {
      $image.on('click', function() {
        if ($(this).hasClass('video-unplayed')) {
          $(this).find('.video-timing').remove();
          $(this).removeClass('video-unplayed');
          $(this).find('.'+PV).productVideoLoader();
        }
      });
    },

    _unloadVideoPlayer: function ($wrapper) {
      $wrapper.find('.' + this.PV).each(function () {
        var $item = $(this).parent();
        if ($(this).find('iframe').length > 0) {
          $(this).find('iframe').remove();
          var cloneVideoDiv = $(this).clone();
          $(this).remove();
          $item.append(cloneVideoDiv);
          $item.addClass('video-unplayed');
        }
      });
    }
  });
});