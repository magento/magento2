/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

(function ($) {
    $.fn.magnify = function (options) {
        'use strict';

        var magnify = new Magnify($(this), options);

        /* events must be tracked here */

        /**
         * Return that from _init function
         *
         */
        return magnify;
    };

    function Magnify(element, options) {
        var customUserOptions = options || {},
            $box = $(element),
            $thumb,
            that = this,
            largeWrapper = options.largeWrapper || '.magnifier-preview',
            $magnifierPreview = $(largeWrapper);

        curThumb = null,
        magnifierOptions = {
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
            zoomable: customUserOptions.zoomable !== undefined ?
                customUserOptions.zoomable
                : false,
            onthumbenter: customUserOptions.onthumbenter !== undefined ?
                customUserOptions.onthumbenter
                : null,
            onthumbmove: customUserOptions.onthumbmove !== undefined ?
                customUserOptions.onthumbmove
                : null,
            onthumbleave: customUserOptions.onthumbleave !== undefined ?
                customUserOptions.onthumbleave
                : null,
            onzoom: customUserOptions.onzoom !== undefined ?
                customUserOptions.onzoom
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
        lensbg = customUserOptions.bg !== undefined ?
            customUserOptions.lensbg
            : true,
        gZoom = customUserOptions.zoom !== undefined ?
            customUserOptions.zoom
            : magnifierOptions.zoom,
        gZoomMin = customUserOptions.zoomMin !== undefined ?
            customUserOptions.zoomMin
            : magnifierOptions.zoomMin,
        gZoomMax = customUserOptions.zoomMax !== undefined ?
            customUserOptions.zoomMax
            : magnifierOptions.zoomMax,
        gMode = customUserOptions.mode || magnifierOptions.mode,
        gEventType = customUserOptions.eventType || magnifierOptions.eventType,
        data = {},
        inBounds = false,
        isOverThumb = false,
        rate = 1,
        paddingX = 0,
        paddingY = 0,
        enabled = true,
        showWrapper = true;

        var MagnifyCls = {
            magnifyHidden: 'magnify-hidden',
            magnifyOpaque: 'magnify-opaque',
            magnifyFull: 'magnify-fullimage'
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

        function _toBoolean(str) {
            if (typeof str === 'string') {
                if (str === 'true') {
                    return true;
                } else if (str === 'false' || '') {
                    return false;
                }
                console.warn('Wrong type: can\'t be transformed to Boolean');

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

        function updateLensOnLoad(idSelectorMainImg, thumb, largeImgInMagnifyLens, largeWrapper) {
            var magnifyLensElement= $box.find('.magnify-lens'),
                textWrapper;

            if (data[idSelectorMainImg].status === 1) {
                textWrapper = $('<div class="magnifier-loader-text"></div>');
                magnifyLensElement.className = 'magnifier-loader magnify-hidden';
                textWrapper.html('Loading...');
                magnifyLensElement.html('').append(textWrapper);
            } else if (data[idSelectorMainImg].status === 2) {
                magnifyLensElement.addClass(MagnifyCls.magnifyHidden);
                magnifyLensElement.html('');

                largeImgInMagnifyLens.id = idSelectorMainImg + '-large';
                largeImgInMagnifyLens.style.width = data[idSelectorMainImg].largeImgInMagnifyLensWidth + 'px';
                largeImgInMagnifyLens.style.height = data[idSelectorMainImg].largeImgInMagnifyLensHeight + 'px';
                largeImgInMagnifyLens.className = 'magnifier-large magnify-hidden';

                if (data[idSelectorMainImg].mode === 'inside') {
                    magnifyLensElement.append(largeImgInMagnifyLens);
                } else {
                    largeWrapper.html('').append(largeImgInMagnifyLens);
                }
            }

            data[idSelectorMainImg].lensH = data[idSelectorMainImg].lensH > $thumb.height() ? $thumb.height() : data[idSelectorMainImg].lensH;

            if (Math.round(data[idSelectorMainImg].lensW) === 0) {
                magnifyLensElement.css('display', 'none');
            } else {
                magnifyLensElement.css({
                    width: Math.round(data[idSelectorMainImg].lensW) + 'px',
                    height: Math.round(data[idSelectorMainImg].lensH) + 'px',
                    display: ''
                });
            }
        }

        function getMousePos() {
            var xPos = pos.x - magnifierOptions.x,
                yPos = pos.y - magnifierOptions.y,
                t,
                l;

            inBounds =  xPos < 0 || yPos < 0 || xPos > magnifierOptions.w || yPos > magnifierOptions.h  ? false : true;

            l = xPos - magnifierOptions.lensW / 2;
            t = yPos - magnifierOptions.lensH / 2;

            if (xPos < magnifierOptions.lensW / 2) {
                l = 0;
            }

            if (yPos < magnifierOptions.lensH / 2) {
                t = 0;
            }

            if (xPos - magnifierOptions.w + Math.ceil(magnifierOptions.lensW / 2) > 0) {
                l = magnifierOptions.w - Math.ceil(magnifierOptions.lensW + 2);
            }

            if (yPos - magnifierOptions.h + Math.ceil(magnifierOptions.lensH / 2) > 0) {
                t = magnifierOptions.h - Math.ceil(magnifierOptions.lensH);
            }

            pos.l = l;
            pos.t = t;

            magnifierOptions.lensBgX = pos.l;
            magnifierOptions.lensBgY = pos.t;

            if (magnifierOptions.mode === 'inside') {
                magnifierOptions.largeL = Math.round(xPos * (magnifierOptions.zoom - magnifierOptions.lensW / magnifierOptions.w));
                magnifierOptions.largeT = Math.round(yPos * (magnifierOptions.zoom - magnifierOptions.lensH / magnifierOptions.h));
            } else {
                magnifierOptions.largeL = Math.round(magnifierOptions.lensBgX * magnifierOptions.zoom * (magnifierOptions.largeWrapperW / magnifierOptions.w));
                magnifierOptions.largeT = Math.round(magnifierOptions.lensBgY * magnifierOptions.zoom * (magnifierOptions.largeWrapperH / magnifierOptions.h));
            }
        }

        function onThumbEnter() {
            if (_toBoolean(enabled)) {
                magnifierOptions = data[curIdx];
                curLens = $box.find('.magnify-lens');

                if (magnifierOptions.status === 2) {
                    curLens.removeClass(MagnifyCls.magnifyOpaque);
                    curLarge = $('#' + curIdx + '-large');
                    curLarge.removeClass(MagnifyCls.magnifyHidden);
                } else if (magnifierOptions.status === 1) {
                    curLens.className = 'magnifier-loader';
                }
            }
        }

        function onThumbLeave() {
            if (magnifierOptions.status > 0) {
                var handler = magnifierOptions.onthumbleave;

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
                if (status !== magnifierOptions.status) {
                    onThumbEnter();
                }

                if (magnifierOptions.status > 0) {
                    curThumb.className = magnifierOptions.thumbCssClass + ' magnify-opaque';

                    if (magnifierOptions.status === 1) {
                        curLens.className = 'magnifier-loader';
                    } else if (magnifierOptions.status === 2) {
                        curLens.removeClass(MagnifyCls.magnifyHidden);
                        curLarge.removeClass(MagnifyCls.magnifyHidden);
                        curLarge.css({
                            left: '-' + magnifierOptions.largeL + 'px',
                            top: '-' + magnifierOptions.largeT + 'px'
                        });
                    }

                    var borderOffset = 2; // Offset for magnify-lens border
                    pos.t = pos.t <= 0 ? 0 : pos.t - borderOffset;

                    curLens.css({
                        left: pos.l + paddingX + 'px',
                        top: pos.t + paddingY + 'px'
                    });

                    if (lensbg) {
                        curLens.css({
                            'background-color': 'rgba(f,f,f,.5)'
                        });
                    } else {
                        curLens.get(0).style.backgroundPosition = '-' +
                        magnifierOptions.lensBgX + 'px -' +
                        magnifierOptions.lensBgY + 'px';
                    }
                    var handler = magnifierOptions.onthumbmove;

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

                status = magnifierOptions.status;
            }
        }

        function setThumbData(mainImage, mainImageData) {
            var thumbBounds = mainImage.getBoundingClientRect(),
                w = 0,
                h = 0;

            mainImageData.x = Math.round(thumbBounds.left);
            mainImageData.y = Math.round(thumbBounds.top);
            mainImageData.w = Math.round(thumbBounds.right - mainImageData.x);
            mainImageData.h = Math.round(thumbBounds.bottom - mainImageData.y);

            if (mainImageData.mode === 'inside') {
                w = mainImageData.w;
                h = mainImageData.h;
            } else {
                w = mainImageData.largeWrapperW;
                h = mainImageData.largeWrapperH;
            }

            mainImageData.largeImgInMagnifyLensWidth = Math.round(mainImageData.zoom * w);
            mainImageData.largeImgInMagnifyLensHeight = Math.round(mainImageData.zoom * h);

            mainImageData.lensW = Math.round(mainImageData.w / mainImageData.zoom);
            mainImageData.lensH = Math.round(mainImageData.h / mainImageData.zoom);
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

                if (_toBoolean(enabled)) {

                    $magnifierPreview.show().css('display', '');
                    $magnifierPreview.addClass(MagnifyCls.magnifyHidden);
                    set(opts);
                } else {
                    $magnifierPreview.empty().hide();
                }
            }

            return that;
        }

        function hoverEvents(thumb) {
            $(thumb).on('mouseover', function (e) {

                if (showWrapper) {

                    if (magnifierOptions.status !== 0) {
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
                        if (magnifierOptions.status !== 0) {
                            onThumbLeave();
                        }
                        handleEvents(e);
                        isOverThumb = true;
                    }
                }
            });
        }

        function bindEvents(eType, thumb) {
            var eventFlag = 'hasBoundEvent_' + eType;
            if (thumb[eventFlag]) {
                // Events are already bound, no need to bind in duplicate
                return;
            }
            thumb[eventFlag] = true;

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

            setThumbData(curThumb, magnifierOptions);

            pos.x = e.clientX;
            pos.y = e.clientY;

            getMousePos();
            move();

            var handler = magnifierOptions.onthumbenter;

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
                onthumbenter = options.onthumbenter !== undefined ?
                    options.onthumbenter
                    : magnifierOptions.onthumbenter,
                onthumbleave = options.onthumbleave !== undefined ?
                    options.onthumbleave
                    : magnifierOptions.onthumbleave,
                onthumbmove = options.onthumbmove !== undefined ?
                    options.onthumbmove
                    : magnifierOptions.onthumbmove;

            largeUrl = $thumb.data('original') || customUserOptions.full || $thumb.attr('src');

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
                    largeWrapper[0].style.top = top.replace('%px', '%');
                }
            }

            if (options.left) {
                if (typeof options.left == 'function') {
                    var left = options.left() + 'px';
                } else {
                    var left = options.left + 'px';
                }

                if (largeWrapper.length) {
                    largeWrapper[0].style.left = left.replace('%px', '%');
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

            paddingX = ($thumb.parent().width() - $thumb.width()) / 2;
            paddingY = ($thumb.parent().height() - $thumb.height()) / 2;

            showWrapper = false;
            $(thumbObj).on('load', function () {
                data[idx].status = 1;

                $(largeObj).on('load', function () {

                    if (largeObj.width > largeWrapper.width() || largeObj.height > largeWrapper.height()) {
                        showWrapper = true;
                        bindEvents(eventType, thumb);
                        data[idx].status = 2;
                        if (largeObj.width > largeObj.height) {
                            data[idx].zoom = largeObj.width / largeWrapper.width();
                        } else {
                            data[idx].zoom = largeObj.height / largeWrapper.height();
                        }
                        setThumbData(thumb, data[idx]);
                        updateLensOnLoad(idx, thumb, largeObj, largeWrapper);
                    }
                });

                largeObj.src = data[idx].largeUrl;
            });

            thumbObj.src = thumb.src;
        }

        /**
         * Hide magnifier when mouse exceeds image bounds.
         */
        function onMouseLeave() {
            onThumbLeave();
            isOverThumb = false;
            $magnifierPreview.addClass(MagnifyCls.magnifyHidden);
        }

        function onMousemove(e) {
            pos.x = e.clientX;
            pos.y = e.clientY;

            getMousePos();

            if (gEventType === 'hover') {
                isOverThumb = inBounds;
            }

            if (inBounds && isOverThumb) {
                if (gMode === 'outside') {
                    $magnifierPreview.removeClass(MagnifyCls.magnifyHidden);
                }
                move();
            }
        }

        function onScroll() {
            if (curThumb !== null) {
                setThumbData(curThumb, magnifierOptions);
            }
        }

        $(window).on('scroll', onScroll);
        $(window).on('resize', function () {
            _init($box, customUserOptions);
        });

        $box.on('mousemove', onMousemove);
        $box.on('mouseleave', onMouseLeave);

        _init($box, customUserOptions);
    }
}(jQuery));
