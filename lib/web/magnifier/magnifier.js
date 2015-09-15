/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

;(function ($) {
    $.fn.magnify = function (options) {
        'use strict';

        var magnify = new Magnify($(this), options);
        /*events must be tracked here*/

        /**
         * Return that from _init function
         *
         */
        return magnify;

    };

    function Magnify(element, options) {
        var gOptions = options || {},
            $box = $(element),
            $thumb,
            that = this,
            largeWrapper = options.largeWrapper ||  ".magnifier-preview",
            $largeWrapper = $(largeWrapper);
        curThumb = null,
            currentOpts = {
                x: 0,
                y: 0,
                w: 0,
                h: 0,
                lensW: 0,
                lensH: 0,
                lensBgX: 0,
                lensBgY: 0,
                largeW: 0,
                largeH: 0,
                largeL: 0,
                largeT: 0,
                zoom: 2,
                zoomMin: 1.1,
                zoomMax: 5,
                mode: 'outside',
                eventType: 'click',
                status: 0,
                zoomAttached: false,
                zoomable: (gOptions.zoomable !== undefined)
                    ? gOptions.zoomable
                    : false,
                onthumbenter: (gOptions.onthumbenter !== undefined)
                    ? gOptions.onthumbenter
                    : null,
                onthumbmove: (gOptions.onthumbmove !== undefined)
                    ? gOptions.onthumbmove
                    : null,
                onthumbleave: (gOptions.onthumbleave !== undefined)
                    ? gOptions.onthumbleave
                    : null,
                onzoom: (gOptions.onzoom !== undefined)
                    ? gOptions.onzoom
                    : null
            },
            pos = {
                t: 0,
                l: 0,
                x: 0,
                y: 0
            },
            gId = 0,
            status = 0,
            curIdx = '',
            curLens = null,
            curLarge = null,
            lensbg = (gOptions.bg !== undefined) ? gOptions.lensbg : true,
            gZoom = (gOptions.zoom !== undefined)
                ? gOptions.zoom
                : currentOpts.zoom,
            gZoomMin = (gOptions.zoomMin !== undefined)
                ? gOptions.zoomMin
                : currentOpts.zoomMin,
            gZoomMax = (gOptions.zoomMax !== undefined)
                ? gOptions.zoomMax
                : currentOpts.zoomMax,
            gMode = gOptions.mode || currentOpts.mode,
            gEventType = gOptions.eventType || currentOpts.eventType,
            data = {},
            inBounds = false,
            isOverThumb = false,
            rate = 1,
            paddingX = 0,
            paddingY = 0,
            enabled = true,
            showWrapper = true;

        var MagnifyCls = {
            magnifyHidden: "magnify-hidden",
            magnifyOpaque: "magnify-opaque"
        };


        /**
         * Update Lens positon on.
         *
         */
        that.update = function () {
            updateLensOnLoad();
        };

        /**
         * Init new Magnifier
         *
         */
        that.init = function () {
            _init($box, options);
        };

        /**
         * Delete events and created block for magnify
         *
         */
        that.destroy  = function () {
            console.warn("API not implemented.");
        };

        /**
         * @todo
         */
        that.zoomIn = function () {
            console.warn("API not implemented.");
        };

        /**
         * @todo
         */
        that.zoomOut = function () {
            console.warn("API not implemented.");
        };

        /**
         * @todo
         */
        that.show = function () {
            console.warn("API not implemented.");
        };

        /**
         * @todo
         */
        that.hide = function () {
            console.warn("API not implemented.");
        };

        function createLens(thumb) {
            if ($(thumb).siblings('.magnify-lens').length) {
                return false;
            }
            var lens = $('<div class="magnify-lens" class="magnifier-loader" data-gallery-role="magnifier-zoom"></div>');
            $(thumb).parent().append(lens);
        }

        function updateLensOnLoad(idx, thumb, large, largeWrapper) {
            var lens = $box.find('.magnify-lens'),
                textWrapper;

            if (data[idx].status === 1) {
                textWrapper = $('<div class="magnifier-loader-text"></div>');
                lens.className = 'magnifier-loader magnify-hidden';
                textWrapper.html("Loading...");
                lens.html("").append(textWrapper);
            } else if (data[idx].status === 2) {
                lens.addClass(MagnifyCls.magnifyHidden);
                lens.html("");
                large.id = idx + '-large';
                large.style.width = Math.round(data[idx].largeW * rate) + 'px';
                large.style.height = data[idx].largeH + 'px';
                large.className = 'magnifier-large magnify-hidden';

                if (data[idx].mode === 'inside') {
                    lens.append(large);
                } else {
                    largeWrapper.html("").append(large);
                }
            }

            data[idx].lensH = data[idx].lensH > $thumb.height() ? $thumb.height() : data[idx].lensH;
            lens.css({
                width: data[idx].lensW + 1 + 'px',
                height: data[idx].lensH + 0.5 + 'px'
            });
        }

        function getMousePos() {
            var xPos = pos.x - currentOpts.x,
                yPos = pos.y - currentOpts.y,
                t,
                l;

            inBounds = ( xPos < 0 || yPos < 0 || xPos > currentOpts.w || yPos > currentOpts.h ) ? false : true;

            l = xPos - Math.round(currentOpts.lensW / 2);
            t = yPos - Math.round(currentOpts.lensH / 2);

            if (currentOpts.mode !== 'inside') {
                if (xPos < currentOpts.lensW / 2) {
                    l = 0;
                }

                if (yPos < currentOpts.lensH / 2) {
                    t = 0;
                }

                if (xPos - currentOpts.w + Math.ceil(currentOpts.lensW / 2) > 0) {
                    l = currentOpts.w - Math.ceil(currentOpts.lensW + 2);
                }

                if (yPos - currentOpts.h + Math.ceil(currentOpts.lensH / 2) > 0) {
                    t = currentOpts.h - Math.ceil(currentOpts.lensH + 2);
                }

                pos.l = Math.round(l);
                pos.t = Math.round(t);

                currentOpts.lensBgX = pos.l + 1;
                currentOpts.lensBgY = pos.t + 1;

                if (currentOpts.mode === 'inside') {
                    currentOpts.largeL = Math.round(xPos * (currentOpts.zoom - (currentOpts.lensW / currentOpts.w)));
                    currentOpts.largeT = Math.round(yPos * (currentOpts.zoom - (currentOpts.lensH / currentOpts.h)));
                } else {
                    currentOpts.largeL = Math.round(currentOpts.lensBgX * currentOpts.zoom * (currentOpts.largeWrapperW / currentOpts.w) * rate);
                    currentOpts.largeT = Math.round(currentOpts.lensBgY * currentOpts.zoom * (currentOpts.largeWrapperH / currentOpts.h));
                }
            }
        }



        function onThumbEnter() {
            if (enabled === true) {
                currentOpts = data[curIdx];
                curLens = $box.find('.magnify-lens');

                if (currentOpts.status === 2) {
                    curLens.removeClass(MagnifyCls.magnifyOpaque);
                    curLarge = $('#' + curIdx + '-large');
                    curLarge.removeClass(MagnifyCls.magnifyHidden);
                } else if (currentOpts.status === 1) {
                    curLens.className = 'magnifier-loader';
                }
            }
        }

        function onThumbLeave() {
            if (currentOpts.status > 0) {
                var handler = currentOpts.onthumbleave;

                if (handler !== null) {
                    handler({
                        thumb: curThumb,
                        lens: curLens,
                        large: curLarge,
                        x: pos.x,
                        y: pos.y
                    });
                }
                if (!curLens.hasClass(MagnifyCls.magnifyHidden)) {
                    curLens.addClass(MagnifyCls.magnifyHidden);

                    //$curThumb.removeClass(MagnifyCls.magnifyOpaque);
                    if (curLarge !== null) {
                        curLarge.addClass(MagnifyCls.magnifyHidden);
                    }
                }
            }
        }

        function move() {
            if (enabled === true) {
                if (status !== currentOpts.status) {
                    onThumbEnter();
                }

                if (currentOpts.status > 0) {
                    curThumb.className = currentOpts.thumbCssClass + ' magnify-opaque';

                    if (currentOpts.status === 1) {
                        curLens.className = 'magnifier-loader';
                    } else if (currentOpts.status === 2) {
                        curLens.removeClass(MagnifyCls.magnifyHidden);
                        curLarge.removeClass(MagnifyCls.magnifyHidden);
                        curLarge.css({
                            left: '-' + currentOpts.largeL + 'px',
                            top: '-' + currentOpts.largeT + 'px'
                        });
                    }

                    pos.t = pos.t <= 0 ? 0 : pos.t;
                    pos.t = pos.t > 0 ? pos.t: pos.t;
                    pos.l = pos.l <= 0 ? 0 : pos.l;
                    //pos.l = pos.l > 0 ? pos.l : pos.l;
                    curLens.css({
                        left: pos.l + paddingX +'px',
                        top: pos.t + paddingY + 1.75 + 'px'
                    });

                    if (lensbg) {
                        curLens.css({
                            "background-color": "rgba(f,f,f,.5)"
                        });
                    } else {
                        curLens.get(0).style.backgroundPosition = '-' +
                        currentOpts.lensBgX + 'px -' +
                        currentOpts.lensBgY + 'px';
                    }
                    var handler = currentOpts.onthumbmove;

                    if (handler !== null) {
                        handler({
                            thumb: curThumb,
                            lens: curLens,
                            large: curLarge,
                            x: pos.x,
                            y: pos.y
                        });
                    }
                }

                status = currentOpts.status;
            }
        }

        function setThumbData(thumb, thumbData) {
            var thumbBounds = thumb.getBoundingClientRect(),
                w = 0,
                h = 0;

            thumbData.x = thumbBounds.left;
            thumbData.y = thumbBounds.top;
            thumbData.w = Math.round(thumbBounds.right - thumbData.x);
            thumbData.h = Math.round(thumbBounds.bottom - thumbData.y);

            if (thumbData.mode === 'inside') {
                w = thumbData.w;
                h = thumbData.h;
            } else {
                w = thumbData.largeWrapperW;
                h = thumbData.largeWrapperH;
            }

            thumbData.largeW = Math.round(thumbData.zoom * w);
            thumbData.largeH = Math.round(thumbData.zoom * h);

            thumbData.lensW = (thumbData.w / thumbData.zoom) / rate;
            thumbData.lensH = thumbData.h / thumbData.zoom;
        }

        function _init($box, options) {
            var opts = {};
            if (options.thumb === undefined) {
                return false;
            }

            $thumb = $box.find(options.thumb);

            if ($thumb.length) {
                for (var key in options) {
                    opts[key] = options[key];
                }

                opts.thumb = $thumb;
                enabled = opts.enabled;

                if(enabled) {

                    $largeWrapper.show().css('display', '');
                    $largeWrapper.addClass(MagnifyCls.magnifyHidden);
                    set(opts);
                } else {
                    $largeWrapper.empty().hide();
                }
            }

            return that;
        }

        function hoverEvents(thumb) {
            $(thumb).on('mouseover', function (e) {

                if (showWrapper) {

                    if (currentOpts.status !== 0) {
                        onThumbLeave();
                    }
                    handleEvents(e);
                    isOverThumb = inBounds;
                }
            }).trigger('mouseover');
        }

        function clickEvents(thumb) {
            $(thumb).on('click', function (e) {

                if (showWrapper) {
                    if (!isOverThumb) {
                        if (currentOpts.status !== 0) {
                            onThumbLeave();
                        }
                        handleEvents(e);
                        isOverThumb = true;
                    }
                }
            });
        }

        function bindEvents(eType, thumb) {
            switch (eType) {
                case 'hover':
                    hoverEvents(thumb);
                    break;
                case 'click':
                    clickEvents(thumb);
                    break;
            }
        }

        function handleEvents(e) {
            var src = e.target;
            curIdx = src.id;
            curThumb = src;

            onThumbEnter(src);

            setThumbData(curThumb, currentOpts);

            pos.x = e.clientX;
            pos.y = e.clientY;

            getMousePos();
            move();

            var handler = currentOpts.onthumbenter;

            if (handler !== null) {
                handler({
                    thumb: curThumb,
                    lens: curLens,
                    large: curLarge,
                    x: pos.x,
                    y: pos.y
                });
            }
        }

        function set(options) {
            if (data[options.thumb.id] !== undefined) {
                curThumb = options.thumb;
                return false;
            }

            var thumbObj = new Image(),
                largeObj = new Image(),
                $thumb = options.thumb,
                thumb = $thumb.get(0),
                idx = thumb.id,
                largeUrl,
                largeWrapper = $(options.largeWrapper),
                zoom = options.zoom || thumb.getAttribute('data-zoom') || gZoom,
                zoomMin = options.zoomMin || gZoomMin,
                zoomMax = options.zoomMax || gZoomMax,
                mode = options.mode || thumb.getAttribute('data-mode') || gMode,
                eventType = options.eventType || thumb.getAttribute('data-eventType') || gEventType,
                onthumbenter = (options.onthumbenter !== undefined)
                    ? options.onthumbenter
                    : currentOpts.onthumbenter,
                onthumbleave = (options.onthumbleave !== undefined)
                    ? options.onthumbleave
                    : currentOpts.onthumbleave,
                onthumbmove = (options.onthumbmove !== undefined)
                    ? options.onthumbmove
                    : currentOpts.onthumbmove;

            largeUrl = gOptions.original || $thumb.attr('src');

            if (thumb.id === '') {
                idx = thumb.id = 'magnifier-item-' + gId;
                gId += 1;
            }

            createLens(thumb, idx);

            if (options.width) {
                largeWrapper.width(options.width);
            }
            if (options.height) {
                largeWrapper.height(options.height);
            }
            if (options.top) {
                if (typeof options.top == 'function') {
                    var top = options.top() + 'px';
                } else {
                    var top = options.top + 'px';
                }
                largeWrapper[0].style.top = top.replace("%px", "%");
            }
            if (options.left) {
                if (typeof options.left == 'function') {
                    var left = options.left() + 'px';
                } else {
                    var left = options.left + 'px';
                }
                largeWrapper[0].style.left = left.replace("%px", "%");
            }

            data[idx] = {
                zoom: zoom,
                zoomMin: zoomMin,
                zoomMax: zoomMax,
                mode: mode,
                eventType: eventType,
                thumbCssClass: thumb.className,
                zoomAttached: false,
                status: 0,
                largeUrl: largeUrl,
                largeWrapperId: mode === 'outside' ? largeWrapper.attr('id') : null,
                largeWrapperW: mode === 'outside' ? largeWrapper.width() : null,
                largeWrapperH: mode === 'outside' ? largeWrapper.height() : null,
                onthumbenter: onthumbenter,
                onthumbleave: onthumbleave,
                onthumbmove: onthumbmove
            };

            rate = ($thumb.width() / $thumb.height()) / (data[idx].largeWrapperW / data[idx].largeWrapperH);
            paddingX = ($thumb.parent().width() - $thumb.width()) / 2;
            paddingY = ($thumb.parent().height() - $thumb.height()) / 2;

            showWrapper = false;
            $(thumbObj).on('load', function () {
                data[idx].status = 1;

                $(largeObj).on('load', function () {

                    if ((largeObj.width > largeWrapper.width()) || (largeObj.height > largeWrapper.height())) {
                        showWrapper = true;
                        bindEvents(eventType, thumb);
                        data[idx].status = 2;
                        data[idx].zoom = largeObj.height / largeWrapper.height();
                        setThumbData(thumb, data[idx]);
                        updateLensOnLoad(idx, thumb, largeObj, largeWrapper);
                    }
                });

                largeObj.src = data[idx].largeUrl;
            });

            thumbObj.src = thumb.src;
        }

        function onMousemove(e) {

            pos.x = e.clientX;
            pos.y = e.clientY;

            getMousePos();

            if (gEventType === 'hover') {
                isOverThumb = inBounds;
            }

            if (inBounds && isOverThumb) {
                $largeWrapper.removeClass(MagnifyCls.magnifyHidden);
                move();
            } else {
                onThumbLeave();
                isOverThumb = false;
                $largeWrapper.addClass(MagnifyCls.magnifyHidden);
            }
        }

        function magnifierFullscreen () {
            var isDragActive = false,
                startX,
                startY,
                imagePosX,
                imagePosY,
                touch,
                isTouchEnabled = 'ontouchstart' in document.documentElement;

            $('[data-gallery-role="gallery"]').on('fotorama:fullscreenenter fotorama:showend fotorama:load fotorama:ready', function () {

                var $image = $('[data-gallery-role="stage-shaft"] [data-active="true"] img'),
                    $imageContainer = $image.parent(),
                    gallery = $('[data-gallery-role="gallery"]');

                gallery.on('fotorama:fullscreenexit', function () {
                    $thumb.css({
                        'top': '',
                        'left': ''
                    });
                });

                if (gallery.data('fotorama').fullScreen) {

                    $('.fotorama__stage__frame .fotorama__img').each(function () {
                        var image = new Image();
                        image.src = $(this).attr("src");

                        if ( (image.height > $(this).parent().height()) || (image.width > $(this).parent().width()) ) {

                            if (image.height / image.width < $(this).parent().height() / $(this).parent().width()) {
                                $(this).width($(this).parent().width());
                                $(this).height('auto');
                            } else {
                                $(this).height($(this).parent().height());
                                $(this).width('auto');
                            }

                            $(this).css({
                                'top': '',
                                'left': ''
                            });
                        }

                    });
                }

                $image.on(isTouchEnabled ? 'touchstart' : 'pointerdown mousedown MSPointerDow', function (e) {
                    if (gallery.data('fotorama').fullScreen) {
                        e.preventDefault();

                        $image.css('cursor', 'move');
                        imagePosY = $image.offset().top;
                        imagePosX = $image.offset().left;

                        if (isTouchEnabled) {
                            touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
                            e.clientX = touch.pageX;
                            e.clientY = touch.pageY;
                        }
                        startX = e.clientX || e.originalEvent.clientX;
                        startY = e.clientY || e.originalEvent.clientY;
                        isDragActive = true;
                    }
                });



                $image.on(isTouchEnabled ? 'touchmove' : 'mousemove pointermove MSPointerMove', function (e) {
                    if (gallery.data('fotorama').fullScreen && isDragActive) {

                        var top,
                            left,
                            startOffset = $image.offset(),
                            clientX = e.clientX || e.originalEvent.clientX,
                            clientY = e.clientY || e.originalEvent.clientY;


                        e.preventDefault();

                        if (isTouchEnabled) {
                            touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
                            e.clientX = touch.pageX;
                            e.clientY = touch.pageY;
                        }
                        top = +imagePosY + (clientY - startY);
                        left = +imagePosX + (clientX - startX);

                        if ($image.height() > $imageContainer.height()) {

                            if (($imageContainer.offset().top + $imageContainer.height()) > (top + $image.height())) {
                                top = $imageContainer.offset().top + $imageContainer.height() - $image.height();
                            } else {
                                top = ($imageContainer.offset().top < top) ? 0 : top;
                            }
                            $image.offset({
                                'top': top
                            });
                        }

                        if ($image.width() > $imageContainer.width()) {

                            if (($imageContainer.offset().left + $imageContainer.width()) > (left + $image.width())) {
                                left = $imageContainer.offset().left + $imageContainer.width() - $image.width();
                            } else {
                                left = ($imageContainer.offset().left < left) ? $imageContainer.offset().left : left;
                            }
                            $image.offset({
                                'left': left
                            });
                        }
                    }
                });

                $image.on(isTouchEnabled ? 'touchend' : 'mouseup pointerup MSPointerUp', function (e) {
                    if (gallery.data('fotorama').fullScreen) {
                        isDragActive = false;
                        $image.css('cursor', 'pointer');
                    }
                });
            });
        };

        function onScroll() {

            if (curThumb !== null) {
                setThumbData(curThumb, currentOpts);
            }
        }

        if ($('.fotorama-item').data('fotorama').fullScreen) {
            $('.fotorama__stage__frame .fotorama__img').each(function () {
                var image = new Image();
                image.src = $(this).attr("src");

                if ( (image.height > $(this).parent().height()) || (image.width > $(this).parent().width()) ) {

                    if (image.height / image.width < $(this).parent().height() / $(this).parent().width()) {
                        $(this).width($(this).parent().width());
                        $(this).height('');
                    } else {
                        $(this).height($(this).parent().height());
                        $(this).width('');
                    }
                }
            });
        }

        $(window).on('scroll', onScroll);
        $(window).resize(function() {

            if ($('.fotorama-item').data('fotorama').fullScreen) {
                $('.fotorama__stage__frame .fotorama__img').each(function () {
                    var image = new Image();
                    image.src = $(this).attr("src");

                    if ( (image.height > $(this).parent().height()) || (image.width > $(this).parent().width()) ) {

                        if (image.height / image.width < $(this).parent().height() / $(this).parent().width()) {
                            $(this).width($(this).parent().width());
                            $(this).height('');
                        } else {
                            $(this).height($(this).parent().height());
                            $(this).width('');
                        }
                    }
                });
            }


            _init($box, gOptions);

        });

        function checkFullscreenImagePosition() {
            if ($('[data-gallery-role="gallery"]').data('fotorama').fullScreen) {

                var $image = $('[data-gallery-role="stage-shaft"] > [data-active="true"] > img'),
                    $imageContainer = $image.parent(),
                    top, left;

                if (($imageContainer.offset().top + $imageContainer.height()) > ($image.offset().top + $image.height())) {
                    top = $imageContainer.offset().top + $imageContainer.height() - $image.height();
                } else {
                    top = ($imageContainer.offset().top < $image.offset().top) ? 0 : top;
                }

                if (top !== undefined) {
                    $image.css('top', top);
                }

                if (($imageContainer.offset().left + $imageContainer.width()) > ($image.offset().left + $image.width())) {
                    left = $imageContainer.offset().left + $imageContainer.width() - $image.width();
                } else {
                    left = ($imageContainer.offset().left < $image.offset().left) ? 0 : left;
                }

                if (left !== undefined) {
                    $image.css('left', left);
                }

                if ($image.width() < $imageContainer.width()) {
                    $image.css('left', '');
                }

                if ($image.height() < $imageContainer.height()) {
                    $image.css('top', '');
                }
            }
        }

        function zoomIn(e) {
            var $image = $('[data-gallery-role="stage-shaft"] [data-active="true"] img'),
                gallery = $('[data-gallery-role="gallery"]'),
                imgOriginalSize = getImageSize($image[0].src),
                setedResult = Math.round($image.width() + 10);
            e.preventDefault();

            if (setedResult >imgOriginalSize.rw) {
                setedResult = imgOriginalSize.rw;
            }
            $image.css({'width': setedResult, height: 'auto'});
            checkFullscreenImagePosition();
        }

        function zoomOut(e) {
            var $image = $('[data-gallery-role="stage-shaft"] [data-active="true"] img'),
                gallery = $('[data-gallery-role="gallery"]'),
                imgOriginalSize = getImageSize($image[0].src),
                setedResult = Math.round($image.width() - 10);
            if(e) {
                e.preventDefault();
            }

            if (setedResult < imgOriginalSize.rw/2) {
                setedResult = imgOriginalSize.rw/2;
            }
            $image.css({'width': setedResult, height: 'auto'});
            checkFullscreenImagePosition();
        }

        /**
         * Return width and height of original image
         * @param src path for original image
         * @returns {{rw: number, rh: number}}
         */
        function getImageSize(src) {
            var img = new Image(),
                imgSize = {
                    rw: 0,
                    rh: 0
                };
            img.src = src;
            imgSize.rw = img.width;
            imgSize.rh = img.height;
            return imgSize;
        }


        function setEventOnce() {
            $('.fotorama__zoom-in')
                .off('click', zoomIn)
                .on('click', zoomIn);
            $('.fotorama__zoom-out')
                .off('click', zoomOut)
                .on('click', zoomOut);
        }

        $(document).on('mousemove', onMousemove);
        _init($box, gOptions);
        setEventOnce();
        //checkFullscreenImagePosition();
        magnifierFullscreen();

    }
}(jQuery));
