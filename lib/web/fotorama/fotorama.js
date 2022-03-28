/*!
 * Fotorama 4.6.4 | http://fotorama.io/license/
 */
fotoramaVersion = '4.6.4';
(function (window, document, location, $, undefined) {
    "use strict";
    var _fotoramaClass = 'fotorama',
        _fullscreenClass = 'fotorama__fullscreen',

        wrapClass = _fotoramaClass + '__wrap',
        wrapCss2Class = wrapClass + '--css2',
        wrapCss3Class = wrapClass + '--css3',
        wrapVideoClass = wrapClass + '--video',
        wrapFadeClass = wrapClass + '--fade',
        wrapSlideClass = wrapClass + '--slide',
        wrapNoControlsClass = wrapClass + '--no-controls',
        wrapNoShadowsClass = wrapClass + '--no-shadows',
        wrapPanYClass = wrapClass + '--pan-y',
        wrapRtlClass = wrapClass + '--rtl',
        wrapOnlyActiveClass = wrapClass + '--only-active',
        wrapNoCaptionsClass = wrapClass + '--no-captions',
        wrapToggleArrowsClass = wrapClass + '--toggle-arrows',

        stageClass = _fotoramaClass + '__stage',
        stageFrameClass = stageClass + '__frame',
        stageFrameVideoClass = stageFrameClass + '--video',
        stageShaftClass = stageClass + '__shaft',

        grabClass = _fotoramaClass + '__grab',
        pointerClass = _fotoramaClass + '__pointer',

        arrClass = _fotoramaClass + '__arr',
        arrDisabledClass = arrClass + '--disabled',
        arrPrevClass = arrClass + '--prev',
        arrNextClass = arrClass + '--next',

        navClass = _fotoramaClass + '__nav',
        navWrapClass = navClass + '-wrap',
        navShaftClass = navClass + '__shaft',
        navShaftVerticalClass = navWrapClass + '--vertical',
        navShaftListClass = navWrapClass + '--list',
        navShafthorizontalClass = navWrapClass + '--horizontal',
        navDotsClass = navClass + '--dots',
        navThumbsClass = navClass + '--thumbs',
        navFrameClass = navClass + '__frame',

        fadeClass = _fotoramaClass + '__fade',
        fadeFrontClass = fadeClass + '-front',
        fadeRearClass = fadeClass + '-rear',

        shadowClass = _fotoramaClass + '__shadow',
        shadowsClass = shadowClass + 's',
        shadowsLeftClass = shadowsClass + '--left',
        shadowsRightClass = shadowsClass + '--right',
        shadowsTopClass = shadowsClass + '--top',
        shadowsBottomClass = shadowsClass + '--bottom',

        activeClass = _fotoramaClass + '__active',
        selectClass = _fotoramaClass + '__select',

        hiddenClass = _fotoramaClass + '--hidden',

        fullscreenClass = _fotoramaClass + '--fullscreen',
        fullscreenIconClass = _fotoramaClass + '__fullscreen-icon',

        errorClass = _fotoramaClass + '__error',
        loadingClass = _fotoramaClass + '__loading',
        loadedClass = _fotoramaClass + '__loaded',
        loadedFullClass = loadedClass + '--full',
        loadedImgClass = loadedClass + '--img',

        grabbingClass = _fotoramaClass + '__grabbing',

        imgClass = _fotoramaClass + '__img',
        imgFullClass = imgClass + '--full',

        thumbClass = _fotoramaClass + '__thumb',
        thumbArrLeft = thumbClass + '__arr--left',
        thumbArrRight = thumbClass + '__arr--right',
        thumbBorderClass = thumbClass + '-border',

        htmlClass = _fotoramaClass + '__html',

        videoContainerClass = _fotoramaClass + '-video-container',
        videoClass = _fotoramaClass + '__video',
        videoPlayClass = videoClass + '-play',
        videoCloseClass = videoClass + '-close',


        horizontalImageClass = _fotoramaClass + '_horizontal_ratio',
        verticalImageClass = _fotoramaClass + '_vertical_ratio',
        fotoramaSpinnerClass = _fotoramaClass + '__spinner',
        spinnerShowClass = fotoramaSpinnerClass + '--show';
    var JQUERY_VERSION = $ && $.fn.jquery.split('.');

    if (!JQUERY_VERSION
        || JQUERY_VERSION[0] < 1
        || (JQUERY_VERSION[0] == 1 && JQUERY_VERSION[1] < 8)) {
        throw 'Fotorama requires jQuery 1.8 or later and will not run without it.';
    }

    var _ = {};
    /* Modernizr 2.8.3 (Custom Build) | MIT & BSD
     * Build: http://modernizr.com/download/#-csstransforms3d-csstransitions-touch-prefixed
     */

    var Modernizr = (function (window, document, undefined) {
        var version = '2.8.3',
            Modernizr = {},


            docElement = document.documentElement,

            mod = 'modernizr',
            modElem = document.createElement(mod),
            mStyle = modElem.style,
            inputElem,


            toString = {}.toString,

            prefixes = ' -webkit- -moz- -o- -ms- '.split(' '),


            omPrefixes = 'Webkit Moz O ms',

            cssomPrefixes = omPrefixes.split(' '),

            domPrefixes = omPrefixes.toLowerCase().split(' '),


            tests = {},
            inputs = {},
            attrs = {},

            classes = [],

            slice = classes.slice,

            featureName,


            injectElementWithStyles = function (rule, callback, nodes, testnames) {

                var style, ret, node, docOverflow,
                    div = document.createElement('div'),
                    body = document.body,
                    fakeBody = body || document.createElement('body');

                if (parseInt(nodes, 10)) {
                    while (nodes--) {
                        node = document.createElement('div');
                        node.id = testnames ? testnames[nodes] : mod + (nodes + 1);
                        div.appendChild(node);
                    }
                }

                style = ['&#173;', '<style id="s', mod, '">', rule, '</style>'].join('');
                div.id = mod;
                (body ? div : fakeBody).innerHTML += style;
                fakeBody.appendChild(div);
                if (!body) {
                    fakeBody.style.background = '';
                    fakeBody.style.overflow = 'hidden';
                    docOverflow = docElement.style.overflow;
                    docElement.style.overflow = 'hidden';
                    docElement.appendChild(fakeBody);
                }

                ret = callback(div, rule);
                if (!body) {
                    fakeBody.parentNode.removeChild(fakeBody);
                    docElement.style.overflow = docOverflow;
                } else {
                    div.parentNode.removeChild(div);
                }

                return !!ret;

            },
            _hasOwnProperty = ({}).hasOwnProperty, hasOwnProp;

        if (!is(_hasOwnProperty, 'undefined') && !is(_hasOwnProperty.call, 'undefined')) {
            hasOwnProp = function (object, property) {
                return _hasOwnProperty.call(object, property);
            };
        }
        else {
            hasOwnProp = function (object, property) {
                return ((property in object) && is(object.constructor.prototype[property], 'undefined'));
            };
        }


        if (!Function.prototype.bind) {
            Function.prototype.bind = function bind(that) {

                var target = this;

                if (typeof target != "function") {
                    throw new TypeError();
                }

                var args = slice.call(arguments, 1),
                    bound = function () {

                        if (this instanceof bound) {

                            var F = function () {
                            };
                            F.prototype = target.prototype;
                            var self = new F();

                            var result = target.apply(
                                self,
                                args.concat(slice.call(arguments))
                            );
                            if (Object(result) === result) {
                                return result;
                            }
                            return self;

                        } else {

                            return target.apply(
                                that,
                                args.concat(slice.call(arguments))
                            );

                        }

                    };

                return bound;
            };
        }

        function setCss(str) {
            mStyle.cssText = str;
        }

        function setCssAll(str1, str2) {
            return setCss(prefixes.join(str1 + ';') + ( str2 || '' ));
        }

        function is(obj, type) {
            return typeof obj === type;
        }

        function contains(str, substr) {
            return !!~('' + str).indexOf(substr);
        }

        function testProps(props, prefixed) {
            for (var i in props) {
                var prop = props[i];
                if (!contains(prop, "-") && mStyle[prop] !== undefined) {
                    return prefixed == 'pfx' ? prop : true;
                }
            }
            return false;
        }

        function testDOMProps(props, obj, elem) {
            for (var i in props) {
                var item = obj[props[i]];
                if (item !== undefined) {

                    if (elem === false) return props[i];

                    if (is(item, 'function')) {
                        return item.bind(elem || obj);
                    }

                    return item;
                }
            }
            return false;
        }

        function testPropsAll(prop, prefixed, elem) {

            var ucProp = prop.charAt(0).toUpperCase() + prop.slice(1),
                props = (prop + ' ' + cssomPrefixes.join(ucProp + ' ') + ucProp).split(' ');

            if (is(prefixed, "string") || is(prefixed, "undefined")) {
                return testProps(props, prefixed);

            } else {
                props = (prop + ' ' + (domPrefixes).join(ucProp + ' ') + ucProp).split(' ');
                return testDOMProps(props, prefixed, elem);
            }
        }

        tests['touch'] = function () {
            var bool;

            if (('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch) {
                bool = true;
            } else {
                injectElementWithStyles(['@media (', prefixes.join('touch-enabled),('), mod, ')', '{#modernizr{top:9px;position:absolute}}'].join(''), function (node) {
                    bool = node.offsetTop === 9;
                });
            }

            return bool;
        };
        tests['csstransforms3d'] = function () {

            var ret = !!testPropsAll('perspective');

            if (ret && 'webkitPerspective' in docElement.style) {

                injectElementWithStyles('@media (transform-3d),(-webkit-transform-3d){#modernizr{left:9px;position:absolute;height:3px;}}', function (node, rule) {
                    ret = node.offsetLeft === 9 && node.offsetHeight === 3;
                });
            }
            return ret;
        };


        tests['csstransitions'] = function () {
            return testPropsAll('transition');
        };


        for (var feature in tests) {
            if (hasOwnProp(tests, feature)) {
                featureName = feature.toLowerCase();
                Modernizr[featureName] = tests[feature]();

                classes.push((Modernizr[featureName] ? '' : 'no-') + featureName);
            }
        }


        Modernizr.addTest = function (feature, test) {
            if (typeof feature == 'object') {
                for (var key in feature) {
                    if (hasOwnProp(feature, key)) {
                        Modernizr.addTest(key, feature[key]);
                    }
                }
            } else {

                feature = feature.toLowerCase();

                if (Modernizr[feature] !== undefined) {
                    return Modernizr;
                }

                test = typeof test == 'function' ? test() : test;

                if (typeof enableClasses !== "undefined" && enableClasses) {
                    docElement.className += ' ' + (test ? '' : 'no-') + feature;
                }
                Modernizr[feature] = test;

            }

            return Modernizr;
        };


        setCss('');
        modElem = inputElem = null;


        Modernizr._version = version;

        Modernizr._prefixes = prefixes;
        Modernizr._domPrefixes = domPrefixes;
        Modernizr._cssomPrefixes = cssomPrefixes;


        Modernizr.testProp = function (prop) {
            return testProps([prop]);
        };

        Modernizr.testAllProps = testPropsAll;
        Modernizr.testStyles = injectElementWithStyles;
        Modernizr.prefixed = function (prop, obj, elem) {
            if (!obj) {
                return testPropsAll(prop, 'pfx');
            } else {
                return testPropsAll(prop, obj, elem);
            }
        };
        return Modernizr;
    })(window, document);

    var fullScreenApi = {
            ok: false,
            is: function () {
                return false;
            },
            request: function () {
            },
            cancel: function () {
            },
            event: '',
            prefix: ''
        },
        browserPrefixes = 'webkit moz o ms khtml'.split(' ');

// check for native support
    if (typeof document.cancelFullScreen != 'undefined') {
        fullScreenApi.ok = true;
    } else {
        // check for fullscreen support by vendor prefix
        for (var i = 0, il = browserPrefixes.length; i < il; i++) {
            fullScreenApi.prefix = browserPrefixes[i];
            if (typeof document[fullScreenApi.prefix + 'CancelFullScreen'] != 'undefined') {
                fullScreenApi.ok = true;
                break;
            }
        }
    }

// update methods to do something useful
    if (fullScreenApi.ok) {
        fullScreenApi.event = fullScreenApi.prefix + 'fullscreenchange';
        fullScreenApi.is = function () {
            switch (this.prefix) {
                case '':
                    return document.fullScreen;
                case 'webkit':
                    return document.webkitIsFullScreen;
                default:
                    return document[this.prefix + 'FullScreen'];
            }
        };
        fullScreenApi.request = function (el) {
            return (this.prefix === '') ? el.requestFullScreen() : el[this.prefix + 'RequestFullScreen']();
        };
        fullScreenApi.cancel = function (el) {
            if (!this.is()) {
                return false;
            }
            return (this.prefix === '') ? document.cancelFullScreen() : document[this.prefix + 'CancelFullScreen']();
        };
    }
    /* Bez v1.0.10-g5ae0136
     * http://github.com/rdallasgray/bez
     *
     * A plugin to convert CSS3 cubic-bezier co-ordinates to jQuery-compatible easing functions
     *
     * With thanks to Nikolay Nemshilov for clarification on the cubic-bezier maths
     * See http://st-on-it.blogspot.com/2011/05/calculating-cubic-bezier-function.html
     *
     * Copyright 2011 Robert Dallas Gray. All rights reserved.
     * Provided under the FreeBSD license: https://github.com/rdallasgray/bez/blob/master/LICENSE.txt
     */
    function bez(coOrdArray) {
        var encodedFuncName = "bez_" + $.makeArray(arguments).join("_").replace(".", "p");
        if (typeof $['easing'][encodedFuncName] !== "function") {
            var polyBez = function (p1, p2) {
                var A = [null, null],
                    B = [null, null],
                    C = [null, null],
                    bezCoOrd = function (t, ax) {
                        C[ax] = 3 * p1[ax];
                        B[ax] = 3 * (p2[ax] - p1[ax]) - C[ax];
                        A[ax] = 1 - C[ax] - B[ax];
                        return t * (C[ax] + t * (B[ax] + t * A[ax]));
                    },
                    xDeriv = function (t) {
                        return C[0] + t * (2 * B[0] + 3 * A[0] * t);
                    },
                    xForT = function (t) {
                        var x = t, i = 0, z;
                        while (++i < 14) {
                            z = bezCoOrd(x, 0) - t;
                            if (Math.abs(z) < 1e-3) break;
                            x -= z / xDeriv(x);
                        }
                        return x;
                    };
                return function (t) {
                    return bezCoOrd(xForT(t), 1);
                }
            };
            $['easing'][encodedFuncName] = function (x, t, b, c, d) {
                return c * polyBez([coOrdArray[0], coOrdArray[1]], [coOrdArray[2], coOrdArray[3]])(t / d) + b;
            }
        }
        return encodedFuncName;
    }

    var $WINDOW = $(window),
        $DOCUMENT = $(document),
        $HTML,
        $BODY,

        QUIRKS_FORCE = location.hash.replace('#', '') === 'quirks',
        TRANSFORMS3D = Modernizr.csstransforms3d,
        CSS3 = TRANSFORMS3D && !QUIRKS_FORCE,
        COMPAT = TRANSFORMS3D || document.compatMode === 'CSS1Compat',
        FULLSCREEN = fullScreenApi.ok,

        MOBILE = navigator.userAgent.match(/Android|webOS|iPhone|iPad|iPod|BlackBerry|Windows Phone/i),
        SLOW = !CSS3 || MOBILE,

        MS_POINTER = navigator.msPointerEnabled,

        WHEEL = "onwheel" in document.createElement("div") ? "wheel" : document.onmousewheel !== undefined ? "mousewheel" : "DOMMouseScroll",

        TOUCH_TIMEOUT = 250,
        TRANSITION_DURATION = 300,

        SCROLL_LOCK_TIMEOUT = 1400,

        AUTOPLAY_INTERVAL = 5000,
        MARGIN = 2,
        THUMB_SIZE = 64,

        WIDTH = 500,
        HEIGHT = 333,

        STAGE_FRAME_KEY = '$stageFrame',
        NAV_DOT_FRAME_KEY = '$navDotFrame',
        NAV_THUMB_FRAME_KEY = '$navThumbFrame',

        AUTO = 'auto',

        BEZIER = bez([.1, 0, .25, 1]),

        MAX_WIDTH = 1200,

        /**
         * Number of thumbnails in slide. Calculated only on setOptions and resize.
         * @type {number}
         */
        thumbsPerSlide = 1,

        OPTIONS = {

            /**
             * Set width for gallery.
             * Default value - width of first image
             * Number - set value in px
             * String - set value in quotes
             *
             */
            width: null,

            /**
             * Set min-width for gallery
             *
             */
            minwidth: null,

            /**
             * Set max-width for gallery
             *
             */
            maxwidth: '100%',

            /**
             * Set height for gallery
             * Default value - height of first image
             * Number - set value in px
             * String - set value in quotes
             *
             */
            height: null,

            /**
             * Set min-height for gallery
             *
             */
            minheight: null,

            /**
             * Set max-height for gallery
             *
             */
            maxheight: null,

            /**
             * Set proportion ratio for gallery depends of image
             *
             */
            ratio: null, // '16/9' || 500/333 || 1.5

            margin: MARGIN,

            nav: 'dots', // 'thumbs' || false
            navposition: 'bottom', // 'top'
            navwidth: null,
            thumbwidth: THUMB_SIZE,
            thumbheight: THUMB_SIZE,
            thumbmargin: MARGIN,
            thumbborderwidth: MARGIN,

            allowfullscreen: false, // true || 'native'

            transition: 'slide', // 'crossfade' || 'dissolve'
            clicktransition: null,
            transitionduration: TRANSITION_DURATION,

            captions: true,

            startindex: 0,

            loop: false,

            autoplay: false,
            stopautoplayontouch: true,

            keyboard: false,

            arrows: true,
            click: true,
            swipe: false,
            trackpad: false,

            shuffle: false,

            direction: 'ltr', // 'rtl'

            shadows: true,

            showcaption: true,

            /**
             * Set type of thumbnail navigation
             */
            navdir: 'horizontal',

            /**
             * Set configuration to show or hide arrows in thumb navigation
             */
            navarrows: true,

            /**
             * Set type of navigation. Can be thumbs or slides
             */
            navtype: 'thumbs'

        },

        KEYBOARD_OPTIONS = {
            left: true,
            right: true,
            down: true,
            up: true,
            space: false,
            home: false,
            end: false
        };

    function noop() {
    }

    function minMaxLimit(value, min, max) {
        return Math.max(isNaN(min) ? -Infinity : min, Math.min(isNaN(max) ? Infinity : max, value));
    }

    function readTransform(css, dir) {
        return css.match(/ma/) && css.match(/-?\d+(?!d)/g)[css.match(/3d/) ?
                (dir === 'vertical' ? 13 : 12) : (dir === 'vertical' ? 5 : 4)
                ]
    }

    function readPosition($el, dir) {
        if (CSS3) {
            return +readTransform($el.css('transform'), dir);
        } else {
            return +$el.css(dir === 'vertical' ? 'top' : 'left').replace('px', '');
        }
    }

    function getTranslate(pos, direction) {
        var obj = {};

        if (CSS3) {

            switch (direction) {
                case 'vertical':
                    obj.transform = 'translate3d(0, ' + (pos) + 'px,0)';
                    break;
                case 'list':
                    break;
                default :
                    obj.transform = 'translate3d(' + (pos) + 'px,0,0)';
                    break;
            }
        } else {
            direction === 'vertical' ?
                obj.top = pos :
                obj.left = pos;
        }
        return obj;
    }

    function getDuration(time) {
        return {'transition-duration': time + 'ms'};
    }

    function unlessNaN(value, alternative) {
        return isNaN(value) ? alternative : value;
    }

    function numberFromMeasure(value, measure) {
        return unlessNaN(+String(value).replace(measure || 'px', ''));
    }

    function numberFromPercent(value) {
        return /%$/.test(value) ? numberFromMeasure(value, '%') : undefined;
    }

    function numberFromWhatever(value, whole) {
        return unlessNaN(numberFromPercent(value) / 100 * whole, numberFromMeasure(value));
    }

    function measureIsValid(value) {
        return (!isNaN(numberFromMeasure(value)) || !isNaN(numberFromMeasure(value, '%'))) && value;
    }

    function getPosByIndex(index, side, margin, baseIndex) {

        return (index - (baseIndex || 0)) * (side + (margin || 0));
    }

    function getIndexByPos(pos, side, margin, baseIndex) {
        return -Math.round(pos / (side + (margin || 0)) - (baseIndex || 0));
    }

    function bindTransitionEnd($el) {
        var elData = $el.data();

        if (elData.tEnd) return;

        var el = $el[0],
            transitionEndEvent = {
                WebkitTransition: 'webkitTransitionEnd',
                MozTransition: 'transitionend',
                OTransition: 'oTransitionEnd otransitionend',
                msTransition: 'MSTransitionEnd',
                transition: 'transitionend'
            };
        addEvent(el, transitionEndEvent[Modernizr.prefixed('transition')], function (e) {
            elData.tProp && e.propertyName.match(elData.tProp) && elData.onEndFn();
        });
        elData.tEnd = true;
    }

    function afterTransition($el, property, fn, time) {
        var ok,
            elData = $el.data();

        if (elData) {
            elData.onEndFn = function () {
                if (ok) return;
                ok = true;
                clearTimeout(elData.tT);
                fn();
            };
            elData.tProp = property;

            // Passive call, just in case of fail of native transition-end event
            clearTimeout(elData.tT);
            elData.tT = setTimeout(function () {
                elData.onEndFn();
            }, time * 1.5);

            bindTransitionEnd($el);
        }
    }


    function stop($el, pos/*, _001*/) {
        var dir = $el.navdir || 'horizontal';
        if ($el.length) {
            var elData = $el.data();
            if (CSS3) {
                $el.css(getDuration(0));
                elData.onEndFn = noop;
                clearTimeout(elData.tT);
            } else {
                $el.stop();
            }
            var lockedPos = getNumber(pos, function () {
                return readPosition($el, dir);
            });

            $el.css(getTranslate(lockedPos, dir/*, _001*/));//.width(); // `.width()` for reflow
            return lockedPos;
        }
    }

    function getNumber() {
        var number;
        for (var _i = 0, _l = arguments.length; _i < _l; _i++) {
            number = _i ? arguments[_i]() : arguments[_i];
            if (typeof number === 'number') {
                break;
            }
        }

        return number;
    }

    function edgeResistance(pos, edge) {
        return Math.round(pos + ((edge - pos) / 1.5));
    }

    function getProtocol() {
        getProtocol.p = getProtocol.p || (location.protocol === 'https:' ? 'https://' : 'http://');
        return getProtocol.p;
    }

    function parseHref(href) {
        var a = document.createElement('a');
        a.href = href;
        return a;
    }

    function findVideoId(href, forceVideo) {
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
        } else if (href.host.match(/youtube\.com|youtu\.be|youtube-nocookie.com/)) {
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

        return id ? {id: id, type: type, s: href.search.replace(/^\?/, ''), p: getProtocol()} : false;
    }

    function getVideoThumbs(dataFrame, data, fotorama) {
        var img, thumb, video = dataFrame.video;
        if (video.type === 'youtube') {
            thumb = getProtocol() + 'img.youtube.com/vi/' + video.id + '/default.jpg';
            img = thumb.replace(/\/default.jpg$/, '/hqdefault.jpg');
            dataFrame.thumbsReady = true;
        } else if (video.type === 'vimeo') {
            $.ajax({
                url: getProtocol() + 'vimeo.com/api/oembed.json',
                data: {
                    url: 'https://vimeo.com/' + video.id
                },
                dataType: 'jsonp',
                success: function (json) {
                    dataFrame.thumbsReady = true;
                    updateData(data, {
                        img: json[0].thumbnail_url,
                        thumb: json[0].thumbnail_url
                    }, dataFrame.i, fotorama);
                }
            });
        } else {
            dataFrame.thumbsReady = true;
        }

        return {
            img: img,
            thumb: thumb
        }
    }

    function updateData(data, _dataFrame, i, fotorama) {
        for (var _i = 0, _l = data.length; _i < _l; _i++) {
            var dataFrame = data[_i];

            if (dataFrame.i === i && dataFrame.thumbsReady) {
                var clear = {videoReady: true};
                clear[STAGE_FRAME_KEY] = clear[NAV_THUMB_FRAME_KEY] = clear[NAV_DOT_FRAME_KEY] = false;

                fotorama.splice(_i, 1, $.extend(
                    {},
                    dataFrame,
                    clear,
                    _dataFrame
                ));

                break;
            }
        }
    }

    function getDataFromHtml($el) {
        var data = [];

        function getDataFromImg($img, imgData, checkVideo) {
            var $child = $img.children('img').eq(0),
                _imgHref = $img.attr('href'),
                _imgSrc = $img.attr('src'),
                _thumbSrc = $child.attr('src'),
                _video = imgData.video,
                video = checkVideo ? findVideoId(_imgHref, _video === true) : false;

            if (video) {
                _imgHref = false;
            } else {
                video = _video;
            }

            getDimensions($img, $child, $.extend(imgData, {
                video: video,
                img: imgData.img || _imgHref || _imgSrc || _thumbSrc,
                thumb: imgData.thumb || _thumbSrc || _imgSrc || _imgHref
            }));
        }

        function getDimensions($img, $child, imgData) {
            var separateThumbFLAG = imgData.thumb && imgData.img !== imgData.thumb,
                width = numberFromMeasure(imgData.width || $img.attr('width')),
                height = numberFromMeasure(imgData.height || $img.attr('height'));

            $.extend(imgData, {
                width: width,
                height: height,
                thumbratio: getRatio(imgData.thumbratio || (numberFromMeasure(imgData.thumbwidth || ($child && $child.attr('width')) || separateThumbFLAG || width) / numberFromMeasure(imgData.thumbheight || ($child && $child.attr('height')) || separateThumbFLAG || height)))
            });
        }

        $el.children().each(function () {
            var $this = $(this),
                dataFrame = optionsToLowerCase($.extend($this.data(), {id: $this.attr('id')}));
            if ($this.is('a, img')) {
                getDataFromImg($this, dataFrame, true);
            } else if (!$this.is(':empty')) {
                getDimensions($this, null, $.extend(dataFrame, {
                    html: this,
                    _html: $this.html() // Because of IE
                }));
            } else return;

            data.push(dataFrame);
        });

        return data;
    }

    function isHidden(el) {
        return el.offsetWidth === 0 && el.offsetHeight === 0;
    }

    function isDetached(el) {
        return !$.contains(document.documentElement, el);
    }

    function waitFor(test, fn, timeout, i) {
        if (!waitFor.i) {
            waitFor.i = 1;
            waitFor.ii = [true];
        }

        i = i || waitFor.i;

        if (typeof waitFor.ii[i] === 'undefined') {
            waitFor.ii[i] = true;
        }

        if (test()) {
            fn();
        } else {
            waitFor.ii[i] && setTimeout(function () {
                waitFor.ii[i] && waitFor(test, fn, timeout, i);
            }, timeout || 100);
        }

        return waitFor.i++;
    }

    waitFor.stop = function (i) {
        waitFor.ii[i] = false;
    };

    function fit($el, measuresToFit) {
        var elData = $el.data(),
            measures = elData.measures;

        if (measures && (!elData.l ||
            elData.l.W !== measures.width ||
            elData.l.H !== measures.height ||
            elData.l.r !== measures.ratio ||
            elData.l.w !== measuresToFit.w ||
            elData.l.h !== measuresToFit.h)) {

            var height = minMaxLimit(measuresToFit.h, 0, measures.height),
                width = height * measures.ratio;

            UTIL.setRatio($el, width, height);

            elData.l = {
                W: measures.width,
                H: measures.height,
                r: measures.ratio,
                w: measuresToFit.w,
                h: measuresToFit.h
            };
        }

        return true;
    }

    function setStyle($el, style) {
        var el = $el[0];
        if (el.styleSheet) {
            el.styleSheet.cssText = style;
        } else {
            $el.html(style);
        }
    }

    function findShadowEdge(pos, min, max, dir) {
        return min === max ? false :
            dir === 'vertical' ?
                (pos <= min ? 'top' : pos >= max ? 'bottom' : 'top bottom') :
                (pos <= min ? 'left' : pos >= max ? 'right' : 'left right');
    }

    function smartClick($el, fn, _options) {
        _options = _options || {};

        $el.each(function () {
            var $this = $(this),
                thisData = $this.data(),
                startEvent;

            if (thisData.clickOn) return;

            thisData.clickOn = true;

            $.extend(touch($this, {
                onStart: function (e) {
                    startEvent = e;
                    (_options.onStart || noop).call(this, e);
                },
                onMove: _options.onMove || noop,
                onTouchEnd: _options.onTouchEnd || noop,
                onEnd: function (result) {
                    if (result.moved) return;
                    fn.call(this, startEvent);
                }
            }), {noMove: true});
        });
    }

    function div(classes, child) {
        return '<div class="' + classes + '">' + (child || '') + '</div>';
    }


    /**
     * Function transforming into valid classname
     * @param className - name of the class
     * @returns {string} - dom format of class name
     */
    function cls(className) {
        return "." + className;
    }

    /**
     *
     * @param {json-object} videoItem Parsed object from data.video item or href from link a in input dates
     * @returns {string} DOM view of video iframe
     */
    function createVideoFrame(videoItem) {
        var frame = '<iframe src="' + videoItem.p + videoItem.type + '.com/embed/' + videoItem.id + '" frameborder="0" allowfullscreen></iframe>';
        return frame;
    }

// Fisher–Yates Shuffle
// http://bost.ocks.org/mike/shuffle/
    function shuffle(array) {
        // While there remain elements to shuffle
        var l = array.length;
        while (l) {
            // Pick a remaining element
            var i = Math.floor(Math.random() * l--);

            // And swap it with the current element
            var t = array[l];
            array[l] = array[i];
            array[i] = t;
        }

        return array;
    }

    function clone(array) {
        return Object.prototype.toString.call(array) == '[object Array]'
            && $.map(array, function (frame) {
                return $.extend({}, frame);
            });
    }

    function lockScroll($el, left, top) {
        $el
            .scrollLeft(left || 0)
            .scrollTop(top || 0);
    }

    function optionsToLowerCase(options) {
        if (options) {
            var opts = {};
            $.each(options, function (key, value) {
                opts[key.toLowerCase()] = value;
            });

            return opts;
        }
    }

    function getRatio(_ratio) {
        if (!_ratio) return;
        var ratio = +_ratio;
        if (!isNaN(ratio)) {
            return ratio;
        } else {
            ratio = _ratio.split('/');
            return +ratio[0] / +ratio[1] || undefined;
        }
    }

    function addEvent(el, e, fn, bool) {
        if (!e) return;
        el.addEventListener ? el.addEventListener(e, fn, {passive: true}) : el.attachEvent('on' + e, fn);
    }

    /**
     *
     * @param position guess position for navShaft
     * @param restriction object contains min and max values for position
     * @returns {*} filtered value of position
     */
    function validateRestrictions(position, restriction) {
        if (position > restriction.max) {
            position = restriction.max;
        } else {
            if (position < restriction.min) {
                position = restriction.min;
            }
        }
        return position;
    }

    function validateSlidePos(opt, navShaftTouchTail, guessIndex, offsetNav, $guessNavFrame, $navWrap, dir) {
        var position,
            size,
            wrapSize;
        if (dir === 'horizontal') {
            size = opt.thumbwidth;
            wrapSize = $navWrap.width();
        } else {
            size = opt.thumbheight;
            wrapSize = $navWrap.height();
        }
        if ( (size + opt.margin) * (guessIndex + 1) >= (wrapSize - offsetNav) ) {
            if (dir === 'horizontal') {
                position = -$guessNavFrame.position().left;
            } else {
                position = -$guessNavFrame.position().top;
            }
        } else {
            if ((size + opt.margin) * (guessIndex) <= Math.abs(offsetNav)) {
                if (dir === 'horizontal') {
                    position = -$guessNavFrame.position().left + wrapSize - (size + opt.margin);
                } else {
                    position = -$guessNavFrame.position().top + wrapSize - (size + opt.margin);
                }
            } else {
                position = offsetNav;
            }
        }
        position = validateRestrictions(position, navShaftTouchTail);

        return position || 0;
    }

    function elIsDisabled(el) {
        return !!el.getAttribute('disabled');
    }

    function disableAttr(FLAG, disable) {
        if (disable) {
            return {disabled: FLAG};
        } else {
            return {tabindex: FLAG * -1 + '', disabled: FLAG};

        }
    }

    function addEnterUp(el, fn) {
        addEvent(el, 'keyup', function (e) {
            elIsDisabled(el) || e.keyCode == 13 && fn.call(el, e);
        });
    }

    function addFocus(el, fn) {
        addEvent(el, 'focus', el.onfocusin = function (e) {
            fn.call(el, e);
        }, true);
    }

    function stopEvent(e, stopPropagation) {
        e.preventDefault ? e.preventDefault() : (e.returnValue = false);
        stopPropagation && e.stopPropagation && e.stopPropagation();
    }

    function getDirectionSign(forward) {
        return forward ? '>' : '<';
    }

    var UTIL = (function () {

        function setRatioClass($el, wh, ht) {
            var rateImg = wh / ht;

            if (rateImg <= 1) {
                $el.parent().removeClass(horizontalImageClass);
                $el.parent().addClass(verticalImageClass);
            } else {
                $el.parent().removeClass(verticalImageClass);
                $el.parent().addClass(horizontalImageClass);
            }
        }

        /**
         * Set specific attribute in thumbnail template
         * @param $frame DOM item of specific thumbnail
         * @param value Value which must be setted into specific attribute
         * @param searchAttr Name of attribute where value must be included
         */
        function setThumbAttr($frame, value, searchAttr) {
            var attr = searchAttr;

            if (!$frame.attr(attr) && $frame.attr(attr) !== undefined) {
                $frame.attr(attr, value);
            }

            if ($frame.find("[" + attr + "]").length) {
                $frame.find("[" + attr + "]")
                    .each(function () {
                        $(this).attr(attr, value);
                    });
            }
        }

        /**
         * Method describe behavior need to render caption on preview or not
         * @param frameItem specific item from data
         * @param isExpected {bool} if items with caption need render them or not
         * @returns {boolean} if true then caption should be rendered
         */
        function isExpectedCaption(frameItem, isExpected, undefined) {
            var expected = false,
                frameExpected;

            frameItem.showCaption === undefined || frameItem.showCaption === true ? frameExpected = true : frameExpected = false;

            if (!isExpected) {
                return false;
            }

            if (frameItem.caption && frameExpected) {
                expected = true;
            }

            return expected;
        }

        return {
            setRatio: setRatioClass,
            setThumbAttr: setThumbAttr,
            isExpectedCaption: isExpectedCaption
        };

    }(UTIL || {}, jQuery));

    function slide($el, options) {
        var elData = $el.data(),
            elPos = Math.round(options.pos),
            onEndFn = function () {
                if (elData && elData.sliding) {
                    elData.sliding = false;
                }
                (options.onEnd || noop)();
            };

        if (typeof options.overPos !== 'undefined' && options.overPos !== options.pos) {
            elPos = options.overPos;
        }

        var translate = $.extend(getTranslate(elPos, options.direction), options.width && {width: options.width}, options.height && {height: options.height});
        if (elData && elData.sliding) {
            elData.sliding = true;
        }

        if (CSS3) {
            $el.css($.extend(getDuration(options.time), translate));

            if (options.time > 10) {
                afterTransition($el, 'transform', onEndFn, options.time);
            } else {
                onEndFn();
            }
        } else {
            $el.stop().animate(translate, options.time, BEZIER, onEndFn);
        }
    }

    function fade($el1, $el2, $frames, options, fadeStack, chain) {
        var chainedFLAG = typeof chain !== 'undefined';
        if (!chainedFLAG) {
            fadeStack.push(arguments);
            Array.prototype.push.call(arguments, fadeStack.length);
            if (fadeStack.length > 1) return;
        }

        $el1 = $el1 || $($el1);
        $el2 = $el2 || $($el2);

        var _$el1 = $el1[0],
            _$el2 = $el2[0],
            crossfadeFLAG = options.method === 'crossfade',
            onEndFn = function () {
                if (!onEndFn.done) {
                    onEndFn.done = true;
                    var args = (chainedFLAG || fadeStack.shift()) && fadeStack.shift();
                    args && fade.apply(this, args);
                    (options.onEnd || noop)(!!args);
                }
            },
            time = options.time / (chain || 1);

        $frames.removeClass(fadeRearClass + ' ' + fadeFrontClass);

        $el1
            .stop()
            .addClass(fadeRearClass);
        $el2
            .stop()
            .addClass(fadeFrontClass);

        crossfadeFLAG && _$el2 && $el1.fadeTo(0, 0);

        $el1.fadeTo(crossfadeFLAG ? time : 0, 1, crossfadeFLAG && onEndFn);
        $el2.fadeTo(time, 0, onEndFn);

        (_$el1 && crossfadeFLAG) || _$el2 || onEndFn();
    }

    var lastEvent,
        moveEventType,
        preventEvent,
        preventEventTimeout,
        dragDomEl;

    function extendEvent(e) {
        var touch = (e.touches || [])[0] || e;
        e._x = touch.pageX || touch.originalEvent.pageX;
        e._y = touch.clientY || touch.originalEvent.clientY;
        e._now = $.now();
    }

    function touch($el, options) {
        var el = $el[0],
            tail = {},
            touchEnabledFLAG,
            startEvent,
            $target,
            controlTouch,
            touchFLAG,
            targetIsSelectFLAG,
            targetIsLinkFlag,
            isDisabledSwipe,
            tolerance,
            moved;

        function onStart(e) {
            $target = $(e.target);
            tail.checked = targetIsSelectFLAG = targetIsLinkFlag = isDisabledSwipe = moved = false;

            if (touchEnabledFLAG
                || tail.flow
                || (e.touches && e.touches.length > 1)
                || e.which > 1
                || (lastEvent && lastEvent.type !== e.type && preventEvent)
                || (targetIsSelectFLAG = options.select && $target.is(options.select, el))) return targetIsSelectFLAG;

            touchFLAG = e.type === 'touchstart';
            targetIsLinkFlag = $target.is('a, a *', el);
            isDisabledSwipe = $target.hasClass('disableSwipe');
            controlTouch = tail.control;

            tolerance = (tail.noMove || tail.noSwipe || controlTouch) ? 16 : !tail.snap ? 4 : 0;

            extendEvent(e);

            startEvent = lastEvent = e;
            moveEventType = e.type.replace(/down|start/, 'move').replace(/Down/, 'Move');

            (options.onStart || noop).call(el, e, {control: controlTouch, $target: $target});

            touchEnabledFLAG = tail.flow = true;

            if (!isDisabledSwipe && (!touchFLAG || tail.go)) stopEvent(e);
        }

        function onMove(e) {
            if ((e.touches && e.touches.length > 1)
                || (MS_POINTER && !e.isPrimary)
                || moveEventType !== e.type
                || !touchEnabledFLAG) {
                touchEnabledFLAG && onEnd();
                (options.onTouchEnd || noop)();
                return;
            }

            isDisabledSwipe = $(e.target).hasClass('disableSwipe');

            if (isDisabledSwipe) {
                return;
            }

            extendEvent(e);

            var xDiff = Math.abs(e._x - startEvent._x), // opt _x → _pageX
                yDiff = Math.abs(e._y - startEvent._y),
                xyDiff = xDiff - yDiff,
                xWin = (tail.go || tail.x || xyDiff >= 0) && !tail.noSwipe,
                yWin = xyDiff < 0;

            if (touchFLAG && !tail.checked) {
                if (touchEnabledFLAG = xWin) {
                    stopEvent(e);
                }
            } else {
                stopEvent(e);
                if (movedEnough(xDiff,yDiff)) {
                    (options.onMove || noop).call(el, e, {touch: touchFLAG});
                }
            }

            if (!moved && movedEnough(xDiff, yDiff) && Math.sqrt(Math.pow(xDiff, 2) + Math.pow(yDiff, 2)) > tolerance) {
                moved = true;
            }

            tail.checked = tail.checked || xWin || yWin;
        }

        function movedEnough(xDiff, yDiff) {
            return xDiff > yDiff && xDiff > 1.5;
        }

        function onEnd(e) {
            (options.onTouchEnd || noop)();

            var _touchEnabledFLAG = touchEnabledFLAG;
            tail.control = touchEnabledFLAG = false;

            if (_touchEnabledFLAG) {
                tail.flow = false;
            }

            if (!_touchEnabledFLAG || (targetIsLinkFlag && !tail.checked)) return;

            e && stopEvent(e);

            preventEvent = true;
            clearTimeout(preventEventTimeout);
            preventEventTimeout = setTimeout(function () {
                preventEvent = false;
            }, 1000);

            (options.onEnd || noop).call(el, {
                moved: moved,
                $target: $target,
                control: controlTouch,
                touch: touchFLAG,
                startEvent: startEvent,
                aborted: !e || e.type === 'MSPointerCancel'
            });
        }

        function onOtherStart() {
            if (tail.flow) return;
            tail.flow = true;
        }

        function onOtherEnd() {
            if (!tail.flow) return;
            tail.flow = false;
        }

        if (MS_POINTER) {
            addEvent(el, 'MSPointerDown', onStart);
            addEvent(document, 'MSPointerMove', onMove);
            addEvent(document, 'MSPointerCancel', onEnd);
            addEvent(document, 'MSPointerUp', onEnd);
        } else {
            addEvent(el, 'touchstart', onStart);
            addEvent(el, 'touchmove', onMove);
            addEvent(el, 'touchend', onEnd);

            addEvent(document, 'touchstart', onOtherStart);
            addEvent(document, 'touchend', onOtherEnd);
            addEvent(document, 'touchcancel', onOtherEnd);

            $WINDOW.on('scroll', onOtherEnd);

            $el.on('mousedown', onStart);
            $DOCUMENT
                .on('mousemove', onMove)
                .on('mouseup', onEnd);
        }
        if (Modernizr.touch) {
            dragDomEl = 'a';
        } else {
            dragDomEl = 'div';
        }
        $el.on('click', dragDomEl, function (e) {
            tail.checked && stopEvent(e);
        });

        return tail;
    }

    function moveOnTouch($el, options) {
        var el = $el[0],
            elData = $el.data(),
            tail = {},
            startCoo,
            coo,
            startElPos,
            moveElPos,
            edge,
            moveTrack,
            startTime,
            endTime,
            min,
            max,
            snap,
            dir,
            slowFLAG,
            controlFLAG,
            moved,
            tracked;

        function startTracking(e, noStop) {
            tracked = true;
            startCoo = coo = (dir === 'vertical') ? e._y : e._x;
            startTime = e._now;

            moveTrack = [
                [startTime, startCoo]
            ];

            startElPos = moveElPos = tail.noMove || noStop ? 0 : stop($el, (options.getPos || noop)()/*, options._001*/);

            (options.onStart || noop).call(el, e);
        }

        function onStart(e, result) {
            min = tail.min;
            max = tail.max;
            snap = tail.snap,
                dir = tail.direction || 'horizontal',
                $el.navdir = dir;

            slowFLAG = e.altKey;
            tracked = moved = false;

            controlFLAG = result.control;

            if (!controlFLAG && !elData.sliding) {
                startTracking(e);
            }
        }

        function onMove(e, result) {
            if (!tail.noSwipe) {
                if (!tracked) {
                    startTracking(e);
                }
                coo = (dir === 'vertical') ? e._y : e._x;

                moveTrack.push([e._now, coo]);

                moveElPos = startElPos - (startCoo - coo);

                edge = findShadowEdge(moveElPos, min, max, dir);

                if (moveElPos <= min) {
                    moveElPos = edgeResistance(moveElPos, min);
                } else if (moveElPos >= max) {
                    moveElPos = edgeResistance(moveElPos, max);
                }

                if (!tail.noMove) {
                    $el.css(getTranslate(moveElPos, dir));
                    if (!moved) {
                        moved = true;
                        // only for mouse
                        result.touch || MS_POINTER || $el.addClass(grabbingClass);
                    }

                    (options.onMove || noop).call(el, e, {pos: moveElPos, edge: edge});
                }
            }
        }

        function onEnd(result) {
            if (tail.noSwipe && result.moved) return;

            if (!tracked) {
                startTracking(result.startEvent, true);
            }

            result.touch || MS_POINTER || $el.removeClass(grabbingClass);

            endTime = $.now();

            var _backTimeIdeal = endTime - TOUCH_TIMEOUT,
                _backTime,
                _timeDiff,
                _timeDiffLast,
                backTime = null,
                backCoo,
                virtualPos,
                limitPos,
                newPos,
                overPos,
                time = TRANSITION_DURATION,
                speed,
                friction = options.friction;

            for (var _i = moveTrack.length - 1; _i >= 0; _i--) {
                _backTime = moveTrack[_i][0];
                _timeDiff = Math.abs(_backTime - _backTimeIdeal);
                if (backTime === null || _timeDiff < _timeDiffLast) {
                    backTime = _backTime;
                    backCoo = moveTrack[_i][1];
                } else if (backTime === _backTimeIdeal || _timeDiff > _timeDiffLast) {
                    break;
                }
                _timeDiffLast = _timeDiff;
            }

            newPos = minMaxLimit(moveElPos, min, max);

            var cooDiff = backCoo - coo,
                forwardFLAG = cooDiff >= 0,
                timeDiff = endTime - backTime,
                longTouchFLAG = timeDiff > TOUCH_TIMEOUT,
                swipeFLAG = !longTouchFLAG && moveElPos !== startElPos && newPos === moveElPos;

            if (snap) {
                newPos = minMaxLimit(Math[swipeFLAG ? (forwardFLAG ? 'floor' : 'ceil') : 'round'](moveElPos / snap) * snap, min, max);
                min = max = newPos;
            }

            if (swipeFLAG && (snap || newPos === moveElPos)) {
                speed = -(cooDiff / timeDiff);
                time *= minMaxLimit(Math.abs(speed), options.timeLow, options.timeHigh);
                virtualPos = Math.round(moveElPos + speed * time / friction);

                if (!snap) {
                    newPos = virtualPos;
                }

                if (!forwardFLAG && virtualPos > max || forwardFLAG && virtualPos < min) {
                    limitPos = forwardFLAG ? min : max;
                    overPos = virtualPos - limitPos;
                    if (!snap) {
                        newPos = limitPos;
                    }
                    overPos = minMaxLimit(newPos + overPos * .03, limitPos - 50, limitPos + 50);
                    time = Math.abs((moveElPos - overPos) / (speed / friction));
                }
            }

            time *= slowFLAG ? 10 : 1;

            (options.onEnd || noop).call(el, $.extend(result, {
                moved: result.moved || longTouchFLAG && snap,
                pos: moveElPos,
                newPos: newPos,
                overPos: overPos,
                time: time,
                dir: dir
            }));
        }

        tail = $.extend(touch(options.$wrap, $.extend({}, options, {
            onStart: onStart,
            onMove: onMove,
            onEnd: onEnd
        })), tail);

        return tail;
    }

    function wheel($el, options) {
        var el = $el[0],
            lockFLAG,
            lastDirection,
            lastNow,
            tail = {
                prevent: {}
            };

        addEvent(el, WHEEL, function (e) {
            var yDelta = e.wheelDeltaY || -1 * e.deltaY || 0,
                xDelta = e.wheelDeltaX || -1 * e.deltaX || 0,
                xWin = Math.abs(xDelta) && !Math.abs(yDelta),
                direction = getDirectionSign(xDelta < 0),
                sameDirection = lastDirection === direction,
                now = $.now(),
                tooFast = now - lastNow < TOUCH_TIMEOUT;

            lastDirection = direction;
            lastNow = now;

            if (!xWin || !tail.ok || tail.prevent[direction] && !lockFLAG) {
                return;
            } else {
                stopEvent(e, true);
                if (lockFLAG && sameDirection && tooFast) {
                    return;
                }
            }

            if (options.shift) {
                lockFLAG = true;
                clearTimeout(tail.t);
                tail.t = setTimeout(function () {
                    lockFLAG = false;
                }, SCROLL_LOCK_TIMEOUT);
            }

            (options.onEnd || noop)(e, options.shift ? direction : xDelta);

        });

        return tail;
    }

    jQuery.Fotorama = function ($fotorama, opts) {
        $HTML = $('html');
        $BODY = $('body');

        var that = this,
            stamp = $.now(),
            stampClass = _fotoramaClass + stamp,
            fotorama = $fotorama[0],
            data,
            dataFrameCount = 1,
            fotoramaData = $fotorama.data(),
            size,

            $style = $('<style></style>'),

            $anchor = $(div(hiddenClass)),
            $wrap = $fotorama.find(cls(wrapClass)),
            $stage = $wrap.find(cls(stageClass)),
            stage = $stage[0],

            $stageShaft = $fotorama.find(cls(stageShaftClass)),
            $stageFrame = $(),
            $arrPrev = $fotorama.find(cls(arrPrevClass)),
            $arrNext = $fotorama.find(cls(arrNextClass)),
            $arrs = $fotorama.find(cls(arrClass)),
            $navWrap = $fotorama.find(cls(navWrapClass)),
            $nav = $navWrap.find(cls(navClass)),
            $navShaft = $nav.find(cls(navShaftClass)),
            $navFrame,
            $navDotFrame = $(),
            $navThumbFrame = $(),

            stageShaftData = $stageShaft.data(),
            navShaftData = $navShaft.data(),

            $thumbBorder = $fotorama.find(cls(thumbBorderClass)),
            $thumbArrLeft = $fotorama.find(cls(thumbArrLeft)),
            $thumbArrRight = $fotorama.find(cls(thumbArrRight)),

            $fullscreenIcon = $fotorama.find(cls(fullscreenIconClass)),
            fullscreenIcon = $fullscreenIcon[0],
            $videoPlay = $(div(videoPlayClass)),
            $videoClose = $fotorama.find(cls(videoCloseClass)),
            videoClose = $videoClose[0],

            $spinner = $fotorama.find(cls(fotoramaSpinnerClass)),

            $videoPlaying,

            activeIndex = false,
            activeFrame,
            activeIndexes,
            repositionIndex,
            dirtyIndex,
            lastActiveIndex,
            prevIndex,
            nextIndex,
            nextAutoplayIndex,
            startIndex,

            o_loop,
            o_nav,
            o_navThumbs,
            o_navTop,
            o_allowFullScreen,
            o_nativeFullScreen,
            o_fade,
            o_thumbSide,
            o_thumbSide2,
            o_transitionDuration,
            o_transition,
            o_shadows,
            o_rtl,
            o_keyboard,
            lastOptions = {},

            measures = {},
            measuresSetFLAG,

            stageShaftTouchTail = {},
            stageWheelTail = {},
            navShaftTouchTail = {},
            navWheelTail = {},

            scrollTop,
            scrollLeft,

            showedFLAG,
            pausedAutoplayFLAG,
            stoppedAutoplayFLAG,

            toDeactivate = {},
            toDetach = {},

            measuresStash,

            touchedFLAG,

            hoverFLAG,

            navFrameKey,
            stageLeft = 0,

            fadeStack = [];

        $wrap[STAGE_FRAME_KEY] = $('<div class="' + stageFrameClass + '"></div>');
        $wrap[NAV_THUMB_FRAME_KEY] = $($.Fotorama.jst.thumb());
        $wrap[NAV_DOT_FRAME_KEY] = $($.Fotorama.jst.dots());

        toDeactivate[STAGE_FRAME_KEY] = [];
        toDeactivate[NAV_THUMB_FRAME_KEY] = [];
        toDeactivate[NAV_DOT_FRAME_KEY] = [];
        toDetach[STAGE_FRAME_KEY] = {};

        $wrap.addClass(CSS3 ? wrapCss3Class : wrapCss2Class);

        fotoramaData.fotorama = this;

        /**
         * Search video items in incoming data and transform object for video layout.
         *
         */
        function checkForVideo() {
            $.each(data, function (i, dataFrame) {
                if (!dataFrame.i) {
                    dataFrame.i = dataFrameCount++;
                    var video = findVideoId(dataFrame.video, true);
                    if (video) {
                        var thumbs = {};
                        dataFrame.video = video;
                        if (!dataFrame.img && !dataFrame.thumb) {
                            thumbs = getVideoThumbs(dataFrame, data, that);
                        } else {
                            dataFrame.thumbsReady = true;
                        }
                        updateData(data, {img: thumbs.img, thumb: thumbs.thumb}, dataFrame.i, that);
                    }
                }
            });
        }

        /**
         * Checks if current media object is YouTube or Vimeo video stream
         * @returns {boolean}
         */
        function isVideo() {
            return $((that.activeFrame || {}).$stageFrame || {}).hasClass('fotorama-video-container');
        }

        function allowKey(key) {
            return o_keyboard[key];
        }

        function setStagePosition() {
            if ($stage !== undefined) {

                if (opts.navdir == 'vertical') {
                    var padding = opts.thumbwidth + opts.thumbmargin;

                    $stage.css('left', padding);
                    $arrNext.css('right', padding);
                    $fullscreenIcon.css('right', padding);
                    $wrap.css('width', $wrap.css('width') + padding);
                    $stageShaft.css('max-width', $wrap.width() - padding);
                } else {
                    $stage.css('left', '');
                    $arrNext.css('right', '');
                    $fullscreenIcon.css('right', '');
                    $wrap.css('width', $wrap.css('width') + padding);
                    $stageShaft.css('max-width', '');
                }
            }
        }

        function bindGlobalEvents(FLAG) {
            var keydownCommon = 'keydown.' + _fotoramaClass,
                localStamp = _fotoramaClass + stamp,
                keydownLocal = 'keydown.' + localStamp,
                keyupLocal = 'keyup.' + localStamp,
                resizeLocal = 'resize.' + localStamp + ' ' + 'orientationchange.' + localStamp,
                showParams;

            if (FLAG) {
                $DOCUMENT
                    .on(keydownLocal, function (e) {
                        var catched,
                            index;

                        if ($videoPlaying && e.keyCode === 27) {
                            catched = true;
                            unloadVideo($videoPlaying, true, true);
                        } else if (that.fullScreen || (opts.keyboard && !that.index)) {
                            if (e.keyCode === 27) {
                                catched = true;
                                that.cancelFullScreen();
                            } else if ((e.shiftKey && e.keyCode === 32 && allowKey('space')) || (!e.altKey && !e.metaKey && e.keyCode === 37 && allowKey('left')) || (e.keyCode === 38 && allowKey('up') && $(':focus').attr('data-gallery-role'))) {
                                that.longPress.progress();
                                index = '<';
                            } else if ((e.keyCode === 32 && allowKey('space')) || (!e.altKey && !e.metaKey && e.keyCode === 39 && allowKey('right')) || (e.keyCode === 40 && allowKey('down') && $(':focus').attr('data-gallery-role'))) {
                                that.longPress.progress();
                                index = '>';
                            } else if (e.keyCode === 36 && allowKey('home')) {
                                that.longPress.progress();
                                index = '<<';
                            } else if (e.keyCode === 35 && allowKey('end')) {
                                that.longPress.progress();
                                index = '>>';
                            }
                        }

                        (catched || index) && stopEvent(e);
                        showParams = {index: index, slow: e.altKey, user: true};
                        index && (that.longPress.inProgress ?
                            that.showWhileLongPress(showParams) :
                            that.show(showParams));
                    });

                if (FLAG) {
                    $DOCUMENT
                        .on(keyupLocal, function (e) {
                            if (that.longPress.inProgress) {
                                that.showEndLongPress({user: true});
                            }
                            that.longPress.reset();
                        });
                }

                if (!that.index) {
                    $DOCUMENT
                        .off(keydownCommon)
                        .on(keydownCommon, 'textarea, input, select', function (e) {
                            !$BODY.hasClass(_fullscreenClass) && e.stopPropagation();
                        });
                }

                $WINDOW.on(resizeLocal, that.resize);
            } else {
                $DOCUMENT.off(keydownLocal);
                $WINDOW.off(resizeLocal);
            }
        }

        function appendElements(FLAG) {
            if (FLAG === appendElements.f) return;

            if (FLAG) {
                $fotorama
                    .addClass(_fotoramaClass + ' ' + stampClass)
                    .before($anchor)
                    .before($style);
                addInstance(that);
            } else {
                $anchor.detach();
                $style.detach();
                $fotorama
                    .html(fotoramaData.urtext)
                    .removeClass(stampClass);

                hideInstance(that);
            }

            bindGlobalEvents(FLAG);
            appendElements.f = FLAG;
        }

        /**
         * Set and install data from incoming @param {JSON} options or takes data attr from data-"name"=... values.
         */
        function setData() {
            data = that.data = data || clone(opts.data) || getDataFromHtml($fotorama);
            size = that.size = data.length;

            ready.ok && opts.shuffle && shuffle(data);

            checkForVideo();

            activeIndex = limitIndex(activeIndex);

            size && appendElements(true);
        }

        function stageNoMove() {
            var _noMove = size < 2 || $videoPlaying;
            stageShaftTouchTail.noMove = _noMove || o_fade;
            stageShaftTouchTail.noSwipe = _noMove || !opts.swipe;

            !o_transition && $stageShaft.toggleClass(grabClass, !opts.click && !stageShaftTouchTail.noMove && !stageShaftTouchTail.noSwipe);
            MS_POINTER && $wrap.toggleClass(wrapPanYClass, !stageShaftTouchTail.noSwipe);
        }

        function setAutoplayInterval(interval) {
            if (interval === true) interval = '';
            opts.autoplay = Math.max(+interval || AUTOPLAY_INTERVAL, o_transitionDuration * 1.5);
        }

        function updateThumbArrow(opt) {
            if (opt.navarrows && opt.nav === 'thumbs') {
                $thumbArrLeft.show();
                $thumbArrRight.show();
            } else {
                $thumbArrLeft.hide();
                $thumbArrRight.hide();
            }

        }

        function getThumbsInSlide($el, opts) {
            return Math.floor($wrap.width() / (opts.thumbwidth + opts.thumbmargin));
        }

        /**
         * Options on the fly
         * */
        function setOptions() {
            if (!opts.nav || opts.nav === 'dots') {
                opts.navdir = 'horizontal'
            }

            that.options = opts = optionsToLowerCase(opts);
            thumbsPerSlide = getThumbsInSlide($wrap, opts);

            o_fade = (opts.transition === 'crossfade' || opts.transition === 'dissolve');

            o_loop = opts.loop && (size > 2 || (o_fade && (!o_transition || o_transition !== 'slide')));

            o_transitionDuration = +opts.transitionduration || TRANSITION_DURATION;

            o_rtl = opts.direction === 'rtl';

            o_keyboard = $.extend({}, opts.keyboard && KEYBOARD_OPTIONS, opts.keyboard);
            updateThumbArrow(opts);
            var classes = {add: [], remove: []};

            function addOrRemoveClass(FLAG, value) {
                classes[FLAG ? 'add' : 'remove'].push(value);
            }

            if (size > 1) {
                o_nav = opts.nav;
                o_navTop = opts.navposition === 'top';
                classes.remove.push(selectClass);

                $arrs.toggle(!!opts.arrows);
            } else {
                o_nav = false;
                $arrs.hide();
            }

            arrsUpdate();
            stageWheelUpdate();
            thumbArrUpdate();
            if (opts.autoplay) setAutoplayInterval(opts.autoplay);

            o_thumbSide = numberFromMeasure(opts.thumbwidth) || THUMB_SIZE;
            o_thumbSide2 = numberFromMeasure(opts.thumbheight) || THUMB_SIZE;

            stageWheelTail.ok = navWheelTail.ok = opts.trackpad && !SLOW;

            stageNoMove();

            extendMeasures(opts, [measures]);

            o_navThumbs = o_nav === 'thumbs';

            if ($navWrap.filter(':hidden') && !!o_nav) {
                $navWrap.show();
            }
            if (o_navThumbs) {
                frameDraw(size, 'navThumb');

                $navFrame = $navThumbFrame;
                navFrameKey = NAV_THUMB_FRAME_KEY;

                setStyle($style, $.Fotorama.jst.style({
                    w: o_thumbSide,
                    h: o_thumbSide2,
                    b: opts.thumbborderwidth,
                    m: opts.thumbmargin,
                    s: stamp,
                    q: !COMPAT
                }));

                $nav
                    .addClass(navThumbsClass)
                    .removeClass(navDotsClass);
            } else if (o_nav === 'dots') {
                frameDraw(size, 'navDot');

                $navFrame = $navDotFrame;
                navFrameKey = NAV_DOT_FRAME_KEY;

                $nav
                    .addClass(navDotsClass)
                    .removeClass(navThumbsClass);
            } else {
                $navWrap.hide();
                o_nav = false;
                $nav.removeClass(navThumbsClass + ' ' + navDotsClass);
            }

            if (o_nav) {
                if (o_navTop) {
                    $navWrap.insertBefore($stage);
                } else {
                    $navWrap.insertAfter($stage);
                }
                frameAppend.nav = false;

                frameAppend($navFrame, $navShaft, 'nav');
            }

            o_allowFullScreen = opts.allowfullscreen;

            if (o_allowFullScreen) {
                $fullscreenIcon.prependTo($stage);
                o_nativeFullScreen = FULLSCREEN && o_allowFullScreen === 'native';
            } else {
                $fullscreenIcon.detach();
                o_nativeFullScreen = false;
            }

            addOrRemoveClass(o_fade, wrapFadeClass);
            addOrRemoveClass(!o_fade, wrapSlideClass);
            addOrRemoveClass(!opts.captions, wrapNoCaptionsClass);
            addOrRemoveClass(o_rtl, wrapRtlClass);
            addOrRemoveClass(opts.arrows, wrapToggleArrowsClass);

            o_shadows = opts.shadows && !SLOW;
            addOrRemoveClass(!o_shadows, wrapNoShadowsClass);

            $wrap
                .addClass(classes.add.join(' '))
                .removeClass(classes.remove.join(' '));

            lastOptions = $.extend({}, opts);
            setStagePosition();
        }

        function normalizeIndex(index) {
            return index < 0 ? (size + (index % size)) % size : index >= size ? index % size : index;
        }

        function limitIndex(index) {
            return minMaxLimit(index, 0, size - 1);
        }

        function edgeIndex(index) {
            return o_loop ? normalizeIndex(index) : limitIndex(index);
        }

        function getPrevIndex(index) {
            return index > 0 || o_loop ? index - 1 : false;
        }

        function getNextIndex(index) {
            return index < size - 1 || o_loop ? index + 1 : false;
        }

        function setStageShaftMinmaxAndSnap() {
            stageShaftTouchTail.min = o_loop ? -Infinity : -getPosByIndex(size - 1, measures.w, opts.margin, repositionIndex);
            stageShaftTouchTail.max = o_loop ? Infinity : -getPosByIndex(0, measures.w, opts.margin, repositionIndex);
            stageShaftTouchTail.snap = measures.w + opts.margin;
        }

        function setNavShaftMinMax() {

            var isVerticalDir = (opts.navdir === 'vertical');
            var param = isVerticalDir ? $navShaft.height() : $navShaft.width();
            var mainParam = isVerticalDir ? measures.h : measures.nw;
            navShaftTouchTail.min = Math.min(0, mainParam - param);
            navShaftTouchTail.max = 0;
            navShaftTouchTail.direction = opts.navdir;
            $navShaft.toggleClass(grabClass, !(navShaftTouchTail.noMove = navShaftTouchTail.min === navShaftTouchTail.max));
        }

        function eachIndex(indexes, type, fn) {
            if (typeof indexes === 'number') {
                indexes = new Array(indexes);
                var rangeFLAG = true;
            }
            return $.each(indexes, function (i, index) {
                if (rangeFLAG) index = i;
                if (typeof index === 'number') {
                    var dataFrame = data[normalizeIndex(index)];

                    if (dataFrame) {
                        var key = '$' + type + 'Frame',
                            $frame = dataFrame[key];

                        fn.call(this, i, index, dataFrame, $frame, key, $frame && $frame.data());
                    }
                }
            });
        }

        function setMeasures(width, height, ratio, index) {
            if (!measuresSetFLAG || (measuresSetFLAG === '*' && index === startIndex)) {

                width = measureIsValid(opts.width) || measureIsValid(width) || WIDTH;
                height = measureIsValid(opts.height) || measureIsValid(height) || HEIGHT;
                that.resize({
                    width: width,
                    ratio: opts.ratio || ratio || width / height
                }, 0, index !== startIndex && '*');
            }
        }

        function loadImg(indexes, type, specialMeasures, again) {

            eachIndex(indexes, type, function (i, index, dataFrame, $frame, key, frameData) {

                if (!$frame) return;

                var fullFLAG = that.fullScreen && !frameData.$full && type === 'stage';

                if (frameData.$img && !again && !fullFLAG) return;

                var img = new Image(),
                    $img = $(img),
                    imgData = $img.data();

                frameData[fullFLAG ? '$full' : '$img'] = $img;

                var srcKey = type === 'stage' ? (fullFLAG ? 'full' : 'img') : 'thumb',
                    src = dataFrame[srcKey],
                    dummy = fullFLAG ? dataFrame['img'] : dataFrame[type === 'stage' ? 'thumb' : 'img'];

                if (type === 'navThumb') $frame = frameData.$wrap;

                function triggerTriggerEvent(event) {
                    var _index = normalizeIndex(index);
                    triggerEvent(event, {
                        index: _index,
                        src: src,
                        frame: data[_index]
                    });
                }

                function error() {
                    $img.remove();

                    $.Fotorama.cache[src] = 'error';

                    if ((!dataFrame.html || type !== 'stage') && dummy && dummy !== src) {
                        dataFrame[srcKey] = src = dummy;
                        frameData.$full = null;
                        loadImg([index], type, specialMeasures, true);
                    } else {
                        if (src && !dataFrame.html && !fullFLAG) {
                            $frame
                                .trigger('f:error')
                                .removeClass(loadingClass)
                                .addClass(errorClass);

                            triggerTriggerEvent('error');
                        } else if (type === 'stage') {
                            $frame
                                .trigger('f:load')
                                .removeClass(loadingClass + ' ' + errorClass)
                                .addClass(loadedClass);

                            triggerTriggerEvent('load');
                            setMeasures();
                        }

                        frameData.state = 'error';

                        if (size > 1 && data[index] === dataFrame && !dataFrame.html && !dataFrame.deleted && !dataFrame.video && !fullFLAG) {
                            dataFrame.deleted = true;
                            that.splice(index, 1);
                        }
                    }
                }

                function loaded() {
                    $.Fotorama.measures[src] = imgData.measures = $.Fotorama.measures[src] || {
                            width: img.width,
                            height: img.height,
                            ratio: img.width / img.height
                        };

                    setMeasures(imgData.measures.width, imgData.measures.height, imgData.measures.ratio, index);

                    $img
                        .off('load error')
                        .addClass('' + (fullFLAG ? imgFullClass: imgClass))
                        .attr('aria-hidden', 'false')
                        .prependTo($frame);

                    if ($frame.hasClass(stageFrameClass) && !$frame.hasClass(videoContainerClass)) {
                        $frame.attr("href", $img.attr("src"));
                    }

                    fit($img, (
                            $.isFunction(specialMeasures) ? specialMeasures() : specialMeasures) || measures);

                    $.Fotorama.cache[src] = frameData.state = 'loaded';

                    setTimeout(function () {
                        $frame
                            .trigger('f:load')
                            .removeClass(loadingClass + ' ' + errorClass)
                            .addClass(loadedClass + ' ' + (fullFLAG ? loadedFullClass : loadedImgClass));

                        if (type === 'stage') {
                            triggerTriggerEvent('load');
                        } else if (dataFrame.thumbratio === AUTO || !dataFrame.thumbratio && opts.thumbratio === AUTO) {
                            // danger! reflow for all thumbnails
                            dataFrame.thumbratio = imgData.measures.ratio;
                            reset();
                        }
                    }, 0);
                }

                if (!src) {
                    error();
                    return;
                }

                function waitAndLoad() {
                    var _i = 10;
                    waitFor(function () {
                        return !touchedFLAG || !_i-- && !SLOW;
                    }, function () {
                        loaded();
                    });
                }

                if (!$.Fotorama.cache[src]) {
                    $.Fotorama.cache[src] = '*';

                    $img
                        .on('load', waitAndLoad)
                        .on('error', error);
                } else {
                    (function justWait() {
                        if ($.Fotorama.cache[src] === 'error') {
                            error();
                        } else if ($.Fotorama.cache[src] === 'loaded') {
                            setTimeout(waitAndLoad, 0);
                        } else {
                            setTimeout(justWait, 100);
                        }
                    })();
                }

                frameData.state = '';
                img.src = src;

                if (frameData.data.caption) {
                    img.alt = frameData.data.caption || "";
                }

                if (frameData.data.full) {
                    $(img).data('original', frameData.data.full);
                }

                if (UTIL.isExpectedCaption(dataFrame, opts.showcaption)) {
                    $(img).attr('aria-labelledby', dataFrame.labelledby);
                }
            });
        }

        function updateFotoramaState() {
            var $frame = activeFrame[STAGE_FRAME_KEY];

            if ($frame && !$frame.data().state) {
                $spinner.addClass(spinnerShowClass);
                $frame.on('f:load f:error', function () {
                    $frame.off('f:load f:error');
                    $spinner.removeClass(spinnerShowClass);
                });
            }
        }

        function addNavFrameEvents(frame) {
            addEnterUp(frame, onNavFrameClick);
            addFocus(frame, function () {

                setTimeout(function () {
                    lockScroll($nav);
                }, 0);
                slideNavShaft({time: o_transitionDuration, guessIndex: $(this).data().eq, minMax: navShaftTouchTail});
            });
        }

        function frameDraw(indexes, type) {
            eachIndex(indexes, type, function (i, index, dataFrame, $frame, key, frameData) {
                if ($frame) return;

                $frame = dataFrame[key] = $wrap[key].clone();
                frameData = $frame.data();
                frameData.data = dataFrame;
                var frame = $frame[0],
                    labelledbyValue = "labelledby" + $.now();

                if (type === 'stage') {

                    if (dataFrame.html) {
                        $('<div class="' + htmlClass + '"></div>')
                            .append(
                            dataFrame._html ? $(dataFrame.html)
                                .removeAttr('id')
                                .html(dataFrame._html) // Because of IE
                                : dataFrame.html
                        )
                            .appendTo($frame);
                    }

                    if (dataFrame.id) {
                        labelledbyValue = dataFrame.id || labelledbyValue;
                    }
                    dataFrame.labelledby = labelledbyValue;

                    if (UTIL.isExpectedCaption(dataFrame, opts.showcaption)) {
                        $($.Fotorama.jst.frameCaption({
                            caption: dataFrame.caption,
                            labelledby: labelledbyValue
                        })).appendTo($frame);
                    }

                    dataFrame.video && $frame
                        .addClass(stageFrameVideoClass)
                        .append($videoPlay.clone());

                    // This solves tabbing problems
                    addFocus(frame, function (e) {
                        setTimeout(function () {
                            lockScroll($stage);
                        }, 0);
                        clickToShow({index: frameData.eq, user: true}, e);
                    });

                    $stageFrame = $stageFrame.add($frame);
                } else if (type === 'navDot') {
                    addNavFrameEvents(frame);
                    $navDotFrame = $navDotFrame.add($frame);
                } else if (type === 'navThumb') {
                    addNavFrameEvents(frame);
                    frameData.$wrap = $frame.children(':first');

                    $navThumbFrame = $navThumbFrame.add($frame);
                    if (dataFrame.video) {
                        frameData.$wrap.append($videoPlay.clone());
                    }
                }
            });
        }

        function callFit($img, measuresToFit) {
            return $img && $img.length && fit($img, measuresToFit);
        }

        function stageFramePosition(indexes) {
            eachIndex(indexes, 'stage', function (i, index, dataFrame, $frame, key, frameData) {
                if (!$frame) return;

                var normalizedIndex = normalizeIndex(index);
                frameData.eq = normalizedIndex;

                toDetach[STAGE_FRAME_KEY][normalizedIndex] = $frame.css($.extend({left: o_fade ? 0 : getPosByIndex(index, measures.w, opts.margin, repositionIndex)}, o_fade && getDuration(0)));

                if (isDetached($frame[0])) {
                    $frame.appendTo($stageShaft);
                    unloadVideo(dataFrame.$video);
                }

                callFit(frameData.$img, measures);
                callFit(frameData.$full, measures);

                if ($frame.hasClass(stageFrameClass) && !($frame.attr('aria-hidden') === "false" && $frame.hasClass(activeClass))) {
                    $frame.attr('aria-hidden', 'true');
                }
            });
        }

        function thumbsDraw(pos, loadFLAG) {
            var leftLimit,
                rightLimit,
                exceedLimit;


            if (o_nav !== 'thumbs' || isNaN(pos)) return;

            leftLimit = -pos;
            rightLimit = -pos + measures.nw;

            if (opts.navdir === 'vertical') {
                pos = pos - opts.thumbheight;
                rightLimit = -pos + measures.h;
            }

            $navThumbFrame.each(function () {
                var $this = $(this),
                    thisData = $this.data(),
                    eq = thisData.eq,
                    getSpecialMeasures = function () {
                        return {
                            h: o_thumbSide2,
                            w: thisData.w
                        }
                    },
                    specialMeasures = getSpecialMeasures(),
                    exceedLimit = opts.navdir === 'vertical' ?
                    thisData.t > rightLimit : thisData.l > rightLimit;
                specialMeasures.w = thisData.w;

                if ((opts.navdir !== 'vertical' && thisData.l + thisData.w < leftLimit)
                    || exceedLimit
                    || callFit(thisData.$img, specialMeasures)) return;

                loadFLAG && loadImg([eq], 'navThumb', getSpecialMeasures);
            });
        }

        function frameAppend($frames, $shaft, type) {
            if (!frameAppend[type]) {

                var thumbsFLAG = type === 'nav' && o_navThumbs,
                    left = 0,
                    top = 0;

                $shaft.append(
                    $frames
                        .filter(function () {
                            var actual,
                                $this = $(this),
                                frameData = $this.data();
                            for (var _i = 0, _l = data.length; _i < _l; _i++) {
                                if (frameData.data === data[_i]) {
                                    actual = true;
                                    frameData.eq = _i;
                                    break;
                                }
                            }
                            return actual || $this.remove() && false;
                        })
                        .sort(function (a, b) {
                            return $(a).data().eq - $(b).data().eq;
                        })
                        .each(function () {
                            var $this = $(this),
                                frameData = $this.data();
                            UTIL.setThumbAttr($this, frameData.data.caption, "aria-label");
                        })
                        .each(function () {

                            if (!thumbsFLAG) return;

                            var $this = $(this),
                                frameData = $this.data(),
                                thumbwidth = Math.round(o_thumbSide2 * frameData.data.thumbratio) || o_thumbSide,
                                thumbheight = Math.round(o_thumbSide / frameData.data.thumbratio) || o_thumbSide2;
                            frameData.t = top;
                            frameData.h = thumbheight;
                            frameData.l = left;
                            frameData.w = thumbwidth;

                            $this.css({width: thumbwidth});

                            top += thumbheight + opts.thumbmargin;
                            left += thumbwidth + opts.thumbmargin;
                        })
                );

                frameAppend[type] = true;
            }
        }

        function getDirection(x) {
            return x - stageLeft > measures.w / 3;
        }

        function disableDirrection(i) {
            return !o_loop && (!(activeIndex + i) || !(activeIndex - size + i)) && !$videoPlaying;
        }

        function arrsUpdate() {
            var disablePrev = disableDirrection(0),
                disableNext = disableDirrection(1);
            $arrPrev
                .toggleClass(arrDisabledClass, disablePrev)
                .attr(disableAttr(disablePrev, false));
            $arrNext
                .toggleClass(arrDisabledClass, disableNext)
                .attr(disableAttr(disableNext, false));
        }

        function thumbArrUpdate() {
            var isLeftDisable = false,
                isRightDisable = false;
            if (opts.navtype === 'thumbs' && !opts.loop) {
                (activeIndex == 0) ? isLeftDisable = true : isLeftDisable = false;
                (activeIndex == opts.data.length - 1) ? isRightDisable = true : isRightDisable = false;
            }
            if (opts.navtype === 'slides') {
                var pos = readPosition($navShaft, opts.navdir);
                pos >= navShaftTouchTail.max ? isLeftDisable = true : isLeftDisable = false;
                pos <= navShaftTouchTail.min ? isRightDisable = true : isRightDisable = false;
            }
            $thumbArrLeft
                .toggleClass(arrDisabledClass, isLeftDisable)
                .attr(disableAttr(isLeftDisable, true));
            $thumbArrRight
                .toggleClass(arrDisabledClass, isRightDisable)
                .attr(disableAttr(isRightDisable, true));
        }

        function stageWheelUpdate() {
            if (stageWheelTail.ok) {
                stageWheelTail.prevent = {'<': disableDirrection(0), '>': disableDirrection(1)};
            }
        }

        function getNavFrameBounds($navFrame) {
            var navFrameData = $navFrame.data(),
                left,
                top,
                width,
                height;

            if (o_navThumbs) {
                left = navFrameData.l;
                top = navFrameData.t;
                width = navFrameData.w;
                height = navFrameData.h;
            } else {
                left = $navFrame.position().left;
                width = $navFrame.width();
            }

            var horizontalBounds = {
                c: left + width / 2,
                min: -left + opts.thumbmargin * 10,
                max: -left + measures.w - width - opts.thumbmargin * 10
            };

            var verticalBounds = {
                c: top + height / 2,
                min: -top + opts.thumbmargin * 10,
                max: -top + measures.h - height - opts.thumbmargin * 10
            };

            return opts.navdir === 'vertical' ? verticalBounds : horizontalBounds;
        }

        function slideThumbBorder(time) {
            var navFrameData = activeFrame[navFrameKey].data();
            slide($thumbBorder, {
                time: time * 1.2,
                pos: (opts.navdir === 'vertical' ? navFrameData.t : navFrameData.l),
                width: navFrameData.w,
                height: navFrameData.h,
                direction: opts.navdir
            });
        }

        function slideNavShaft(options) {
            var $guessNavFrame = data[options.guessIndex][navFrameKey],
                typeOfAnimation = opts.navtype;

            var overflowFLAG,
                time,
                minMax,
                boundTop,
                boundLeft,
                l,
                pos,
                x;

            if ($guessNavFrame) {
                if (typeOfAnimation === 'thumbs') {
                    overflowFLAG = navShaftTouchTail.min !== navShaftTouchTail.max;
                    minMax = options.minMax || overflowFLAG && getNavFrameBounds(activeFrame[navFrameKey]);
                    boundTop = overflowFLAG && (options.keep && slideNavShaft.t ? slideNavShaft.l : minMaxLimit((options.coo || measures.nw / 2) - getNavFrameBounds($guessNavFrame).c, minMax.min, minMax.max));
                    boundLeft = overflowFLAG && (options.keep && slideNavShaft.l ? slideNavShaft.l : minMaxLimit((options.coo || measures.nw / 2) - getNavFrameBounds($guessNavFrame).c, minMax.min, minMax.max));
                    l = (opts.navdir === 'vertical' ? boundTop : boundLeft);
                    pos = overflowFLAG && minMaxLimit(l, navShaftTouchTail.min, navShaftTouchTail.max) || 0;
                    time = options.time * 1.1;
                    slide($navShaft, {
                        time: time,
                        pos: pos,
                        direction: opts.navdir,
                        onEnd: function () {
                            thumbsDraw(pos, true);
                            thumbArrUpdate();
                        }
                    });

                    setShadow($nav, findShadowEdge(pos, navShaftTouchTail.min, navShaftTouchTail.max, opts.navdir));
                    slideNavShaft.l = l;
                } else {
                    x = readPosition($navShaft, opts.navdir);
                    time = options.time * 1.11;

                    pos = validateSlidePos(opts, navShaftTouchTail, options.guessIndex, x, $guessNavFrame, $navWrap, opts.navdir);

                    slide($navShaft, {
                        time: time,
                        pos: pos,
                        direction: opts.navdir,
                        onEnd: function () {
                            thumbsDraw(pos, true);
                            thumbArrUpdate();
                        }
                    });
                    setShadow($nav, findShadowEdge(pos, navShaftTouchTail.min, navShaftTouchTail.max, opts.navdir));
                }
            }
        }

        function navUpdate() {
            deactivateFrames(navFrameKey);
            toDeactivate[navFrameKey].push(activeFrame[navFrameKey].addClass(activeClass).attr('data-active', true));
        }

        function deactivateFrames(key) {
            var _toDeactivate = toDeactivate[key];

            while (_toDeactivate.length) {
                _toDeactivate.shift().removeClass(activeClass).attr('data-active', false);
            }
        }

        function detachFrames(key) {
            var _toDetach = toDetach[key];

            $.each(activeIndexes, function (i, index) {
                delete _toDetach[normalizeIndex(index)];
            });

            $.each(_toDetach, function (index, $frame) {
                delete _toDetach[index];
                $frame.detach();
            });
        }

        function stageShaftReposition(skipOnEnd) {

            repositionIndex = dirtyIndex = activeIndex;

            var $frame = activeFrame[STAGE_FRAME_KEY];

            if ($frame) {
                deactivateFrames(STAGE_FRAME_KEY);
                toDeactivate[STAGE_FRAME_KEY].push($frame.addClass(activeClass).attr('data-active', true));

                if ($frame.hasClass(stageFrameClass)) {
                    $frame.attr('aria-hidden', 'false');
                }

                skipOnEnd || that.showStage.onEnd(true);
                stop($stageShaft, 0, true);

                detachFrames(STAGE_FRAME_KEY);
                stageFramePosition(activeIndexes);
                setStageShaftMinmaxAndSnap();
                setNavShaftMinMax();
                addEnterUp($stageShaft[0], function () {
                    if (!$fotorama.hasClass(fullscreenClass)) {
                        that.requestFullScreen();
                        $fullscreenIcon.focus();
                    }
                });
            }
        }

        function extendMeasures(options, measuresArray) {
            if (!options) return;

            $.each(measuresArray, function (i, measures) {
                if (!measures) return;

                $.extend(measures, {
                    width: options.width || measures.width,
                    height: options.height,
                    minwidth: options.minwidth,
                    maxwidth: options.maxwidth,
                    minheight: options.minheight,
                    maxheight: options.maxheight,
                    ratio: getRatio(options.ratio)
                })
            });
        }

        function triggerEvent(event, extra) {
            $fotorama.trigger(_fotoramaClass + ':' + event, [that, extra]);
        }

        function onTouchStart() {
            clearTimeout(onTouchEnd.t);
            touchedFLAG = 1;

            if (opts.stopautoplayontouch) {
                that.stopAutoplay();
            } else {
                pausedAutoplayFLAG = true;
            }
        }

        function onTouchEnd() {
            if (!touchedFLAG) return;
            if (!opts.stopautoplayontouch) {
                releaseAutoplay();
                changeAutoplay();
            }

            onTouchEnd.t = setTimeout(function () {
                touchedFLAG = 0;
            }, TRANSITION_DURATION + TOUCH_TIMEOUT);
        }

        function releaseAutoplay() {
            pausedAutoplayFLAG = !!($videoPlaying || stoppedAutoplayFLAG);
        }

        function changeAutoplay() {

            clearTimeout(changeAutoplay.t);
            waitFor.stop(changeAutoplay.w);

            if (!opts.autoplay || pausedAutoplayFLAG) {
                if (that.autoplay) {
                    that.autoplay = false;
                    triggerEvent('stopautoplay');
                }

                return;
            }

            if (!that.autoplay) {
                that.autoplay = true;
                triggerEvent('startautoplay');
            }

            var _activeIndex = activeIndex;


            var frameData = activeFrame[STAGE_FRAME_KEY].data();
            changeAutoplay.w = waitFor(function () {
                return frameData.state || _activeIndex !== activeIndex;
            }, function () {
                changeAutoplay.t = setTimeout(function () {

                    if (pausedAutoplayFLAG || _activeIndex !== activeIndex) return;

                    var _nextAutoplayIndex = nextAutoplayIndex,
                        nextFrameData = data[_nextAutoplayIndex][STAGE_FRAME_KEY].data();

                    changeAutoplay.w = waitFor(function () {

                        return nextFrameData.state || _nextAutoplayIndex !== nextAutoplayIndex;
                    }, function () {
                        if (pausedAutoplayFLAG || _nextAutoplayIndex !== nextAutoplayIndex) return;
                        that.show(o_loop ? getDirectionSign(!o_rtl) : nextAutoplayIndex);
                    });
                }, opts.autoplay);
            });

        }

        that.startAutoplay = function (interval) {
            if (that.autoplay) return this;
            pausedAutoplayFLAG = stoppedAutoplayFLAG = false;
            setAutoplayInterval(interval || opts.autoplay);
            changeAutoplay();

            return this;
        };

        that.stopAutoplay = function () {
            if (that.autoplay) {
                pausedAutoplayFLAG = stoppedAutoplayFLAG = true;
                changeAutoplay();
            }
            return this;
        };

        that.showSlide = function (slideDir) {
            var currentPosition = readPosition($navShaft, opts.navdir),
                pos,
                time = 500 * 1.1,
                size = opts.navdir === 'horizontal' ? opts.thumbwidth : opts.thumbheight,
                onEnd = function () {
                    thumbArrUpdate();
                };
            if (slideDir === 'next') {
                pos = currentPosition - (size + opts.margin) * thumbsPerSlide;
            }
            if (slideDir === 'prev') {
                pos = currentPosition + (size + opts.margin) * thumbsPerSlide;
            }
            pos = validateRestrictions(pos, navShaftTouchTail);
            thumbsDraw(pos, true);
            slide($navShaft, {
                time: time,
                pos: pos,
                direction: opts.navdir,
                onEnd: onEnd
            });
        };

        that.showWhileLongPress = function (options) {
            if (that.longPress.singlePressInProgress) {
                return;
            }

            var index = calcActiveIndex(options);
            calcGlobalIndexes(index);
            var time = calcTime(options) / 50;
            var _activeFrame = activeFrame;
            that.activeFrame = activeFrame = data[activeIndex];
            var silent = _activeFrame === activeFrame && !options.user;

            that.showNav(silent, options, time);

            return this;
        };

        that.showEndLongPress = function (options) {
            if (that.longPress.singlePressInProgress) {
                return;
            }

            var index = calcActiveIndex(options);
            calcGlobalIndexes(index);
            var time = calcTime(options) / 50;
            var _activeFrame = activeFrame;
            that.activeFrame = activeFrame = data[activeIndex];

            var silent = _activeFrame === activeFrame && !options.user;

            that.showStage(silent, options, time);

            showedFLAG = typeof lastActiveIndex !== 'undefined' && lastActiveIndex !== activeIndex;
            lastActiveIndex = activeIndex;
            return this;
        };

        function calcActiveIndex (options) {
            var index;

            if (typeof options !== 'object') {
                index = options;
                options = {};
            } else {
                index = options.index;
            }

            index = index === '>' ? dirtyIndex + 1 : index === '<' ? dirtyIndex - 1 : index === '<<' ? 0 : index === '>>' ? size - 1 : index;
            index = isNaN(index) ? undefined : index;
            index = typeof index === 'undefined' ? activeIndex || 0 : index;

            return index;
        }

        function calcGlobalIndexes (index) {
            that.activeIndex = activeIndex = edgeIndex(index);
            prevIndex = getPrevIndex(activeIndex);
            nextIndex = getNextIndex(activeIndex);
            nextAutoplayIndex = normalizeIndex(activeIndex + (o_rtl ? -1 : 1));
            activeIndexes = [activeIndex, prevIndex, nextIndex];

            dirtyIndex = o_loop ? index : activeIndex;
        }

        function calcTime (options) {
            var diffIndex = Math.abs(lastActiveIndex - dirtyIndex),
                time = getNumber(options.time, function () {
                    return Math.min(o_transitionDuration * (1 + (diffIndex - 1) / 12), o_transitionDuration * 2);
                });

            if (options.slow) {
                time *= 10;
            }

            return time;
        }

        that.showStage = function (silent, options, time, e) {
            if (e !== undefined && e.target.tagName == 'IFRAME') {
                return;
            }
            unloadVideo($videoPlaying, activeFrame.i !== data[normalizeIndex(repositionIndex)].i);
            frameDraw(activeIndexes, 'stage');
            stageFramePosition(SLOW ? [dirtyIndex] : [dirtyIndex, getPrevIndex(dirtyIndex), getNextIndex(dirtyIndex)]);
            updateTouchTails('go', true);

            silent || triggerEvent('show', {
                user: options.user,
                time: time
            });

            pausedAutoplayFLAG = true;

            var overPos = options.overPos;
            var onEnd = that.showStage.onEnd = function (skipReposition) {
                if (onEnd.ok) return;
                onEnd.ok = true;

                skipReposition || stageShaftReposition(true);

                if (!silent) {
                    triggerEvent('showend', {
                        user: options.user
                    });
                }

                if (!skipReposition && o_transition && o_transition !== opts.transition) {
                    that.setOptions({transition: o_transition});
                    o_transition = false;
                    return;
                }

                updateFotoramaState();
                loadImg(activeIndexes, 'stage');

                updateTouchTails('go', false);
                stageWheelUpdate();

                stageCursor();
                releaseAutoplay();
                changeAutoplay();

                if (that.fullScreen) {
                    activeFrame[STAGE_FRAME_KEY].find('.' + imgFullClass).attr('aria-hidden', false);
                    activeFrame[STAGE_FRAME_KEY].find('.' + imgClass).attr('aria-hidden', true)
                } else {
                    activeFrame[STAGE_FRAME_KEY].find('.' + imgFullClass).attr('aria-hidden', true);
                    activeFrame[STAGE_FRAME_KEY].find('.' + imgClass).attr('aria-hidden', false)
                }
            };

            if (!o_fade) {
                slide($stageShaft, {
                    pos: -getPosByIndex(dirtyIndex, measures.w, opts.margin, repositionIndex),
                    overPos: overPos,
                    time: time,
                    onEnd: onEnd
                });
            } else {
                var $activeFrame = activeFrame[STAGE_FRAME_KEY],
                    $prevActiveFrame = data[lastActiveIndex] && activeIndex !== lastActiveIndex ? data[lastActiveIndex][STAGE_FRAME_KEY] : null;

                fade($activeFrame, $prevActiveFrame, $stageFrame, {
                    time: time,
                    method: opts.transition,
                    onEnd: onEnd
                }, fadeStack);
            }

            arrsUpdate();
        };

        that.showNav = function(silent, options, time){
            thumbArrUpdate();
            if (o_nav) {
                navUpdate();

                var guessIndex = limitIndex(activeIndex + minMaxLimit(dirtyIndex - lastActiveIndex, -1, 1));
                slideNavShaft({
                    time: time,
                    coo: guessIndex !== activeIndex && options.coo,
                    guessIndex: typeof options.coo !== 'undefined' ? guessIndex : activeIndex,
                    keep: silent
                });
                if (o_navThumbs) slideThumbBorder(time);
            }
        };

        that.show = function (options, e) {
            that.longPress.singlePressInProgress = true;

            var index = calcActiveIndex(options);
            calcGlobalIndexes(index);
            var time = calcTime(options);
            var _activeFrame = activeFrame;
            that.activeFrame = activeFrame = data[activeIndex];

            var silent = _activeFrame === activeFrame && !options.user;

            that.showStage(silent, options, time, e);
            that.showNav(silent, options, time);

            showedFLAG = typeof lastActiveIndex !== 'undefined' && lastActiveIndex !== activeIndex;
            lastActiveIndex = activeIndex;
            that.longPress.singlePressInProgress = false;

            return this;
        };

        that.requestFullScreen = function () {
            if (o_allowFullScreen && !that.fullScreen) {

                //check that this is not video
                if(isVideo()) {
                    return;
                }

                scrollTop = $WINDOW.scrollTop();
                scrollLeft = $WINDOW.scrollLeft();

                lockScroll($WINDOW);

                updateTouchTails('x', true);

                measuresStash = $.extend({}, measures);

                $fotorama
                    .addClass(fullscreenClass)
                    .appendTo($BODY.addClass(_fullscreenClass));

                $HTML.addClass(_fullscreenClass);

                unloadVideo($videoPlaying, true, true);

                that.fullScreen = true;

                if (o_nativeFullScreen) {
                    fullScreenApi.request(fotorama);
                }

                loadImg(activeIndexes, 'stage');
                updateFotoramaState();
                triggerEvent('fullscreenenter');
                that.resize();

                if (!('ontouchstart' in window)) {
                    $fullscreenIcon.focus();
                }
            }

            return this;
        };

        function cancelFullScreen() {
            if (that.fullScreen) {
                that.fullScreen = false;

                if (FULLSCREEN) {
                    fullScreenApi.cancel(fotorama);
                }

                $BODY.removeClass(_fullscreenClass);
                $HTML.removeClass(_fullscreenClass);

                $fotorama
                    .removeClass(fullscreenClass)
                    .insertAfter($anchor);

                measures = $.extend({}, measuresStash);

                unloadVideo($videoPlaying, true, true);

                updateTouchTails('x', false);

                that.resize();
                loadImg(activeIndexes, 'stage');

                lockScroll($WINDOW, scrollLeft, scrollTop);

                triggerEvent('fullscreenexit');
            }
        }

        that.cancelFullScreen = function () {
            if (o_nativeFullScreen && fullScreenApi.is()) {
                fullScreenApi.cancel(document);
            } else {
                cancelFullScreen();
            }

            return this;
        };

        that.toggleFullScreen = function () {
            return that[(that.fullScreen ? 'cancel' : 'request') + 'FullScreen']();
        };

        that.resize = function (options) {
            if (!data) return this;

            var time = arguments[1] || 0,
                setFLAG = arguments[2];

            thumbsPerSlide = getThumbsInSlide($wrap, opts);
            extendMeasures(!that.fullScreen ? optionsToLowerCase(options) : {
                width: $(window).width(),
                maxwidth: null,
                minwidth: null,
                height: $(window).height(),
                maxheight: null,
                minheight: null
            }, [measures, setFLAG || that.fullScreen || opts]);

            var width = measures.width,
                height = measures.height,
                ratio = measures.ratio,
                windowHeight = $WINDOW.height() - (o_nav ? $nav.height() : 0);

            if (measureIsValid(width)) {
                $wrap.css({width: ''});
                $stage.css({width: ''});
                $stageShaft.css({width: ''});
                $nav.css({width: ''});
                $wrap.css({minWidth: measures.minwidth || 0, maxWidth: measures.maxwidth || MAX_WIDTH});

                if (o_nav === 'dots') {
                    $navWrap.hide();
                }
                width = measures.W = measures.w = $wrap.width();
                measures.nw = o_nav && numberFromWhatever(opts.navwidth, width) || width;

                $stageShaft.css({width: measures.w, marginLeft: (measures.W - measures.w) / 2});

                height = numberFromWhatever(height, windowHeight);

                height = height || (ratio && width / ratio);

                if (height) {
                    width = Math.round(width);
                    height = measures.h = Math.round(minMaxLimit(height, numberFromWhatever(measures.minheight, windowHeight), numberFromWhatever(measures.maxheight, windowHeight)));
                    $stage.css({'width': width, 'height': height});

                    if (opts.navdir === 'vertical' && !that.fullscreen) {
                        $nav.width(opts.thumbwidth + opts.thumbmargin * 2);
                    }

                    if (opts.navdir === 'horizontal' && !that.fullscreen) {
                        $nav.height(opts.thumbheight + opts.thumbmargin * 2);
                    }

                    if (o_nav === 'dots') {
                        $nav.width(width)
                            .height('auto');
                        $navWrap.show();
                    }

                    if (opts.navdir === 'vertical' && that.fullScreen) {
                        $stage.css('height', $WINDOW.height());
                    }

                    if (opts.navdir === 'horizontal' && that.fullScreen) {
                        $stage.css('height', $WINDOW.height() - $nav.height());
                    }

                    if (o_nav) {
                        switch (opts.navdir) {
                            case 'vertical':
                                $navWrap.removeClass(navShafthorizontalClass);
                                $navWrap.removeClass(navShaftListClass);
                                $navWrap.addClass(navShaftVerticalClass);
                                $nav
                                    .stop()
                                    .animate({height: measures.h, width: opts.thumbwidth}, time);
                                break;
                            case 'list':
                                $navWrap.removeClass(navShaftVerticalClass);
                                $navWrap.removeClass(navShafthorizontalClass);
                                $navWrap.addClass(navShaftListClass);
                                break;
                            default:
                                $navWrap.removeClass(navShaftVerticalClass);
                                $navWrap.removeClass(navShaftListClass);
                                $navWrap.addClass(navShafthorizontalClass);
                                $nav
                                    .stop()
                                    .animate({width: measures.nw}, time);
                                break;
                        }

                        stageShaftReposition();
                        slideNavShaft({guessIndex: activeIndex, time: time, keep: true});
                        if (o_navThumbs && frameAppend.nav) slideThumbBorder(time);
                    }

                    measuresSetFLAG = setFLAG || true;

                    ready.ok = true;
                    ready();
                }
            }

            stageLeft = $stage.offset().left;
            setStagePosition();

            return this;
        };

        that.setOptions = function (options) {
            $.extend(opts, options);
            reset();
            return this;
        };

        that.shuffle = function () {
            data && shuffle(data) && reset();
            return this;
        };

        function setShadow($el, edge) {
            if (o_shadows) {
                $el.removeClass(shadowsLeftClass + ' ' + shadowsRightClass);
                $el.removeClass(shadowsTopClass + ' ' + shadowsBottomClass);
                edge && !$videoPlaying && $el.addClass(edge.replace(/^|\s/g, ' ' + shadowsClass + '--'));
            }
        }

        that.longPress = {
            threshold: 1,
            count: 0,
            thumbSlideTime: 20,
            progress: function(){
                if (!this.inProgress) {
                    this.count++;
                    this.inProgress = this.count > this.threshold;
                }
            },
            end: function(){
                if(this.inProgress) {
                    this.isEnded = true
                }
            },
            reset: function(){
                this.count = 0;
                this.inProgress = false;
                this.isEnded = false;
            }
        };

        that.destroy = function () {
            that.cancelFullScreen();
            that.stopAutoplay();

            data = that.data = null;

            appendElements();

            activeIndexes = [];
            detachFrames(STAGE_FRAME_KEY);

            reset.ok = false;

            return this;
        };

        /**
         *
         * @returns {jQuery.Fotorama}
         */
        that.playVideo = function () {
            var dataFrame = activeFrame,
                video = dataFrame.video,
                _activeIndex = activeIndex;

            if (typeof video === 'object' && dataFrame.videoReady) {
                o_nativeFullScreen && that.fullScreen && that.cancelFullScreen();

                waitFor(function () {
                    return !fullScreenApi.is() || _activeIndex !== activeIndex;
                }, function () {
                    if (_activeIndex === activeIndex) {
                        dataFrame.$video = dataFrame.$video || $(div(videoClass)).append(createVideoFrame(video));
                        dataFrame.$video.appendTo(dataFrame[STAGE_FRAME_KEY]);

                        $wrap.addClass(wrapVideoClass);
                        $videoPlaying = dataFrame.$video;

                        stageNoMove();

                        $arrs.blur();
                        $fullscreenIcon.blur();

                        triggerEvent('loadvideo');
                    }
                });
            }

            return this;
        };

        that.stopVideo = function () {
            unloadVideo($videoPlaying, true, true);
            return this;
        };

        that.spliceByIndex = function (index, newImgObj) {
            newImgObj.i = index + 1;
            newImgObj.img && $.ajax({
                url: newImgObj.img,
                type: 'HEAD',
                success: function () {
                    data.splice(index, 1, newImgObj);
                    reset();
                }
            });
        };

        function unloadVideo($video, unloadActiveFLAG, releaseAutoplayFLAG) {
            if (unloadActiveFLAG) {
                $wrap.removeClass(wrapVideoClass);
                $videoPlaying = false;

                stageNoMove();
            }

            if ($video && $video !== $videoPlaying) {
                $video.remove();
                triggerEvent('unloadvideo');
            }

            if (releaseAutoplayFLAG) {
                releaseAutoplay();
                changeAutoplay();
            }
        }

        function toggleControlsClass(FLAG) {
            $wrap.toggleClass(wrapNoControlsClass, FLAG);
        }

        function stageCursor(e) {
            if (stageShaftTouchTail.flow) return;

            var x = e ? e.pageX : stageCursor.x,
                pointerFLAG = x && !disableDirrection(getDirection(x)) && opts.click;

            if (stageCursor.p !== pointerFLAG
                && $stage.toggleClass(pointerClass, pointerFLAG)) {
                stageCursor.p = pointerFLAG;
                stageCursor.x = x;
            }
        }

        $stage.on('mousemove', stageCursor);

        function clickToShow(showOptions, e) {
            clearTimeout(clickToShow.t);

            if (opts.clicktransition && opts.clicktransition !== opts.transition) {
                setTimeout(function () {
                    var _o_transition = opts.transition;

                    that.setOptions({transition: opts.clicktransition});

                    // now safe to pass base transition to o_transition, so that.show will restor it
                    o_transition = _o_transition;
                    // this timeout is here to prevent jerking in some browsers
                    clickToShow.t = setTimeout(function () {
                        that.show(showOptions);
                    }, 10);
                }, 0);
            } else {
                that.show(showOptions, e);
            }
        }

        function onStageTap(e, toggleControlsFLAG) {
            var target = e.target,
                $target = $(target);
            if ($target.hasClass(videoPlayClass)) {
                that.playVideo();
            } else if (target === fullscreenIcon) {
                that.toggleFullScreen();
            } else if ($videoPlaying) {
                target === videoClose && unloadVideo($videoPlaying, true, true);
            } else if (!$fotorama.hasClass(fullscreenClass)) {
                that.requestFullScreen();
            }
        }

        function updateTouchTails(key, value) {
            stageShaftTouchTail[key] = navShaftTouchTail[key] = value;
        }

        stageShaftTouchTail = moveOnTouch($stageShaft, {
            onStart: onTouchStart,
            onMove: function (e, result) {
                setShadow($stage, result.edge);
            },
            onTouchEnd: onTouchEnd,
            onEnd: function (result) {
                var toggleControlsFLAG;

                setShadow($stage);
                toggleControlsFLAG = (MS_POINTER && !hoverFLAG || result.touch) &&
                    opts.arrows;

                if ((result.moved || (toggleControlsFLAG && result.pos !== result.newPos && !result.control)) && result.$target[0] !== $fullscreenIcon[0]) {
                    var index = getIndexByPos(result.newPos, measures.w, opts.margin, repositionIndex);

                    that.show({
                        index: index,
                        time: o_fade ? o_transitionDuration : result.time,
                        overPos: result.overPos,
                        user: true
                    });
                } else if (!result.aborted && !result.control) {
                    onStageTap(result.startEvent, toggleControlsFLAG);
                }
            },
            timeLow: 1,
            timeHigh: 1,
            friction: 2,
            select: '.' + selectClass + ', .' + selectClass + ' *',
            $wrap: $stage,
            direction: 'horizontal'

        });

        navShaftTouchTail = moveOnTouch($navShaft, {
            onStart: onTouchStart,
            onMove: function (e, result) {
                setShadow($nav, result.edge);
            },
            onTouchEnd: onTouchEnd,
            onEnd: function (result) {

                function onEnd() {
                    slideNavShaft.l = result.newPos;
                    releaseAutoplay();
                    changeAutoplay();
                    thumbsDraw(result.newPos, true);
                    thumbArrUpdate();
                }

                if (!result.moved) {
                    var target = result.$target.closest('.' + navFrameClass, $navShaft)[0];
                    target && onNavFrameClick.call(target, result.startEvent);
                } else if (result.pos !== result.newPos) {
                    pausedAutoplayFLAG = true;
                    slide($navShaft, {
                        time: result.time,
                        pos: result.newPos,
                        overPos: result.overPos,
                        direction: opts.navdir,
                        onEnd: onEnd
                    });
                    thumbsDraw(result.newPos);
                    o_shadows && setShadow($nav, findShadowEdge(result.newPos, navShaftTouchTail.min, navShaftTouchTail.max, result.dir));
                } else {
                    onEnd();
                }
            },
            timeLow: .5,
            timeHigh: 2,
            friction: 5,
            $wrap: $nav,
            direction: opts.navdir
        });

        stageWheelTail = wheel($stage, {
            shift: true,
            onEnd: function (e, direction) {
                onTouchStart();
                onTouchEnd();
                that.show({index: direction, slow: e.altKey})
            }
        });

        navWheelTail = wheel($nav, {
            onEnd: function (e, direction) {
                onTouchStart();
                onTouchEnd();
                var newPos = stop($navShaft) + direction * .25;
                $navShaft.css(getTranslate(minMaxLimit(newPos, navShaftTouchTail.min, navShaftTouchTail.max), opts.navdir));
                o_shadows && setShadow($nav, findShadowEdge(newPos, navShaftTouchTail.min, navShaftTouchTail.max, opts.navdir));
                navWheelTail.prevent = {'<': newPos >= navShaftTouchTail.max, '>': newPos <= navShaftTouchTail.min};
                clearTimeout(navWheelTail.t);
                navWheelTail.t = setTimeout(function () {
                    slideNavShaft.l = newPos;
                    thumbsDraw(newPos, true)
                }, TOUCH_TIMEOUT);
                thumbsDraw(newPos);
            }
        });

        $wrap.hover(
            function () {
                setTimeout(function () {
                    if (touchedFLAG) return;
                    toggleControlsClass(!(hoverFLAG = true));
                }, 0);
            },
            function () {
                if (!hoverFLAG) return;
                toggleControlsClass(!(hoverFLAG = false));
            }
        );

        function onNavFrameClick(e) {
            var index = $(this).data().eq;

            if (opts.navtype === 'thumbs') {
                clickToShow({index: index, slow: e.altKey, user: true, coo: e._x - $nav.offset().left});
            } else {
                clickToShow({index: index, slow: e.altKey, user: true});
            }
        }

        function onArrClick(e) {
            clickToShow({index: $arrs.index(this) ? '>' : '<', slow: e.altKey, user: true});
        }

        smartClick($arrs, function (e) {
            stopEvent(e);
            onArrClick.call(this, e);
        }, {
            onStart: function () {
                onTouchStart();
                stageShaftTouchTail.control = true;
            },
            onTouchEnd: onTouchEnd
        });

        smartClick($thumbArrLeft, function (e) {
            stopEvent(e);
            if (opts.navtype === 'thumbs') {

                that.show('<');
            } else {
                that.showSlide('prev')
            }
        });

        smartClick($thumbArrRight, function (e) {
            stopEvent(e);
            if (opts.navtype === 'thumbs') {
                that.show('>');
            } else {
                that.showSlide('next')
            }

        });


        function addFocusOnControls(el) {
            addFocus(el, function () {
                setTimeout(function () {
                    lockScroll($stage);
                }, 0);
                toggleControlsClass(false);
            });
        }

        $arrs.each(function () {
            addEnterUp(this, function (e) {
                onArrClick.call(this, e);
            });
            addFocusOnControls(this);
        });

        addEnterUp(fullscreenIcon, function () {
            if ($fotorama.hasClass(fullscreenClass)) {
                that.cancelFullScreen();
                $stageShaft.focus();
            } else {
                that.requestFullScreen();
                $fullscreenIcon.focus();
            }

        });
        addFocusOnControls(fullscreenIcon);

        function reset() {
            setData();
            setOptions();

            if (!reset.i) {
                reset.i = true;
                // Only once
                var _startindex = opts.startindex;
                activeIndex = repositionIndex = dirtyIndex = lastActiveIndex = startIndex = edgeIndex(_startindex) || 0;
                /*(o_rtl ? size - 1 : 0)*///;
            }

            if (size) {
                if (changeToRtl()) return;

                if ($videoPlaying) {
                    unloadVideo($videoPlaying, true);
                }

                activeIndexes = [];

                if (!isVideo()) {
                    detachFrames(STAGE_FRAME_KEY);
                }

                reset.ok = true;

                that.show({index: activeIndex, time: 0});
                that.resize();
            } else {
                that.destroy();
            }
        }

        function changeToRtl() {

            if (!changeToRtl.f === o_rtl) {
                changeToRtl.f = o_rtl;
                activeIndex = size - 1 - activeIndex;
                that.reverse();

                return true;
            }
        }

        $.each('load push pop shift unshift reverse sort splice'.split(' '), function (i, method) {
            that[method] = function () {
                data = data || [];
                if (method !== 'load') {
                    Array.prototype[method].apply(data, arguments);
                } else if (arguments[0] && typeof arguments[0] === 'object' && arguments[0].length) {
                    data = clone(arguments[0]);
                }
                reset();
                return that;
            }
        });

        function ready() {
            if (ready.ok) {
                ready.ok = false;
                triggerEvent('ready');
            }
        }

        reset();
    };
    $.fn.fotorama = function (opts) {
        return this.each(function () {
            var that = this,
                $fotorama = $(this),
                fotoramaData = $fotorama.data(),
                fotorama = fotoramaData.fotorama;

            if (!fotorama) {
                waitFor(function () {
                    return !isHidden(that);
                }, function () {
                    fotoramaData.urtext = $fotorama.html();
                    new $.Fotorama($fotorama,
                        $.extend(
                            {},
                            OPTIONS,
                            window.fotoramaDefaults,
                            opts,
                            fotoramaData
                        )
                    );
                });
            } else {
                fotorama.setOptions(opts, true);
            }
        });
    };
    $.Fotorama.instances = [];

    function calculateIndexes() {
        $.each($.Fotorama.instances, function (index, instance) {
            instance.index = index;
        });
    }

    function addInstance(instance) {
        $.Fotorama.instances.push(instance);
        calculateIndexes();
    }

    function hideInstance(instance) {
        $.Fotorama.instances.splice(instance.index, 1);
        calculateIndexes();
    }

    $.Fotorama.cache = {};
    $.Fotorama.measures = {};
    $ = $ || {};
    $.Fotorama = $.Fotorama || {};
    $.Fotorama.jst = $.Fotorama.jst || {};

    $.Fotorama.jst.dots = function (v) {
        var __t, __p = '', __e = _.escape;
        __p += '<div class="fotorama__nav__frame fotorama__nav__frame--dot" tabindex="0" role="button" data-gallery-role="nav-frame" data-nav-type="thumb" aria-label>\r\n    <div class="fotorama__dot"></div>\r\n</div>';
        return __p
    };

    $.Fotorama.jst.frameCaption = function (v) {
        var __t, __p = '', __e = _.escape;
        __p += '<div class="fotorama__caption" aria-hidden="true">\r\n    <div class="fotorama__caption__wrap" id="' +
            ((__t = ( v.labelledby )) == null ? '' : __t) +
            '">' +
            ((__t = ( v.caption )) == null ? '' : __t) +
            '</div>\r\n</div>\r\n';
        return __p
    };

    $.Fotorama.jst.style = function (v) {
        var __t, __p = '', __e = _.escape;
        __p += '.fotorama' +
            ((__t = ( v.s )) == null ? '' : __t) +
            ' .fotorama__nav--thumbs .fotorama__nav__frame{\r\npadding:' +
            ((__t = ( v.m )) == null ? '' : __t) +
            'px;\r\nheight:' +
            ((__t = ( v.h )) == null ? '' : __t) +
            'px}\r\n.fotorama' +
            ((__t = ( v.s )) == null ? '' : __t) +
            ' .fotorama__thumb-border{\r\nheight:' +
            ((__t = ( v.h )) == null ? '' : __t) +
            'px;\r\nborder-width:' +
            ((__t = ( v.b )) == null ? '' : __t) +
            'px;\r\nmargin-top:' +
            ((__t = ( v.m )) == null ? '' : __t) +
            'px}';
        return __p
    };

    $.Fotorama.jst.thumb = function (v) {
        var __t, __p = '', __e = _.escape;
        __p += '<div class="fotorama__nav__frame fotorama__nav__frame--thumb" tabindex="0" role="button" data-gallery-role="nav-frame" data-nav-type="thumb" aria-label>\r\n    <div class="fotorama__thumb">\r\n    </div>\r\n</div>';
        return __p
    };
})(window, document, location, typeof jQuery !== 'undefined' && jQuery);
