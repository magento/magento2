/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

;(function ($) {
    var onWheelCallback,
        zoomWidthStep = 0,
        zoomHeightStep = 0,
        isDraggable = false,
        isZoomActive = false;

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
            $largeWrapper = $(largeWrapper),
            zoomShown = false,
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
            magnifyOpaque: "magnify-opaque",
            magnifyFull: "magnify-fullimage"

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

        function _toBoolean (str) {
            if (typeof  str === 'string') {
                if (str === 'true') {
                    return true;
                } else if (str === 'false' || '') {
                    return false;
                } else {
                    console.warn("Wrong type: can't be transformed to Boolean");
                }
            } else if (typeof str === 'boolean') {
                return str;
            }
        }

        function createLens(thumb) {
            if ($(thumb).siblings('.magnify-lens').length) {
                return false;
            }
            var lens = $('<div class="magnify-lens magnify-hidden" data-gallery-role="magnifier-zoom"></div>');
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
            if (_toBoolean(enabled)) {
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
            if (_toBoolean(enabled)) {
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

                if(_toBoolean(enabled)) {

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

            largeUrl = gOptions.full || $thumb.attr('src');

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

                if (largeWrapper.length) {
                    largeWrapper[0].style.top = top.replace("%px", "%");
                }
            }
            if (options.left) {
                if (typeof options.left == 'function') {
                    var left = options.left() + 'px';
                } else {
                    var left = options.left + 'px';
                }

                if (largeWrapper.length) {
                    largeWrapper[0].style.left = left.replace("%px", "%");
                }
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

        function toggleZoomButtons($image) {
            var path = $image.attr('src'),
                isVideo = $image.parent().hasClass('fotorama-video-container'),
                imgSize;

            if (path && !isVideo) {
                imgSize = getImageSize(path);
                if ((imgSize.rh > $image.parent().height()) || (imgSize.rw > $image.parent().width())) {
                    $('.fotorama__zoom-in').show();
                    $('.fotorama__zoom-out').show();
                    zoomShown = true;
                } else {
                    $('.fotorama__zoom-in').hide();
                    $('.fotorama__zoom-out').hide();
                    zoomShown = false;
                }
            } else {
                $('.fotorama__zoom-in').hide();
                $('.fotorama__zoom-out').hide();
                zoomShown = false;
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

            $('[data-gallery-role="gallery"]').on('fotorama:fullscreenenter fotorama:showend fotorama:load', function () {
                var $preview = $('[data-gallery-role="stage-shaft"] [data-active="true"] img'),
                    $image = $('[data-gallery-role="stage-shaft"] [data-active="true"] .fotorama__img--full'),
                    $imageContainer = $preview.parent(),
                    gallery = $('[data-gallery-role="gallery"]');

                gallery.on('fotorama:fullscreenexit', function () {
                    $thumb.css({
                        'top': '',
                        'left': ''
                    });
                });

                if (gallery.data('fotorama').fullScreen) {
                    toggleZoomButtons($image);
                    resetVars($('[data-gallery-role="stage-shaft"] .fotorama__img--full'));

                    $('.fotorama__stage__frame .fotorama__img--full').each(function () {
                        var path = $(this).attr("src"),
                            imgSize;
                        if (path) {
                            imgSize = getImageSize(path);

                            if ((imgSize.rh > $(this).parent().height()) || (imgSize.rw > $(this).parent().width())) {

                                if (imgSize.rh / imgSize.rw < $(this).parent().height() / $(this).parent().width()) {
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
                        }
                    });
                }
                $image
                    .off(isTouchEnabled ? 'touchstart' : 'pointerdown mousedown MSPointerDown')
                    .on(isTouchEnabled ? 'touchstart' : 'pointerdown mousedown MSPointerDown', function (e) {
                        if (gallery.data('fotorama').fullScreen && isDraggable) {
                            e.preventDefault();
                            $image.css('cursor', 'move');
                            imagePosY = $image.offset().top;
                            imagePosX = $image.offset().left;

                            startX = e.clientX || e.originalEvent.clientX;
                            startY = e.clientY || e.originalEvent.clientY;
                            if (isTouchEnabled) {
                                touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
                                startX = touch.pageX;
                                startY = touch.pageY;
                            }
                            isDragActive = true;
                        }
                    });



                $image
                    .off(isTouchEnabled ? 'touchmove' : 'mousemove pointermove MSPointerMove')
                    .on(isTouchEnabled ? 'touchmove' : 'mousemove pointermove MSPointerMove', function (e) {
                        if (gallery.data('fotorama').fullScreen && isDragActive && isDraggable) {
                            var top,
                                left,
                                startOffset = $image.offset(),
                                clientX = e.clientX || e.originalEvent.clientX,
                                clientY = e.clientY || e.originalEvent.clientY;


                            e.preventDefault();

                            if (isTouchEnabled && !isZoomActive) {
                                touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
                                clientX = touch.pageX;
                                clientY = touch.pageY;
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
                            return false;
                        }
                    });

                $image
                    .off(isTouchEnabled ? 'touchend' : 'mouseup pointerup MSPointerUp')
                    .on(isTouchEnabled ? 'touchend' : 'mouseup pointerup MSPointerUp', function (e) {
                        if (gallery.data('fotorama').fullScreen && isDragActive && isDraggable) {
                            isDragActive = false;
                            $image.css('cursor', 'pointer');

                            return false;
                        }
                    });
            });
        }

        function onScroll() {

            if (curThumb !== null) {
                setThumbData(curThumb, currentOpts);
            }
        }

        if ($('.fotorama-item').data('fotorama').fullScreen) {
            $('.fotorama__stage__frame .fotorama__img--full').each(function () {
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

                $('.fotorama__stage__frame .fotorama__img--full').each(function () {
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

                toggleZoomButtons($('[data-gallery-role="stage-shaft"] [data-active="true"] .fotorama__img--full'));
            }


            _init($box, gOptions);

        });

        function resetVars($image) {
            zoomWidthStep = 0;
            zoomHeightStep = 0;
            isDraggable = false;
            $image.css({
                top: 0,
                left: 0,
                right: 0,
                bottom: 0,
                cursor: ''
            });
        }

        function checkFullscreenImagePosition(widthStep, heightStep) {
            var $preview, $image, $imageContainer, gallery, top, left;

            if ($('[data-gallery-role="gallery"]').data('fotorama').fullScreen) {

                $preview = $('[data-gallery-role="stage-shaft"] [data-active="true"] img');
                $image = $('[data-gallery-role="stage-shaft"] [data-active="true"] .fotorama__img--full');
                $imageContainer = $preview.parent();
                gallery = $('[data-gallery-role="gallery"]');
                top = $image.position().top;
                left = $image.position().left;

                if ($imageContainer.width() < $image.width() - widthStep && $imageContainer.width() > $image.width()) {
                    left = ($imageContainer.width() - $image.width()) / 2;
                }

                if ($imageContainer.height() < $image.height() - heightStep && $imageContainer.height() > $image.height()) {
                    top = ($imageContainer.height() - $image.height()) / 2;
                }

                if ($image.height() - heightStep > $imageContainer.height()) {
                    if (Math.abs(top + heightStep / 2) > $image.height() - $imageContainer.height() - heightStep) {
                        top = $imageContainer.height() - $image.height() + heightStep;
                    } else if (top + heightStep / 2 >= 0) {
                        top = 0;
                    } else {
                        top += heightStep / 2;
                    }
                    $image.css({
                        top: top,
                        bottom: 'auto'
                    });
                } else {
                    $image.css({
                        top: 0,
                        bottom: 0
                    });
                }

                if ($image.width() - widthStep > $imageContainer.width()) {
                    if (Math.abs(left + widthStep / 2) > $image.width() - $imageContainer.width() - widthStep) {
                        left = $imageContainer.width() - $image.width() + widthStep;
                    } else if (left + widthStep / 2 >= 0) {
                        left = 0;
                    } else {
                        left += widthStep / 2;
                    }
                    $image.css({
                        left: left,
                        right: 'auto'
                    });
                } else {
                    $image.css({
                        left: 0,
                        right: 0
                    });
                }
            }
        }

        function zoomIn(e) {
            if (zoomShown) {
                var $image = $('[data-gallery-role="stage-shaft"] [data-active="true"] .fotorama__img--full'),
                    imgOriginalSize = $image.length ? getImageSize($image[0].src) : '',
                    widthResult,
                    heightResult;

                if (!zoomWidthStep) {
                    zoomWidthStep = Math.ceil((imgOriginalSize.rw - $image.width())/parseFloat(options.fullscreenzoom));
                    zoomHeightStep = Math.ceil((imgOriginalSize.rh - $image.height())/parseFloat(options.fullscreenzoom));
                }
                widthResult = $image.width() + zoomWidthStep;
                heightResult = $image.height() + zoomHeightStep;

                if (widthResult >= imgOriginalSize.rw) {
                    widthResult = imgOriginalSize.rw;
                }
                if (heightResult >= imgOriginalSize.rh) {
                    heightResult = imgOriginalSize.rh;
                }

                if ( zoomShown ) {
                    isDraggable = true;
                }

                if ($image.width() >= $image.height() && $image.width() !== imgOriginalSize.rw) {
                    checkFullscreenImagePosition(-zoomWidthStep, -zoomHeightStep);
                    $image.css({
                        width: widthResult,
                        height: 'auto'
                    });
                } else if ($image.width() < $image.height() && $image.height() !== imgOriginalSize.rh) {
                    checkFullscreenImagePosition(-zoomWidthStep, -zoomHeightStep);
                    $image.css({
                        width: 'auto',
                        height: heightResult
                    });
                }
            }

            return false;
        }

        function zoomOut(e) {
            if (zoomShown) {
                var $image = $('[data-gallery-role="stage-shaft"] [data-active="true"] .fotorama__img--full'),
                    setedResult = $image.width() - zoomWidthStep,
                    widthCheck = $image.width() - zoomWidthStep <= $image.parent().width(),
                    heightCheck = $image.height() - zoomHeightStep <= $image.parent().height();

                e.preventDefault();

                if (widthCheck && heightCheck) {
                    if ($image.width() >= $image.height()) {
                        $image.trigger('fotorama:load');

                        return false;
                    } else if ($image.width() < $image.height()) {
                        $image.trigger('fotorama:load');

                        return false;
                    }
                }
                checkFullscreenImagePosition(zoomWidthStep, zoomHeightStep);
                $image.css({'width': setedResult, height: 'auto'});
            }

            return false;
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
                .off('mouseup')
                .on('mouseup', zoomIn);
            $('.fotorama__zoom-out')
                .off('mouseup')
                .on('mouseup', zoomOut);
            $('.fotorama__zoom-in')
                .off('touchend')
                .on('touchend', zoomIn);
            $('.fotorama__zoom-out')
                .off('touchend')
                .on('touchend', zoomOut);
        }

        $(document).on('mousemove', onMousemove);
        _init($box, gOptions);
        setEventOnce();
        magnifierFullscreen();

        if (!onWheelCallback) {
            onWheelCallback = function onWheel(e) {
                var delta;

                if ($('[data-gallery-role="gallery"]').data('fotorama').fullScreen) {
                    e = e || window.event;
                    delta = e.deltaY || e.detail || e.wheelDelta;

                    if (delta > 0 || (e.scale && e.scale < 1.0)) {
                        zoomOut(e);
                    } else if (delta < 0 || (e.scale && e.scale > 1.0)) {
                        zoomIn(e);
                    }

                    e.preventDefault ? e.preventDefault() : (e.returnValue = false);
                }
            };
        }
        $('.fotorama-item').on('fotorama:load', function () {
            document.querySelector('.fotorama__stage').removeEventListener("gesturechange", onWheelCallback);
            document.querySelector('.fotorama__stage').addEventListener("gesturechange", onWheelCallback);
            document.querySelector('.fotorama__stage').addEventListener("gesturestart", function () {
                isZoomActive = true;
            });
            document.querySelector('.fotorama__stage').addEventListener("gestureend", function () {
                isZoomActive = false;
            });

            if (document.querySelector('.fotorama__stage').addEventListener) {
                if ('onwheel' in document) {
                    // IE9+, FF17+, Ch31+
                    document.querySelector('.fotorama__stage').removeEventListener("wheel", onWheelCallback);
                    document.querySelector('.fotorama__stage').addEventListener("wheel", onWheelCallback);
                } else if ('onmousewheel' in document) {
                    document.querySelector('.fotorama__stage').removeEventListener("mousewheel", onWheelCallback);
                    document.querySelector('.fotorama__stage').addEventListener("mousewheel", onWheelCallback);
                } else {
                    // Firefox < 17
                    document.querySelector('.fotorama__stage').removeEventListener("MozMousePixelScroll", onWheelCallback);
                    document.querySelector('.fotorama__stage').addEventListener("MozMousePixelScroll", onWheelCallback);
                }
            } else { // IE8-
                document.querySelector('.fotorama__stage').detachEvent("onmousewheel", onWheelCallback);
                document.querySelector('.fotorama__stage').attachEvent("onmousewheel", onWheelCallback);
            }
        });

    }
}(jQuery));
