/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     js
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
    @version 0.1.1
    @requires jQuery

    @TODO: - Add more effects;
           - Add more documentation over file for other developers;
 */
;(function($, document, window, undefined){
"use strict";

    /**
        @static
        @private
        @see jQuery.magentoZoom#defaults for default values
        @description Represents variables of magentoZoom plugin with different configurations
        @default {@see data}
        @field [pageX, pageY, timer, state] used for plugin internal use
     */
var data = { pageX:0, pageY:0, timer:0, loading: false, currentItem:-1, switchDirection:null },

    /**
        @private
        @description Utilities for plugin use
     */
    utils = {

        /**
            @event
            @description Binds keyup event for document on gallery opening

            @TODO: custom event handler ?
         */
        bindDocumentEvents: function(){
            $(document).bind('keyup', function(event){
                var code = event.keyCode || event.which;

                event.preventDefault();
                event.stopPropagation();

                switch(code){
                    case 40: // Down arrow
                    case 37: // left arrow
                        if(!data.main.isSingleInCollection){
                            method.galleryPrev();
                        }
                        break;
                    case 32: // Space
                    case 38: // Up arrow
                    case 39: // Right arrow
                        if(!data.main.isSingleInCollection){
                            method.galleryNext();
                        }
                        break;
                    case 27: // Escape
                        method.hideGallery();
                        break;
                }
            });
        },

        /**
            @description Unbinds keyup event for document on gallery close
         */
        unBindDocumentEvents: function(){
            $(document).unbind('keyup');
        },

        /**
            @description Recalculate sizes and setData() for necessary objects
            @param {jQuery} image Zoomed image
            [@param {Object -> Function} callback After setup recalculation after: {@borrows img as this},
            @returns {jQuery} img
         */
        recalculateSize: function(sImg, lImg, callback){
            var settings = data.settings,
                h = lImg.outerHeight(),
                w = lImg.outerWidth(),
                ratio = ( ( w >= h ) ? ( w / sImg.outerWidth() ) : ( h / sImg.outerHeight() ) ),
                isLarger = (ratio > 1),
                trackW = utils.ceil(w / ratio),
                trackH = utils.ceil(h / ratio),
                ieMT = ((data.main.wrapper.height() - trackH) / 2) >> 0,
                ieML = ((data.main.wrapper.width() - trackW) / 2) >> 0,
                moveOnX = (w > settings.enlarged.width),
                moveOnY = (h > settings.enlarged.height),
                wrapperWidth = (w > settings.enlarged.width) ? settings.enlarged.width : w,
                wrapperHeight = (h > settings.enlarged.height) ? settings.enlarged.height : h,
                lW = moveOnX ? utils.ceil((trackW / w) * settings.enlarged.width) : trackW,
                lH = moveOnY ? utils.ceil((trackH / h) * settings.enlarged.height) : trackH,
                track = data.main.track,
                opacity = ((settings.lens.mode === 'standart') ? settings.lens.opacity : 1 );

                track.unbind();
                utils.switchNotice(settings.messages.noticeDefault);
                utils.switchNotice('show');

                data.enlarged.wrapper.css({
                    width: wrapperWidth,
                    height: wrapperHeight
                });

                track.css({
                    width: trackW,
                    height: trackH
                });

                if($.browser.msie && $.browser.version == 7){
                    track.css({
                        'margin-top': ieMT,
                        'margin-left': ieML
                    });
                }

                if(isLarger){

                    if(settings.useLens && isLarger){
                        data.lens.magnifier.css({
                            width: lW,
                            height: lH,
                            opacity: opacity
                        }).hide();
                    }

                    method.attachTrackEvents.call(track);


                } else if(settings.useGallery && (!data.main.image.hasClass('isPlaceholder'))) {
                    track.bind('click', method.showGallery);
                }

                if(settings.useGallery && !isLarger){
                    if(data.main.image.hasClass('isPlaceholder')){
                        utils.switchNotice('hide');
                    } else {
                        utils.switchNotice(settings.messages.noticeLightBox);
                    }
                } else {
                    if(!isLarger){
                        utils.switchNotice('hide');
                    }
                }

                if((!isLarger && !settings.useGallery) || data.main.image.hasClass('isPlaceholder')) {
                    track.addClass('non-active');
                } else {
                    track.removeClass('non-active');
                }

                utils.setData({
                    enlarged: {
                        image: lImg,
                        width: w,
                        height: h,
                        ratio: ratio,
                        isLarger: isLarger
                    },
                    lens: {
                        width: lW,
                        height: lH,
                        moveOnX: moveOnX,
                        moveOnY: moveOnY
                    },
                    main: {
                        trackWidth: trackW
                    }
                });

                if(callback && $.isFunction(callback.after)) { callback.after.call(lImg); }
        },

        /**
            @description recalculates sizes of lightbox each time when image switches

            @TODO: Refactor !
         */
        reCalculateGallerySize: function(){
            var mt, wH, wW, vCorrection,
                gallery = data.gallery,
                wrapper = gallery.gallery,
                thumbs = gallery.thumbsContainer,
                thumbsH = thumbs.is(':visible') ? thumbs.outerHeight(true) : 0,
                img = data.enlarged.image,
                vp = { w: $(window).width(), h: $(window).height() };

            /** Cleanup all CSSs */
                wrapper.css({
                    width:'',
                    height:'',
                    maxHeight: '',
                    marginTop:'',
                    marginLeft:''
                });
                img.css({
                    width:'',
                    height:''
                });
            /** --------------- */

            // if(wrapper.width() > vp.w){
            //     wW = vp.w - (wrapper.innerWidth() - wrapper.width());
            // }

            $(img).parent().css({
                bottom: thumbsH + 15
            });

/*            if(wrapper.height() > vp.h) {
                wH = vp.h - ( wrapper.outerHeight() - wrapper.height());
            } else {
                wH = wrapper.outerHeight();
            }

            wrapper.css({
                width: wW,
                maxHeight: wH,
                marginTop: mt
            });

            img.css({
                // maxWidth: wrapper.width(),
                maxHeight: wrapper.height() - thumbsH
            });

            vCorrection = img.height() + thumbsH;

            if(vCorrection < wrapper.height()) {
                wrapper.height(vCorrection);
            }


            wrapper.css({
                marginLeft: utils.ceil((vp.w - wrapper.width()) / 2)
            });*/

        },

        /**
            @param {jQuery} taget Target image in DOM
            @param {String} link Link to new image
            [@param {Object -> Function} callback Before and After setup switching
                before: {@borrows newImg as this},
                after: {@borrows newImg as this}]
            @description Replaces old image with new one using {@see {@param link}}

            @TODO Optimize for reusable base wrapper for callbacks (eg. utils.BaseCallbackCall)
                {@see <a href="http://css-tricks.com/custom-events-are-pretty-cool/">Custom events vs Callbacks</a>}
         */
        switchImage: function(target, link, callback){
            /** @TODO: Need new fix */
            var suffix = ($.browser.msie) ? ("?" + new Date().getTime()):'',
                img = $('<img>', { src: link + suffix }); /** will increase CDN hit ratio! {@TODO: try use '#' instead } */

            if(callback && $.isFunction(callback.before)) { callback.before.call(img); }

            img.load(function(){
                target.replaceWith(img);

                if(callback && $.isFunction(callback.after)) { callback.after.call(img); }
            });

        },

        /**
            @event
            @param {Function} callback Callback function
            @description Prevent default event behavior with callback
                ONLY incase if there is no any loading progress animation
         */
        click: function(callback){
            this.click(function(event){
                event.preventDefault();
                event.stopPropagation();

                if(callback && $.isFunction(callback) && !data.loading){
                    callback.call(this);
                }
            });
        },

        /**
            @param {String} changeTo Config specific string
                {incase if changeTo === "hide" notice will hide}
            [@param {Object -> Function} callback Before and After setup switching
                before: {@borrows target as this},
                after: {@borrows target as this}]

            @TODO Optimize for reusable base wrapper for callbacks (eg. utils.BaseCallbackCall)
                {@see <a href="http://css-tricks.com/custom-events-are-pretty-cool/">Custom events vs Callbacks</a>}
         */
        switchNotice: function(changeTo, callback){
            var target = data.main.notice;

            if(callback && $.isFunction(callback.before)) { callback.before.call(target); }

            if(changeTo === 'hide'){
                target.css('visibility', 'hidden'); /** dont use $.hide() cuz it will hide element and shift other */
            } else if(changeTo === 'show') {
                target.css('visibility', 'visible');
            } else {
                target
                    .text(changeTo)
                    .css('visibility', 'visible'); /** Incase if it was hidden */
            }

            if(callback && $.isFunction(callback.after)) { callback.after.call(target); }
        },

        /**
            @param {jQeury} target Target to dom element that should be replaced by new Image
                {@TODO in future flash/video/svg?}
            @param {String} link Target lo link that should be fetched
                {@TODO: in future parse string or replace with jQuery||SWFObject object}
            [@param {Object -> Function} callback Before and After setup switching
                before: {@borrows target as this},
                after: {@borrows newImg as this}]
            @returns {jQeury} newImg

            @TODO Optimize for reusable base wrapper for callbacks (eg. utils.BaseCallbackCall)
                {@see <a href="http://css-tricks.com/custom-events-are-pretty-cool/">Custom events vs Callbacks</a>}
         */
        replaceZoom: function(target, link, callback){
            var newImg = $('<img>', { src: link });

            if(callback && $.isFunction(callback.before)) { callback.before.call(target); }

            newImg.load(function(){
                target.replaceWith(newImg);
                if(callback && $.isFunction(callback.after)) { callback.after.call(newImg); }
            });

            return newImg;
        },

        /**
            @param {String|jQuery} target Wrapper where loading wrapper should be appended to
            [@param {Object -> Function} callback Before and After setup loading {@borrows target as this}]
            @returns {jQeury} Loading container

            @TODO Optimize for reusable base wrapper for callbacks (eg. utils.BaseCallbackCall)
                {@see <a href="http://css-tricks.com/custom-events-are-pretty-cool/">Custom events vs Callbacks</a>}
         */
        setLoading: function(target, callback){
            var settings = data.settings,
                prefix = settings.main.prefix,
                inner = $('<div>', { id: prefix + "-text" }).text(settings.messages.loadingMessage),
                cont = $('<div>', { id: prefix + "-loading" }).append(inner);

            /** If loading in progress none of proccesses should be enabled */
            utils.setData({ loading: true });

            /** @see Script.aculo.us Effect.Base */
            if(callback && $.isFunction(callback.before)) { callback.before.call(target); }
            target.append(cont);
            if(callback && $.isFunction(callback.after)) { callback.after.call(target); }

            return cont;
        },

        /**
            @description Removes loading progress overlay
            [@param {Object} Before and After setup loading {@borrows target as this} {@description before event fires before loading flag unset}]

            @TODO Optimize for reusable base wrapper for callbacks (eg. utils.BaseCallbackCall)
                {@see <a href="http://css-tricks.com/custom-events-are-pretty-cool/">Custom events vs Callbacks</a>}
         */
        unsetLoading: function(callback){
            var vars = data,
                settings = vars.settings,
                timeout = settings.main.loadingFadeTimeout || 500;

            if(vars.loading){

                /** @see Script.aculo.us Effect.Base */
                if(callback && $.isFunction(callback.before)) { callback.before(); }

                $('#' + settings.main.prefix + '-loading')
                    .fadeOut(timeout, function(){
                        $(this).remove();
                        /** Unset only after "before" event and remove() itself */
                        utils.setData({ loading: false });

                        if(callback && $.isFunction(callback.after)) { callback.after(); }
                    });
            }
            return false;
        },

        /**
            @param {Object} obj
            @see jQuery.extend() <a href="http://api.jquery.com/jQuery.extend/">jQuery#extend</a>
            @description prior jQuery's $.data method for internal plugin variables
            @returns {Object} Returns object of plugin hash (key:value pairs) values

            @TODO Optimize to function(Arg1, Arg2, ..., ArgN){...}  OR  function({ 'key': Arg1, ..., 'keyN': ArgN }){...}
         */
        setData: function(obj){
            data = $.extend(true, {}, data, obj); /** Deep extending {@see <a href="http://api.jquery.com/jQuery.extend/">jQuery#extend</a>} */
            return data;
        },

        /**
            Because it is faster than Math.ceil(n)
            @see <a href="http://jsperf.com/math-ceil-vs-bitwise">Math.ceil vs Bitwise</a>
         */
        ceil: function(n){
            var f = (n << 0);
            return f === n ? f : f + 1;
        }

    },

    /**
        @private
        @description Internal plugin methods
     */
    method = {
        /**
            @constructor
            @borrows jQuery.magentoZoom[collection] as this
            @param {Object} options Set of custom options
            @returns {jQuery} this
         */
        init: function(options){
            var settings = $.extend(true, {}, $.fn.magentoZoom.defaults, options),
                isSingleInCollection = (this.length === 1 && this.is(settings.main.selector)) ||
                    (this.length === 2 && this[0].href === this[1].href);

            /** Are main image excluded from thumbnails list:
                1. if set through admin - it should return true
                2. if it is placeholder image - it should return false */

            /** @TODO: parametrize placeholder image */
            var equalsInCollection = this.filter('a[href="' + this.filter(settings.main.selector).attr('href') + '"]'),
                isExcluded = (equalsInCollection.length === 1 &&
                    equalsInCollection.attr('href').indexOf('/images/catalog/product/placeholder/') === -1);

            /** Determine current position of item in thumbnails if it is NOT excluded from thumbnails list */
            var index = (!isExcluded) ? this.not(settings.main.selector)
                                            .index(equalsInCollection.not(settings.main.selector)) : -1;

            utils.setData({ settings: settings, main: {
                thumbs: this.not(settings.main.selector),
                isExcluded: isExcluded,
                isSingleInCollection: isSingleInCollection
            },
                currentItem: index });

            /** @TODO: refactor */
            if(!isExcluded){
                equalsInCollection.not(settings.main.selector).addClass('active');
            }

            /** Itterate through collection */
            return this.each(function(){
                var $this = $(this);

                /** Is it a main image? */
                if($this.is(settings.main.selector)) {

                    method.drawContainers.call($this);
                    method.firstLoad.call($this);

                    utils.click.call($this);

                /** Otherwise manipulate on thumbnails */
                } else {
                     /** There are shouldn't be an execution if there is:
                        1. Only one image and it is main image;
                        2. If there is two images and both of them HREFs are equal
                        AND preventDefault() set to collection to override native behaviour
                    */
                    if( isSingleInCollection ) {
                        utils.click.call($this);
                    } else if(!data.settings.isOldMode) {
                        utils.click.call($this, function(){
                            if($this.hasClass('active')) {
                                if(settings.useGallery){
                                    method.showGallery();
                                }
                            } else {
                                method.thumbnailChange.call(this, data.main.wrapper);
                            }
                        });
                    }
                }

            });
        },

        /**
            @borrows vars.main.thumbs as this
            @param {jQuery} container Container for embedding loading progress animation
         */
        thumbnailChange: function(container, isGallery){
            var $this = $(this),
                isExcluded = data.main.isExcluded,
                index = (isGallery) ? data.gallery.thumbs.index($this) : data.main.thumbs.index($this),
                correspondingItem = (data.settings.useGallery) ?
                    $((isGallery) ? data.main.thumbs.get(index) : data.gallery.thumbs.get(index)) : undefined,
                small = {
                    link: $this.attr('rel'),
                    image: data.main.image.find('img')
                },
                large = {
                    link: $this.attr('href'),
                    image: data.enlarged.image
                },
                thumb = {
                    link: data.main.image.attr('rel'),
                    image: $this.find('img')
                };

            if(!data.loading){
                /** Because every image loaded async */
                utils.setData({ loading : true });

                /** Assume that Large image is largest */
                /** @TODO: Parametrize active class */
                utils.switchImage(large.image, large.link, {
                    before: function(){
                        utils.setLoading(container);
                        /** Assume that only one placeholder can be in whole gallery */
                        if(data.main.image.hasClass('isPlaceholder')){
                            data.main.image.removeClass('isPlaceholder');
                        }

                        utils.switchImage(small.image, small.link, {
                            after: function(){
                                if(isExcluded){
                                    utils.switchImage(thumb.image, thumb.link , {
                                        after: function(){

                                            $this.attr({
                                                rel: small.image.attr('src'),
                                                href: large.image.attr('src')
                                            });
                                            data.main.image.attr('rel', thumb.image.attr('src'));

                                            /** Thumbnails should be changed in gallery too */
                                            if(correspondingItem){
                                                utils.switchImage(correspondingItem.find('img'), thumb.link, {
                                                    after: function(){
                                                        correspondingItem.attr({
                                                            rel: small.image.attr('src'),
                                                            href: large.image.attr('src')
                                                        });
                                                    }
                                                });

                                            }

                                        }
                                    });
                                } else {
                                    /**
                                        @TODO: parametrize active class
                                     */
                                    data.main.thumbs.removeClass('active');
                                    if(data.settings.useGallery){
                                        data.gallery.thumbs.removeClass('active');
                                    }

                                    $this.addClass('active');
                                    if(data.settings.useGallery){
                                        correspondingItem.addClass('active');
                                    }
                                }
                            }
                        });
                    },
                    after: function(){
                        data.enlarged.wrapper.show();
                        utils.recalculateSize(data.main.image, this, {
                            after: function(){
                                if(!isGallery){
                                    data.enlarged.wrapper.hide();
                                } else {
                                    utils.reCalculateGallerySize();
                                }
                                if(data.loading){
                                    if(!isExcluded) {
                                        utils.setData({ currentItem: index });
                                    }
                                    utils.unsetLoading();
                                }
                            }
                        });
                    }
                });
            }
        },

        /**
            @description Draw containers for unobtrusive purposes
            @borrows jQuery(settings.main.selector) as this
         */
        drawContainers: function(){
            var wrapper, track, notice, zoom, lens,
                body = $(document.body),
                settings = data.settings,
                prefix = settings.main.prefix;

            this.wrap($('<div>', { id: prefix + '-wrapper' }))
                .after($('<div>', { id: prefix + '-track' }));

            body.append($('<div>', { "class": prefix + '-enlarged' }));

            track = $('#' + prefix + '-track');
            wrapper = $('#' + prefix + '-wrapper');
            notice = wrapper.parent().children('.notice');
            zoom = $('.' + prefix + '-enlarged');

            track.append($('<div>', { id: prefix + '-lens' }));
            lens = $('#' + prefix + '-lens');

            if($.browser.msie) {
                track.css('background', 'url(".")');
                zoom.css('background', 'url(".")');
            }

            utils.setData({
                main: {
                    image: this,
                    track: track,
                    wrapper: wrapper,
                    notice: notice
                },
                enlarged: {
                    wrapper: zoom
                },
                lens: {
                    magnifier: lens
                }
            });
        },

        /**
            @description Loads an image from jQuery(settings.main.selector).attr('href')
                & onLoad event append it to zoom container
            @borrows jQuery(settings.main.selector) as this
         */
        firstLoad: function(){
            var lImg,
                sImg = this,
                container = data.enlarged.wrapper,
                track = data.main.track,
                settings = data.settings;

            utils.setLoading(track);

            lImg = $('<img>', { src: this.attr('href') });

            lImg.bind({
                /**
                    @event
                 */
                load: function(){
                    //following line failed jslint
                    //var w,h,ieH,ieW,ieMT,ieML,isLarger;

                    container.append(lImg);
                    utils.recalculateSize(sImg, lImg, {
                        after: function(){
                            if(data.loading){
                                container.hide();
                                if(settings.useGallery){
                                    method.prepareGallery.call(this);
                                }
                                utils.unsetLoading();
                            }
                        }
                    });
                },

                /**
                    @event
                 */
                error: function(){
                    throw new Error(settings.messages.loadError);
                }
            });
        },

        /**
            @description Attach events (according to configuration) to track element
            @borrows data.main.track as this
         */
        attachTrackEvents: function(){
            var settings = data.settings;

            switch(settings.enlarged.action){
                case 'over':
                    this.bind('mouseenter', method.showZoom);
                    break;
                case 'click':
                    this.bind('click', method.showZoom);
                    break;
            }

            this.bind({
                mouseleave: method.hideZoom,
                mousemove: method.onMouseMove
            });
        },

        /**
            @event
            @param {Object} e Event object
            @description Do as more as posible less actions here for performance purposes
                and ONLY incase if no loading animation in progress
         */
        onMouseMove: function(e){
            var $this = $(this);
            if(!data.loading && $this.hasClass(data.settings.main.activeTrackClass)){
                data.pageX = e.pageX;
                data.pageY = e.pageY;

                if($.browser.msie) {
                    method.redrawZoom();
                }
            }
        },

        /**
            @event
            @param {Object} e Event object
            @description Will reveal enlarged container to the right side (by default) of main image
                ONLY incase if no loading animation in progress
         */
        showZoom: function(e){
            var $this = $(this),
                settings = data.settings,
                track = data.main.track,
                lens = data.lens,
                enlarged = data.enlarged.wrapper;

            if(!data.loading){
                if(!$this.hasClass(settings.main.activeTrackClass) && settings.useLens) {

                    utils.setData({
                        pageX: e.pageX,
                        pageY: e.pageY,

                        lens: {
                            max: {
                                x: track.outerWidth() - lens.width,
                                y: track.outerHeight() - lens.height
                            },
                            cordsCorrection: {
                                x: utils.ceil(track.offset().left + ( lens.width / 2 )),
                                y: utils.ceil(track.offset().top + ( lens.height / 2 ))
                            }
                        }
                    });

                    enlarged.css({
                        left: utils.ceil(track.offset().left + data.main.trackWidth + settings.enlarged.adjustment), /** @TODO: FIX IE9 */
                        top: utils.ceil(track.offset().top)
                    });

                    method.redrawZoom();

                    if(settings.useLens){
                        lens.magnifier.show();
                    }

                    if(settings.useGallery && settings.swapNotices){
                        utils.switchNotice(settings.messages.noticeLightBox);
                    }

                    $this.addClass(settings.main.activeTrackClass);
                    enlarged.show();

                } else if (settings.useGallery){
                    method.showGallery();
                }

            }
        },

        /**
            @event
            @param {Object} e Event object
            @description Will hide enlarged image
                ONLY incase if no loading animation in progress
         */
        hideZoom: function() {
            var $this = $(this),
                settings = data.settings;

            if(!$this.hasClass(settings.gallery.activeGalleryClass) && !data.loading) {

                data.enlarged.wrapper.hide();
                data.main.track.removeClass(settings.main.activeTrackClass);

                if(settings.useGallery && settings.swapNotices ){
                    utils.switchNotice(settings.messages.noticeDefault);
                }

                if(settings.useLens) {
                    data.lens.magnifier.hide();
                }

                $this.removeClass(settings.main.activeTrackClass);

            }

            clearTimeout(data.timer);
        },

        /**
            @description Re-position zoomed image for onMouseMove event
         */
        redrawZoom: function(){
            var vars = data,
                x = (vars.pageX - vars.lens.cordsCorrection.x),
                y = (vars.pageY - vars.lens.cordsCorrection.y),
                max = vars.lens.max;

            if(x < 0) {
                x = 0;
            } else if (x > max.x) {
                x = (data.lens.moveOnX) ? max.x : 0;
            }

            if(y < 0) {
                y = 0;
            } else if (y > max.y) {
                y = (data.lens.moveOnY) ? max.y : 0;
            }

            if(vars.settings.useLens){
                vars.lens.magnifier.css({
                    left: x,
                    top: y
                });
            }

            vars.enlarged.image.css({
                left: -(utils.ceil(x * vars.enlarged.ratio)),
                top: -(utils.ceil(y * vars.enlarged.ratio))
            });

            if(!$.browser.msie) {
                data.timer = setTimeout(method.redrawZoom, vars.settings.refreshRate);
            }
        },

        /**************** Lightbox functionality ******************/

        /**
            @description Draw additional containers for lightbox functionality
            @borrows data.enlarged.image as this

            @TODO: Improve performance on jQuery selectors
         */
        prepareGallery: function(){
            var zoom, thumbsContainer,
                clone = data.main.thumbs.parent('li').parent().clone(),
                thumbs = clone.find('a'),
                prefix = data.settings.main.prefix;

            this.wrap($('<div>', { id: prefix + '-enlarged-inner' }))
                .wrap($('<div>', { id: prefix + '-enlarged-controls' }));

            zoom = $('#' + prefix + '-enlarged-inner');

            zoom.append($('<div>', { id: prefix + '-gallery-thumbs' }))
                .append($('<div>', { id: prefix + '-gallery-close' }))
                .children('#' + prefix + '-enlarged-controls')
                .append($('<div>', { rel: prefix + '-prev' }),
                        $('<div>', { rel: prefix + '-next' }));

            zoom.children('#' + prefix + '-gallery-thumbs')
                .css('overflow', 'hidden') /** @TODO: Refactor + parametrize + move to CSS */
                .append(clone);

            thumbsContainer = clone.parent('#' + prefix + '-gallery-thumbs').hide();

            thumbsContainer.append($('<div>', { rel: prefix + '-prev-slide' }), $('<div>', { rel: prefix + '-next-slide' }));

            utils.setData({ gallery: {
                gallery: zoom,
                close: $('#' + prefix + '-gallery-close'),
                next: zoom.find('[rel=' + prefix + '-next]'),
                prev: zoom.find('[rel=' + prefix + '-prev]'),
                thumbs: thumbs,
                thumbsContainer: thumbsContainer,
                prevSlide: thumbsContainer.find('[rel=' + prefix + '-prev-slide]'),
                nextSlide: thumbsContainer.find('[rel=' + prefix + '-next-slide]'),
                galleryWrapper: zoom.children('#' + prefix + '-enlarged-controls')
            }});

            data.gallery.close.hide();

            if($.browser.msie) {
                // data.gallery.next.css('background', 'url(".")');
                // data.gallery.prev.css('background', 'url(".")');
                data.enlarged.wrapper.css('background', 'url(".")');
            }

            method.attachGalleryEvents();

        },

        /**
            @description Attach gallery controls events
         */
        attachGalleryEvents: function(){
            if(!data.main.isSingleInCollection){
                utils.click.call(data.gallery.thumbs, function(){
                    method.thumbnailChange.call(this, data.gallery.galleryWrapper, true);
                });

                data.gallery.next.bind('click', method.galleryNext);
                data.gallery.prev.bind('click', method.galleryPrev);

                data.gallery.nextSlide.bind('click', method.slideNext);
                data.gallery.prevSlide.bind('click', method.slidePrev);

            }

            data.gallery.close.bind('click', method.hideGallery);

            data.enlarged.wrapper.bind('click', function(event){
                if(event.target === this){
                    method.hideGallery();
                }
            });
        },

        /**
            @description Open gallery and attach document events

            @TODO: parametrize names
         */
        showGallery: function(){
            var settings = data.settings,
                isNotSingleInCollection = (!data.main.isSingleInCollection),
                thumbsItemWidth, visibleThumbs;

            data.gallery.close.show();

            if(isNotSingleInCollection){
                data.gallery.thumbsContainer.show();
            } else {
                data.gallery.next.hide();
                data.gallery.prev.hide();
            }

            data.main.track
                .removeClass(settings.main.activeTrackClass)
                .addClass(settings.gallery.activeGalleryClass);

            if(settings.useLens){
                data.lens.magnifier.hide();
            }

            if(data.enlarged.isLarger){
                utils.switchNotice(settings.messages.noticeDefault);
            }

            data.enlarged.wrapper
                .show()
                // .removeAttr('style')
                .addClass('lightbox');

            $('body').addClass('js-lightbox');

            if(isNotSingleInCollection){
                thumbsItemWidth = $(data.gallery.thumbs[1]).parent().outerWidth(true);
                visibleThumbs = (data.gallery.thumbsContainer.width() / thumbsItemWidth) >> 0;

                if(visibleThumbs >= data.gallery.thumbs.length) {
                    data.gallery.nextSlide.unbind().remove();
                    data.gallery.prevSlide.unbind().remove();
                }
            }

            utils.reCalculateGallerySize();

            if(settings.gallery.useHotkeys){
                utils.bindDocumentEvents();
            }
        },

        /**
            @description Close gallery and dettach document events

            @TODO: parametrize names
         */
        hideGallery: function(){
            var settings = data.settings;

            if(settings.gallery.useHotkeys){
                utils.unBindDocumentEvents();
            }

            data.enlarged.image.css({maxHeight:'',maxWidth:''});

            data.main.track.removeClass(settings.gallery.activeGalleryClass);

            data.enlarged.wrapper.removeClass('lightbox');
            $('body').removeClass('js-lightbox');

            utils.recalculateSize(data.main.image, data.enlarged.image);

            data.gallery.thumbsContainer.hide();
            data.enlarged.wrapper.hide();
            data.gallery.close.hide();


        },

        /**
            @TODO: REFACTOR
         */
        /**
            @see .goTo
         */
        galleryNext: function(){
            method.goTo('next');
        },

        /**
            @see .goTo
         */
        galleryPrev: function(){
            method.goTo('prev');
        },

        /**
            @description Swaps all images to new ones next/previous to current
         */
        goTo: function(direction){
            var $this, index, small, large, thumb, correspondingItem,
                thumbs = data.gallery.thumbs,
                length = thumbs.length,
                isExcluded = data.main.isExcluded,
                current = data.currentItem,
                sd = data.switchDirection,
                wasNext = sd === 'next' || sd === null,
                wasPrev = sd === 'prev' || sd === null;

            switch(direction){
                case 'next':
                    index = (!wasNext) ? current : ((current  + 1) === length) ? 0 : current + 1;
                    if(isExcluded) {
                        data.switchDirection = 'next';
                    }
                    break;
                case 'prev':
                    index = (!wasPrev) ? current : (current <= 0) ? length - 1 : current - 1;
                    if(isExcluded) {
                        data.switchDirection = 'prev';
                    }
                    break;
            }

            $this = $(thumbs.get(index || 0));
            correspondingItem = $(data.main.thumbs.get(index));
            small = {
                link: $this.attr('rel'),
                image: data.main.image.find('img')
            };
            large = {
                link: $this.attr('href'),
                image: data.enlarged.image
            };
            thumb = {
                link: data.main.image.attr('rel'),
                image: $this.find('img')
            };

            if(!data.loading){
                /** Because every image loaded async */
                utils.setData({ loading : true });

                /** Assume that Large image is largest */
                /** @TODO: Parametrize active class */
                utils.switchImage(large.image, large.link, {
                    before: function(){
                        utils.setLoading(data.gallery.galleryWrapper);
                        utils.switchImage(small.image, small.link, {
                            after: function(){
                                if(isExcluded){
                                    utils.switchImage(thumb.image, thumb.link , {
                                        after: function(){
                                            $this.attr({
                                                rel: small.image.attr('src'),
                                                href: large.image.attr('src')
                                            });
                                            data.main.image.attr('rel', thumb.image.attr('src'));

                                            /** Thumbnails should be changed in main thumbnails too */
                                            utils.switchImage(correspondingItem.find('img'), thumb.link, {
                                                after: function(){
                                                    correspondingItem.attr({
                                                        rel: small.image.attr('src'),
                                                        href: large.image.attr('src')
                                                    });
                                                }
                                            });
                                        }
                                    });
                                } else {
                                    /**
                                        @TODO: parametrize active class
                                     */
                                    data.main.thumbs.removeClass('active');
                                    data.gallery.thumbs.removeClass('active');

                                    $this.addClass('active');
                                    correspondingItem.addClass('active');
                                }
                            }
                        });
                    },
                    after: function(){
                        utils.recalculateSize(data.main.image, this, {
                            after: function(){
                                utils.reCalculateGallerySize();
                                if(data.loading){
                                    utils.setData({ currentItem: index });
                                    utils.unsetLoading();
                                }
                            }
                        });
                    }
                });
            }
        },

        slidePrev:function(){
            method.triggerSlider('prev');
        },

        slideNext:function(){
            method.triggerSlider('next');
        },

        triggerSlider: function(direction){
            var itemWidth = $(data.gallery.thumbs[1]).parent().outerWidth(true),
                thumbsContainer = data.gallery.thumbsContainer,
                container = thumbsContainer.find('> ul'),
                currentPosition = parseInt(container.css('left') , 10),
                isLastItem = (((data.gallery.thumbs.length * itemWidth) + currentPosition ) - thumbsContainer.width() - 16) <= 0,
                isFirstItem = container.css('left') === ('0px' || 'auto'),
                _ = (direction === 'prev') ? ((!isFirstItem) ? '+=' + itemWidth : 0) : ((!isLastItem) ? '-=' + itemWidth : currentPosition);

            if(!data.loading){
                data.loading = true;
                container.animate({
                    left: _
                }, 'fast', function(){
                    data.loading = false;
                });
            }
        }

    },

    pub = {
        /**
            @TODO:
            destroy()

         */
    };


    /**
        Magento Zoom jQuery plugin
        @class Represents accessing to privat and global methods
        @param {String|Object} action Method switcher for plugin
     */
    $.fn.magentoZoom = function(action) {
        if (pub[action]){
            return pub[action].apply(this, [].prototype.slice.call( arguments, 1 ));
        } else if (typeof action === 'object' || !action){
            return method.init.apply( this, arguments );
        } else {
            $.error('Method ' + action + ' does not exist on jQuery.magentoZoom');
        }
    };

    /**
        @private
        @default Object
        @memberOf jQuery.magentoZoom
     */
    $.fn.magentoZoom.defaults = {
        main: {
            activeTrackClass: 'zoom-activated',
            selector: '[data-role=base-image-zoom]',
            prefix: 'magento-zoom'
        },
        lens: {
            mode: 'standart',
            opacity: 0.5
        },
        enlarged: {
            adjustment: 20,
            action: 'click',
            width: 370,
            height: 800
        },
        gallery: {
            activeGalleryClass: 'gallery-activated',
            useHotkeys: true
        },
        useLens: true,
        useGallery: true,
        swapNotices: true,
        isOldMode: false,
        refreshRate: 30,
        messages: {
            loadError: "Unable to load media file, please check path of source file",
            noticeDefault: "Click on image to zoom",
            noticeLightBox: "Click on image to view it full sized",
            loadingMessage: "Loading..."
        }
    };
})(jQuery, document, window);
