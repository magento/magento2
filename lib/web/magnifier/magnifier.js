(function ($) {
    $.fn.magnify = function (options) {
        'use strict';

        var magnify = new Magnify($(this), options);

    };

    function Magnify($container, options) {
        var gOptions = options || {},
            $box = $container,
            $thumb,
            $largeWrapper = $(options.largeWrapper);
            curThumb = null,
            curData = {
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
                eventType: 'hover',
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
                : curData.zoom,
            gZoomMin = (gOptions.zoomMin !== undefined)
                ? gOptions.zoomMin
                : curData.zoomMin,
            gZoomMax = (gOptions.zoomMax !== undefined)
                ? gOptions.zoomMax
                : curData.zoomMax,
            gMode = gOptions.mode || curData.mode,
            gEventType = gOptions.eventType || curData.eventType,
            data = {},
            inBounds = false,
            isOverThumb = false,
            rate = 1,
            paddingX = 0,
            paddingY = 0,
            enabled = true;

        var ClassName = {
            magnifyHidden: "hidden",
            magnifyOpaque: "opaque"
        };

        function createLens(thumb, idx) {
            if ($(thumb).siblings('.magnify-lens').length) {
                return false;
            }
            var lens = $('<div class="magnify-lens" class="magnifier-loader"></div>');
            $(thumb).parent().append(lens);
        }

        function updateLensOnZoom() {
            curLens.style.left = pos.l + 'px';
            curLens.style.top = pos.t + 'px';
            curLens.style.width = curData.lensW + 'px';
            curLens.style.height = curData.lensH + 'px';
            curLens.style.backgroundPosition = '-' + curData.lensBgX + 'px -' +
            curData.lensBgY + 'px';
            curLarge.style.left = '-' + curData.largeL + 'px';
            curLarge.style.top = '-' + curData.largeT + 'px';
            curLarge.style.width = Math.round(curData.largeW) + 'px';
            curLarge.style.height = Math.round(curData.largeH) + 'px';
        }

        function updateLensOnLoad(idx, thumb, large, largeWrapper) {
            var lens = $box.find('.magnify-lens'),
                textWrapper;

            if (data[idx].status === 1) {
                textWrapper = $('<div class="magnifier-loader-text"></div>');
                lens.className = 'magnifier-loader hidden';
                textWrapper.html("Loading...");
                lens.html("").append(textWrapper);
            } else if (data[idx].status === 2) {
                lens.addClass(ClassName.magnifyHidden);
                lens.html("");
                large.id = idx + '-large';
                large.style.width = Math.round(data[idx].largeW * rate) + 'px';
                large.style.height = data[idx].largeH + 'px';
                large.className = 'magnifier-large hidden';

                if (data[idx].mode === 'inside') {
                    lens.append(large);
                } else {
                    largeWrapper.html("").append(large);
                }
            }

            data[idx].lensH = data[idx].lensH > $thumb.height() ? $thumb.height() : data[idx].lensH;
            lens.css({
                width: data[idx].lensW + 'px',
                height: data[idx].lensH + 'px'
            });
        }

        function getMousePos() {
            var xPos = pos.x - curData.x,
                yPos = pos.y - curData.y,
                t,
                l;

            inBounds = ( xPos < 0 || yPos < 0 || xPos > curData.w || yPos > curData.h ) ? false : true;

            l = xPos - Math.ceil(curData.lensW / 2);
            t = yPos - Math.ceil(curData.lensH / 2);

            if (curData.mode !== 'inside') {
                if (xPos < curData.lensW / 2) {
                    l = 0;
                }

                if (yPos < curData.lensH / 2) {
                    t = 0;
                }

                if (xPos - curData.w + Math.ceil(curData.lensW / 2) > 0) {
                    l = curData.w - Math.ceil(curData.lensW + 2);
                }

                if (yPos - curData.h + Math.ceil(curData.lensH / 2) > 0) {
                    t = curData.h - Math.ceil(curData.lensH + 2);
                }

                pos.l = Math.ceil(l);
                pos.t = Math.ceil(t);

                curData.lensBgX = pos.l + 1;
                curData.lensBgY = pos.t + 1;

                if (curData.mode === 'inside') {
                    curData.largeL = Math.round(xPos * (curData.zoom - (curData.lensW / curData.w)));
                    curData.largeT = Math.round(yPos * (curData.zoom - (curData.lensH / curData.h)));
                } else {
                    curData.largeL = Math.round(curData.lensBgX * curData.zoom * (curData.largeWrapperW / curData.w) * rate);
                    curData.largeT = Math.round(curData.lensBgY * curData.zoom * (curData.largeWrapperH / curData.h));
                }
            }
        }

        function zoomInOut(e) {
            var delta = (e.wheelDelta > 0 || e.detail < 0) ? 0.1 : -0.1,
                handler = curData.onzoom,
                multiplier = 1,
                w = 0,
                h = 0;

            if (e.preventDefault) {
                e.preventDefault();
            }

            e.returnValue = false;

            curData.zoom = Math.round((curData.zoom + delta) * 10) / 10;

            if (curData.zoom >= curData.zoomMax) {
                curData.zoom = curData.zoomMax;
            } else if (curData.zoom >= curData.zoomMin) {
                curData.lensW = Math.round(curData.w / curData.zoom);
                curData.lensH = Math.round(curData.h / curData.zoom);

                if (curData.mode === 'inside') {
                    w = curData.w;
                    h = curData.h;
                } else {
                    w = curData.largeWrapperW;
                    h = curData.largeWrapperH;
                    multiplier = curData.largeWrapperW / curData.w;
                }

                curData.largeW = Math.round(curData.zoom * w);
                curData.largeH = Math.round(curData.zoom * h);

                getMousePos();
                updateLensOnZoom();

                if (handler !== null) {
                    handler({
                        thumb: curThumb,
                        lens: curLens,
                        large: curLarge,
                        x: pos.x,
                        y: pos.y,
                        zoom: Math.round(curData.zoom * multiplier * 10) / 10,
                        w: curData.lensW,
                        h: curData.lensH
                    });
                }
            } else {
                curData.zoom = curData.zoomMin;
            }
        }

        function onThumbEnter() {
            if (enabled !== false) {
                curData = data[curIdx];
                curLens = $box.find('.magnify-lens');

                if (curData.status === 2) {
                    curLens.removeClass(ClassName.magnifyOpaque);

                    if (curData.zoomAttached === false) {
                        if (curData.zoomable !== undefined && curData.zoomable === true) {
                            $(curLens).on('mousewheel', zoomInOut);
                            if (window.addEventListener) {
                                curLens.get(0).addEventListener('DOMMouseScroll', function (e) {
                                    zoomInOut(e);
                                });
                            }
                        }

                        curData.zoomAttached = true;
                    }

                    curLarge = $('#' + curIdx + '-large');
                    curLarge.removeClass(ClassName.magnifyHidden);
                } else if (curData.status === 1) {
                    curLens.className = 'magnifier-loader';
                }
            }
        }

        function onThumbLeave() {
            if (curData.status > 0) {
                var handler = curData.onthumbleave;

                if (handler !== null) {
                    handler({
                        thumb: curThumb,
                        lens: curLens,
                        large: curLarge,
                        x: pos.x,
                        y: pos.y
                    });
                }
                if (!curLens.hasClass(ClassName.magnifyHidden)) {
                    curLens.addClass(ClassName.magnifyHidden);
                    //$curThumb.removeClass(ClassName.magnifyOpaque);
                    if (curLarge !== null) {
                        curLarge.addClass(ClassName.magnifyHidden);
                    }
                }
            }
        }

        function move() {
            if (enabled) {
                if (status !== curData.status) {
                    onThumbEnter();
                }

                if (curData.status > 0) {
                    curThumb.className = curData.thumbCssClass + ' opaque';

                    if (curData.status === 1) {
                        curLens.className = 'magnifier-loader';
                    } else if (curData.status === 2) {
                        curLens.removeClass(ClassName.magnifyHidden);
                        curLarge.removeClass(ClassName.magnifyHidden);
                        curLarge.css({
                            left: '-' + curData.largeL + 'px',
                            top: '-' + curData.largeT + 'px'
                        });
                    }

                    pos.t = pos.t <= 0 ? 0 : pos.t;
                    pos.t = pos.t > 0 ? pos.t + 1 : pos.t;
                    pos.l = pos.l <= 0 ? 0 : pos.l;
                    pos.l = pos.l > 0 ? pos.l + 1 : pos.l;
                    curLens.css({
                        left: pos.l + paddingX + 'px',
                        top: pos.t + paddingY + 'px'
                    });

                    if (lensbg) {
                        curLens.css({
                            "background-color": "rgba(f,f,f,.5)"
                        });
                    } else {
                        curLens.get(0).style.backgroundPosition = '-' +
                        curData.lensBgX + 'px -' +
                        curData.lensBgY + 'px';
                    }
                    var handler = curData.onthumbmove;

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

                status = curData.status;
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

            thumbData.lensW = Math.round(thumbData.w / thumbData.zoom) / rate;
            thumbData.lensH = Math.round(thumbData.h / thumbData.zoom);
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

                if (enabled === true) {
                    $largeWrapper.show();
                    $largeWrapper.addClass(ClassName.magnifyHidden);
                    set(opts);
                } else {
                    $largeWrapper.empty().hide();
                }
            }
            return {}
        }

        function hoverEvents(thumb) {
            $(thumb).on('mouseover', function (e) {
                if (curData.status !== 0) {
                    onThumbLeave();
                }
                handleEvents(e);
                isOverThumb = true;
            }).trigger('mouseover');
        }

        function clickEvents(thumb) {
            $(thumb).on('click', function (e) {
                if (!isOverThumb) {
                    if (curData.status !== 0) {
                        onThumbLeave();
                    }
                    handleEvents(e);
                    isOverThumb = true;
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

            setThumbData(curThumb, curData);

            pos.x = e.clientX;
            pos.y = e.clientY;

            getMousePos();
            move();

            var handler = curData.onthumbenter;

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
                largeAttrName = options.largeSrc || 'magnify-large',
                thumb = $thumb.get(0),
                idx = thumb.id,
                zoomable,
                largeUrl,
                largeWrapper = $(options.largeWrapper),
                zoom = options.zoom || thumb.getAttribute('data-zoom') || gZoom,
                zoomMin = options.zoomMin || thumb.getAttribute('data-zoom-min') || gZoomMin,
                zoomMax = options.zoomMax || thumb.getAttribute('data-zoom-max') || gZoomMax,
                mode = options.mode || thumb.getAttribute('data-mode') || gMode,
                eventType = options.eventType || thumb.getAttribute('data-eventType') || gEventType,
                onthumbenter = (options.onthumbenter !== undefined)
                    ? options.onthumbenter
                    : curData.onthumbenter,
                onthumbleave = (options.onthumbleave !== undefined)
                    ? options.onthumbleave
                    : curData.onthumbleave,
                onthumbmove = (options.onthumbmove !== undefined)
                    ? options.onthumbmove
                    : curData.onthumbmove,
                onzoom = (options.onzoom !== undefined)
                    ? options.onzoom
                    : curData.onzoom;

            largeUrl = $thumb.data(largeAttrName) || $thumb.attr('src');

            if (options.zoomable !== undefined) {
                zoomable = options.zoomable;
            } else if (thumb.getAttribute('data-zoomable') !== null) {
                zoomable = (thumb.getAttribute('data-zoomable') === 'true');
            } else if (curData.zoomable !== undefined) {
                zoomable = curData.zoomable;
            }

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

            data[idx] = {
                zoom: zoom,
                zoomMin: zoomMin,
                zoomMax: zoomMax,
                mode: mode,
                eventType: eventType,
                zoomable: zoomable,
                thumbCssClass: thumb.className,
                zoomAttached: false,
                status: 0,
                largeUrl: largeUrl,
                largeWrapperId: mode === 'outside' ? largeWrapper.attr('id') : null,
                largeWrapperW: mode === 'outside' ? largeWrapper.width() : null,
                largeWrapperH: mode === 'outside' ? largeWrapper.height() : null,
                onzoom: onzoom,
                onthumbenter: onthumbenter,
                onthumbleave: onthumbleave,
                onthumbmove: onthumbmove
            };

            rate = ($thumb.width() / $thumb.height()) / (data[idx].largeWrapperW / data[idx].largeWrapperH);
            paddingX = Math.ceil(($thumb.parent().width() - $thumb.width()) / 2);
            paddingY = Math.ceil(($thumb.parent().height() - $thumb.height()) / 2);

            bindEvents(eventType, thumb);
            $(thumbObj).on('load', function () {
                data[idx].status = 1;

                $(largeObj).on('load', function () {
                    data[idx].status = 2;
                    data[idx].zoom = largeObj.height / largeWrapper.height();
                    setThumbData(thumb, data[idx]);
                    updateLensOnLoad(idx, thumb, largeObj, largeWrapper);
                });

                largeObj.src = data[idx].largeUrl;
            });

            thumbObj.src = thumb.src;
        }

        function onScroll() {

            if (curThumb !== null) {
                setThumbData(curThumb, curData);
            }
        }

        function onMousemove(e) {

            pos.x = e.clientX;
            pos.y = e.clientY;

            getMousePos();

            if (inBounds && isOverThumb) {
                $largeWrapper.removeClass(ClassName.magnifyHidden);
                move();
            } else {
                onThumbLeave();
                isOverThumb = false;
                $largeWrapper.addClass(ClassName.magnifyHidden);
            }
        }

        $(window).on('scroll', onScroll);
        $(window).resize(function() {
            _init($box, gOptions);
        });
        $(document).on('mousemove', onMousemove);
        _init($box, gOptions);
    }
}(jQuery));
