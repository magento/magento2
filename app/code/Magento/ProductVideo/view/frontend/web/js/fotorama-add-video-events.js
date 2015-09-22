require(["jquery", "jquery/ui", "catalogGallery"], function ($) {
    //this section is for helper functions
    function parseHref(href) {
        var a = document.createElement('a');
        a.href = href;
        return a;
    }

    function parseURL(href, forceVideo) {
        if (typeof href !== 'string') return href;
        href = parseHref(href);

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

    //create AddFotoramaVideoEvents widget
    $.widget('mage.AddFotoramaVideoEvents', {
        options : {
            VideoData : '',
            VideoSettings : ''
        },

        PV: 'product-video', // [CONST]
        VID: 'video', // [CONST]
        VI: 'vimeo', // [CONST]
        Base: 0, //on check for video is base this setting become true if there is any video with base role
        MobileMaxWidth: 767, //max mobile width, currently for playing video if it's base one, we don't need it autoplay if it's on mobile version
        GP: 'gallery-placeholder', //gallery placeholder class is needed to find and erase <script> tag

        _init: function () {
            if (this._checkForVideoExist()) { //check if there is any video at all on this page
                this._checkForVimeo(); //check for Vimeo, because we need to load external framework in order for Vimeo player to work
                this._isVideoBase();
                this._initFotoramaVideo();
                this._attachFotoramaEvents();
            }
        },

        _createVideoData: function (inputData, isJSON) { //create appropriate video data object from backend JSON
            var videoData = {};
            if (isJSON) inputData = $.parseJSON(inputData);

            for (var key in inputData) {
                var DataUrl = '';
                videoData[key] = {
                    mediaType: '',
                    isBase: '',
                    id: '',
                    provider: ''
                };
                if (inputData[key].mediaType === 'external-video') {
                    videoData[key].mediaType = this.VID;
                } else {
                    videoData[key].mediaType = inputData[key].mediaType;
                }
                videoData[key].isBase = inputData[key].isBase;
                if (inputData[key].videoUrl != null) {
                    DataUrl = inputData[key].videoUrl;
                    DataUrl = parseURL(DataUrl);
                    videoData[key].id = DataUrl.id;
                    videoData[key].provider = DataUrl.type;
                }
            }
            return videoData;
        },

        _checkForVideoExist: function () { //if there is no video data, we don't want to load anything
            if (!this.options.VideoData) return false;
            if (!this.options.VideoSettings) return false;
            var result = this._createVideoData(this.options.VideoData, false),
                checker = false;
            this.options.VideoSettings[0].playIfBase = parseInt(this.options.VideoSettings[0].playIfBase);
            this.options.VideoSettings[0].showRelated = parseInt(this.options.VideoSettings[0].showRelated);
            this.options.VideoSettings[0].videoAutoRestart = parseInt(this.options.VideoSettings[0].videoAutoRestart);
            for (var key in result) {
                if (result[key].mediaType === this.VID) {
                    checker = true;
                }
            }
            if (checker) this.options.VideoData = result;
            return checker;
        },

        _checkForVimeo: function () { //check for any vimeo provider in data, cause we need to load external framework in order for Vimeo player to work
            var AllVideoData = this.options.VideoData;
            for (var VideoItem in AllVideoData) {
                if (AllVideoData[VideoItem].provider === this.VI) this._loadVimeoJSFramework();
            }
        },

        _isVideoBase: function () { // we check if there is any video with BASE role, if there is any - play it as soon as the page loads, if it desktop
            var AllVideoData = this.options.VideoData;
            for (var VideoItem in AllVideoData) {
                if (AllVideoData[VideoItem].mediaType === this.VID && AllVideoData[VideoItem].isBase && this.options.VideoSettings[0].playIfBase) this.Base = true;
            }
        },

        _loadVimeoJSFramework: function () { // load external framework in order for Vimeo player to work
            var element = document.createElement('script'),
                scriptTag = document.getElementsByTagName('script')[0];

            element.async = true;
            element.src = "https://f.vimeocdn.com/js/froogaloop2.min.js";
            scriptTag.parentNode.insertBefore(element, scriptTag);
        },

        _initFotoramaVideo: function (e) {
            var fotorama = $(this.element).data('fotorama');
            if (fotorama.activeFrame.$navThumbFrame) {
                var $thumbsParent = fotorama.activeFrame.$navThumbFrame.parent(),
                    $thumbs = $thumbsParent.find('.fotorama__nav__frame');

                for (var t = 0; t < $thumbs.length; t++) {
                    if (this.options.VideoData[t].mediaType === this.VID) {
                        $thumbsParent.find('.fotorama__nav__frame:eq(' + t + ')').addClass('video-thumb-icon');
                    }
                }
            }
            $(this.element).on('fotorama:showend', $.proxy(function (e, fotorama) {
                $(fotorama.activeFrame.$stageFrame).removeAttr('href');
            }, this));
            this._startPrepareForPlayer(e, fotorama);
        },

        _attachFotoramaEvents: function () {
            //when fotorama ends loading new preview - prepare data for player in container and load it if it exists, also do that for previous and for the next frame
            $(this.element).on('fotorama:showend', $.proxy(function (e, fotorama) {
                this._startPrepareForPlayer(e, fotorama);
            }, this));
        },

        _startPrepareForPlayer: function (e, fotorama) {
            this._unloadVideoPlayer(fotorama.activeFrame.$stageFrame.parent(), fotorama);
            //we need to fire 3 events at once, because we need to check previous frame, current frame, and next frame
            this._checkForVideo(e, fotorama, -1);
            this._checkForVideo(e, fotorama, 0);
            this._checkForVideo(e, fotorama, 1);
        },

        _checkForVideo: function (e, fotorama, number) { //number is stands for the element number relatively to current active frame, +1 is to the next frame from the current active one, -1 is to previous
            var FrameNumber = parseInt(fotorama.activeFrame.i),
                videoData = this.options.VideoData[FrameNumber - 1 + number],
                $image = fotorama.data[FrameNumber - 1 + number];
            if ($image) $image = $image.$stageFrame;

            if ($image && videoData && videoData.mediaType === this.VID) {
                $(fotorama.activeFrame.$stageFrame).removeAttr('href');
                this._prepareForVideoContainer($image, videoData, fotorama, number);
            }
        },

        _prepareForVideoContainer: function ($image, videoData, fotorama, number) {
            if (!$image.hasClass('fotorama-video-container')) $image.addClass('fotorama-video-container').addClass('video-unplayed');
            this._createVideoContainer(videoData, $image);
            this._setVideoEvent($image, this.PV, fotorama, number);
        },

        _createVideoContainer: function (videoData, $image) {
            if ($image.find('.' + this.PV).length === 0) { //dont touch anything if there is already <div> with data in current frame
                $image.append('<div class="' + this.PV + '" data-related="' + this.options.VideoSettings[0].showRelated + '" data-loop="' + this.options.VideoSettings[0].videoAutoRestart + '" data-type="' + videoData.provider + '" data-code="' + videoData.id + '" data-width="100%" data-height="100%"></div>');
            }
        },

        _setVideoEvent: function ($image, PV, fotorama, number) {
            $image.find('.magnify-lens').remove();
            $image.on('click', function () {
                if ($(this).hasClass('video-unplayed') && $(this).find('iframe').length === 0) {
                    $(this).removeClass('video-unplayed');
                    $(this).find('.' + PV).productVideoLoader();
                }
            });
            this._handleBaseVideo(fotorama, number); //check for video is it base and handle it if it's base
        },

        _handleBaseVideo: function (fotorama, number) {
            if (this.Base && this.options.VideoData[fotorama.activeIndex].isBase && number === 0 && $(window).width() > this.MobileMaxWidth) {
                //if we have found Base video, and current active frame is the right one, and called number (index) is 0 and its not a mobile - play it for one time
                if (this.options.VideoData[fotorama.activeIndex].provider === this.VI) { //if we have vimeo video as base one, we need to wait for froogaloop for load
                    var waitForFroogaloop = setInterval($.proxy(function () {
                        if (window.Froogaloop) {
                            clearInterval(waitForFroogaloop);
                            $(this.element).data('fotorama').activeFrame.$stageFrame[0].click();
                            this.Base = false;
                        }
                    }, this), 50);
                } else { //if not a vimeo - play it immediately
                    $(this.element).data('fotorama').activeFrame.$stageFrame[0].click();
                    this.Base = false;
                }
            }
        },

        _unloadVideoPlayer: function ($wrapper, $current) {
            $wrapper.find('.' + this.PV).each(function () {
                var $item = $(this).parent();
                if ($(this).find('iframe').length > 0 && $current.activeFrame.$stageFrame.index() != $item.index()) {
                    $(this).find('iframe').remove();
                    var cloneVideoDiv = $(this).clone();
                    $(this).remove();
                    $item.append(cloneVideoDiv);
                    $item.addClass('video-unplayed');
                }
            });
        }
    });

    $('.gallery-placeholder').on('fotorama:ready', function () {
        $(this).find('.fotorama').AddFotoramaVideoEvents({
            VideoData : $(this).data('fotorama-video-data'),
            VideoSettings : $(this).data('fotorama-video-settings')
        });
        //no reason to store video data and settings after - erase it
        $(this).removeAttr('data-fotorama-video-data');
        $(this).removeAttr('data-fotorama-video-settings');
    });
});
