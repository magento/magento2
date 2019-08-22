/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @deprecated since version 2.2.0
 */
(function (root, factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'mage/template',
            'jquery-ui-modules/widget'
        ], factory);
    } else {
        factory(root.jQuery, root.mageTemplate);
    }
}(this, function ($, mageTemplate) {
    'use strict';

    $.widget('mage.zoom', {
        options: {
            largeImage: null,
            startZoomEvent: 'click',
            stopZoomEvent: 'mouseleave',
            hideDelay: '100',
            effects: {
                show: {
                    effect: 'fade',
                    duration: 100
                },
                hide: {
                    effect: 'fade',
                    duration: 100
                }
            },
            controls: {
                lens: {
                    template: '[data-template=zoom-lens]',
                    opacity: 0.7,
                    background: '#ffffff'
                },
                track: {
                    template: '[data-template=zoom-track]'
                },
                display: {
                    template: '[data-template=zoom-display]',
                    width: 400,
                    height: 400,
                    left: 0,
                    top: 0
                },
                notice: {
                    template: '[data-template=notice]',
                    text: null,
                    container: '[data-role=gallery-notice-container]'
                }
            },
            selectors: {
                image: '[data-role=zoom-image]',
                imageContainer: '[data-role=gallery-base-image-container]',
                zoomInner: '[data-role=zoom-inner]',
                track: '[data-role=zoom-track]',
                notice: '[data-role=notice]'
            }
        },
        noticeOriginal: '',

        /**
         * Widget constructor.
         * @protected
         */
        _create: function () {
            this._setZoomData();
            this._render();
            this._bind();

            if (this.largeImage[0].complete) {
                this._largeImageLoaded();
            }
            this._hide(this.display);
            this._hide(this.track);
        },

        /**
         * Render zoom controls.
         * @protected
         */
        _render: function () {
            var noticeContainer;

            this.element.append(this._renderControl('track').append(this._renderControl('lens')));
            this.element.append(this._renderControl('display'))
                .find(this.options.selectors.zoomInner)
                .append(this._renderLargeImage());
            noticeContainer = this.element.find(this.options.controls.notice.container);
            noticeContainer = noticeContainer.length ?
                noticeContainer :
                this.element;
            noticeContainer.append(this._renderControl('notice'));
        },

        /**
         * Toggle zoom notice.
         * @protected
         */
        _toggleNotice: function () {
            this.noticeOriginal = this.notice.text() !== this.options.controls.notice.text ?
                this.notice.text() :
                this.noticeOriginal;

            if (this.getZoomRatio() > 1 && this.largeImageSrc && !this.activated) {
                this.notice.text(this.options.controls.notice.text);
            } else {
                this.notice.text(this.noticeOriginal);
            }
        },

        /**
         * Render zoom control.
         *
         * @param {String} control - name of the control
         * @return {Element} DOM-element
         * @protected
         */
        _renderControl: function (control) {
            var controlData = this.options.controls[control],
                templateData = {},
                css = {},
                controlElement;

            switch (control) {
                case 'display':
                    templateData = {
                        img: this.largeImageSrc
                    };
                    css = {
                        width: controlData.width,
                        height: controlData.height
                    };
                    break;

                case 'notice':
                    templateData = {
                        text: controlData.text || ''
                    };
                    break;
            }
            controlElement = this.element.find(this.options.selectors[control]);
            controlElement = controlElement.length ?
                controlElement :
                $(mageTemplate(controlData.template, {
                    data: templateData
                }));
            this[control] = controlElement.css(css);

            return this[control];
        },

        /**
         * Refresh zoom controls.
         * @protected
         */
        _refresh: function () {
            this._refreshControl('display');
            this._refreshControl('track');
            this._refreshControl('lens');
        },

        /**
         * Refresh zoom control position and css.
         *
         * @param {String} control - name of the control
         * @protected
         */
        _refreshControl: function (control) {
            var controlData = this.options.controls[control],
                position,
                css = {
                    position: 'absolute'
                };

            switch (control) {
                case 'display':
                    position = {
                        my: 'left+' + this.options.controls.display.left + ' top+' +
                            this.options.controls.display.top + '',
                        at: 'left+' + $(this.image).outerWidth() + ' top',
                        of: $(this.image)
                    };
                    break;

                case 'track':
                    $.extend(css, {
                        height: $(this.image).height(),
                        width: $(this.image).width()
                    });
                    position = {
                        my: 'left top',
                        at: 'left top',
                        of: $(this.image)
                    };
                    break;

                case 'lens':
                    $.extend(css, this._calculateLensSize(), {
                        background: controlData.background,
                        opacity: controlData.opacity,
                        left: 0,
                        top: 0
                    });
                    break;
            }
            this[control].css(css);

            if (position) {
                this[control].position(position);
            }
        },

        /**
         * Bind zoom event handlers.
         * @protected
         */
        _bind: function () {
            /* Events delegated to this.element, which means that all zoom controls can be changed any time
             *  and not required to re-bind events
             */
            var events = {};

            events[this.options.startZoomEvent + ' ' + this.options.selectors.image] = 'show';

            /** Handler */
            events[this.options.stopZoomEvent + ' ' + this.options.selectors.track] = function () {
                this._delay(this.hide, this.options.hideDelay || 0);
            };
            events['mousemove ' + this.options.selectors.track] = '_move';
            events.imageupdated = '_onImageUpdated';
            this._on(events);
            this._on(this.largeImage, {
                load: '_largeImageLoaded'
            });
        },

        /**
         * Store initial zoom data.
         * @protected
         */
        _setZoomData: function () {
            this.image = this.element.find(this.options.selectors.image);
            this.largeImageSrc = this.options.largeImage ||
                this.element.find(this.image).data('large');
        },

        /**
         * Update zoom when called enable method.
         * @override
         */
        enable: function () {
            this._super();
            this._onImageUpdated();
        },

        /**
         * Toggle notice when called disable method.
         * @override
         */
        disable: function () {
            this.notice.text(this.noticeOriginal || '');
            this._super();
        },

        /**
         * Show zoom controls.
         *
         * @param {Object} e - event object
         */
        show: function (e) {
            e.preventDefault();

            if (this.getZoomRatio() > 1 && this.largeImageSrc) {
                e.stopImmediatePropagation();
                this.activated = true;
                this._show(this.display, this.options.effects.show);
                this._show(this.track, this.options.effects.show);
                this._refresh();
                this.lens.position({
                    my: 'center',
                    at: 'center',
                    of: e,
                    using: $.proxy(this._refreshZoom, this)
                });
                this._toggleNotice();
                this._trigger('show');
            }
        },

        /** Hide zoom controls */
        hide: function () {
            this.activated = false;
            this._hide(this.display, this.options.effects.hide);
            this._hide(this.track, this.options.effects.hide);
            this._toggleNotice();
            this._trigger('hide');
        },

        /**
         * Refresh zoom when image is updated
         * @protected
         */
        _onImageUpdated: function () {
            // Stop loader in case previous active image has not been loaded yet
            $(this.options.selectors.image).trigger('processStop');

            if (!this.image.is($(this.options.selectors.image))) {
                this._setZoomData();

                if (this.largeImageSrc) {
                    this._refreshLargeImage();
                    this._refresh();
                } else {
                    this.hide();
                }
            }
        },

        /**
         * Reset this.ratio when large image is loaded
         * @protected
         */
        _largeImageLoaded: function () {
            this.largeImage.css({
                width: 'auto',
                height: 'auto'
            });
            this.largeImageSize = {
                width: this.largeImage.width() || this.largeImage.get(0).naturalWidth,
                height: this.largeImage.height() || this.largeImage.get(0).naturalHeight
            };
            this.ratio = null;
            this._toggleNotice();
            $(this.options.selectors.image).trigger('processStop');
        },

        /**
         * Refresh large image (refresh "src" and initial position)
         * @protected
         */
        _refreshLargeImage: function () {
            var oldSrc;

            if (this.largeImage) {
                oldSrc = this.largeImage.attr('src');

                if (oldSrc !== this.largeImageSrc) {
                    $(this.options.selectors.image).trigger('processStart');
                    this.largeImage.attr('src', this.largeImageSrc);
                }

                this.largeImage.css({
                    top: 0,
                    left: 0
                });
            }
        },

        /**
         * @return {Element} DOM-element
         * @protected
         */
        _renderLargeImage: function () {
            var image = $(this.options.selectors.image);

            // Start loader if 'load' event of image is expected to trigger later
            if (this.largeImageSrc) {
                image.trigger('processStart');
            }

            // No need to create template just for img tag
            this.largeImage = $('<img />', {
                src: this.largeImageSrc
            });

            return this.largeImage;
        },

        /**
         * Calculate zoom ratio.
         *
         * @return {Number}
         * @protected
         */
        getZoomRatio: function () {
            var imageWidth;

            if (this.ratio === null || typeof this.ratio === 'undefined') {
                imageWidth = $(this.image).width() || $(this.image).prop('width');

                return this.largeImageSize ? this.largeImageSize.width / imageWidth : 1;
            }

            return this.ratio;
        },

        /**
         * Calculate lens size, depending on zoom ratio.
         *
         * @return {Object} object contain width and height fields
         * @protected
         */
        _calculateLensSize: function () {
            var displayData = this.options.controls.display,
                ratio = this.getZoomRatio();

            return {
                width: Math.ceil(displayData.width / ratio),
                height: Math.ceil(displayData.height / ratio)
            };
        },

        /**
         * Refresh position of large image depending of position of zoom lens.
         *
         * @param {Object} position
         * @param {Object} ui
         * @protected
         */
        _refreshZoom: function (position, ui) {
            $(ui.element.element).css(position);
            this.largeImage.css(this._getLargeImageOffset(position));
        },

        /**
         * @param {Object} position
         * @return {Object}
         * @private
         */
        _getLargeImageOffset: function (position) {
            var ratio = this.getZoomRatio();

            return {
                top: -(position.top * ratio),
                left: -(position.left * ratio)
            };
        },

        /**
         * Mouse move handler.
         *
         * @param {Object} e - event object
         * @protected
         */
        _move: function (e) {
            this.lens.position({
                my: 'center',
                at: 'left top',
                of: e,
                collision: 'fit',
                within: this.image,
                using: $.proxy(this._refreshZoom, this)
            });
        }
    });

    /** Extension for zoom widget - white borders detection */
    $.widget('mage.zoom', $.mage.zoom, {
        /**
         * Get aspect ratio of the element.
         *
         * @param {Object} element - jQuery collection
         * @return {*}
         * @protected
         */
        _getAspectRatio: function (element) {
            var width, height, aspectRatio;

            if (!element || !element.length) {
                return null;
            }
            width = element.width() || element.prop('width');
            height = element.height() || element.prop('height');
            aspectRatio = width / height;

            return Math.round(aspectRatio * 100) / 100;
        },

        /**
         * Calculate large image offset depending on enabled "white borders" functionality.
         *
         * @return {Object}
         * @protected
         */
        _getWhiteBordersOffset: function () {
            var ratio = this.getZoomRatio(),
                largeWidth = this.largeImageSize.width / ratio,
                largeHeight = this.largeImageSize.height / ratio,
                width = this.image.width() || this.image.prop('width'),
                height = this.image.height() || this.image.prop('height'),
                offsetLeft = width - largeWidth > 0 ?
                Math.ceil((width - largeWidth) / 2) :
                0,
                offsetTop = height - largeHeight > 0 ?
                Math.ceil((height - largeHeight) / 2) :
                0;

            return {
                top: offsetTop,
                left: offsetLeft
            };
        },

        /**
         * @override
         */
        _largeImageLoaded: function () {
            this._super();
            this.whiteBordersOffset = null;

            if (this._getAspectRatio(this.image) !== this._getAspectRatio(this.largeImage)) {
                this.whiteBordersOffset = this._getWhiteBordersOffset();
            }
        },

        /**
         * @override
         */
        _getLargeImageOffset: function (position) {
            if (this.whiteBordersOffset) {
                position.top -= this.whiteBordersOffset.top;
                position.left -= this.whiteBordersOffset.left;
            }

            return this._superApply([position]);
        }
    });

    return $.mage.zoom;
}));
