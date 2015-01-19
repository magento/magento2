/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 @version 0.1.1
 @requires jQuery

 @TODO: - Add more effects;
 */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define([
            "jquery",
            "jquery/ui",
            "jquery/template"
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
    "use strict";

    $.widget('mage.gallery', {
        options: {
            images: null,
            sizes: {
                small: {
                    width: 90,
                    height: 90
                },
                medium: {
                    width: 400,
                    height: 400
                }
            },
            showThumbs: true,
            showButtons: true,
            showNotice: true,
            activeClass: 'active',
            disableLinks: true,
            controls: {
                thumbs: {
                    container: '[data-role=gallery-thumbs-container]',
                    template: '[data-template=gallery-thumbs]'
                },
                slideButtons: {
                    container: '[data-role=gallery-buttons-container]',
                    template: '[data-template=gallery-buttons]'
                },
                baseImage: {
                    container: '[data-role=gallery-base-image-container]',
                    template: '[data-template=gallery-base-image]'
                },
                notice: {
                    container: '[data-role=gallery-notice-container]',
                    template: '[data-template=notice]',
                    text: null
                }
            },
            wrapperTemplate: '[data-template=gallery-wrapper]',
            selectors: {
                thumb: '[data-role=gallery-thumb]',
                prev: '[data-role=gallery-prev]',
                next: '[data-role=gallery-next]',
                notice: '[data-role=gallery-notice]'
            },
            fullSizeMode: false
        },

        /**
         * Widget constructor
         * @protected
         */
        _create: function() {
            if (!this.options.images) {
                this.options.images = this._serializeImages();
            }
            this.element.empty();
            this.element.append(this._renderWrapper());
            this._render();
            this._bind();
        },

        /**
         * Serialize images from HTML
         * @return {Array}
         * @protected
         */
        _serializeImages: function() {
            var images = [];
            $(this.options.selectors.thumb).each(function() {
                var thumb = $(this);
                var imageData = {
                    small: thumb.data('image-small'),
                    medium: thumb.data('image-medium'),
                    large: thumb.data('image-large')

                };
                if (imageData.small && imageData.medium && imageData.large) {
                    if (thumb.data('image-selected')) {
                        imageData.selected = thumb.data('image-selected');
                    }
                    images.push(imageData);
                }
            });
            return images;
        },

        /**
         * Bind widget event handlers
         * @protected
         */
        _bind: function() {
            /* All events delegated to this.element, which means that thumbs and slide controls can be changed any time
             *  and not required to re-bind events
             */
            var events = {};
            events['click ' + this.options.selectors.thumb] = 'select';
            events['click ' + this.options.selectors.prev] = 'prev';
            events['click ' + this.options.selectors.next] = 'next';
            this._on(events);
        },

        /**
         * Disable/enable gallery control (thumbs, slide buttons)
         * @param {string} control - control name
         * @param {boolean} enable
         * @protected
         */
        _toggleControl: function(control, enable) {
            if (enable) {
                if (!this[control]) {
                    this._initControl(control);
                } else {
                    this[control].show();
                }

            } else if (this[control]) {
                this[control].hide();
            }
        },

        /**
         * Override jQuery widget factory method, to provide possibility disabling/enabling gallery controls
         *      via changing of widget options
         * @override
         */
        _setOption: function (key, value) {
            var previousValue = this.options[key];
            this._superApply(arguments);
            if (value !== previousValue) {
                switch(key) {
                    case 'showThumbs':
                        this._toggleControl('thumbs', value);
                        break;
                    case 'showButtons':
                        this._toggleControl('slideButtons', value);
                        break;
                    case 'showNotice' :
                        this._toggleControl('notice', value);
                        break;
                    case 'fullSizeMode':
                        this._initControl('baseImage');
                        $(this.baseImage).trigger('imageupdated');
                        break;
                    case 'images' :
                        this._render();
                        break;
                }
            }
        },

        /**
         * Trigger 'imageupdated' event after image is changed
         * @param {Object} e - event object
         */
        select: function(e) {
            var index = $(e.currentTarget).data('index');
            if (index !== this._getSelected()) {
                this._select(index);
                $(this.baseImage).trigger('imageupdated');
            }
        },

        /**
         * Select gallery image
         * @param {number} index - image index
         * @protected
         */
        _select: function(index) {
            this._setSelected(index);
            this._initControl('baseImage');
            this.thumbs.find(this.options.selectors.thumb)
                .removeClass(this.options.activeClass)
                .eq(index).addClass(this.options.activeClass);
        },

        /**
         * @param {number} index - index of next image
         * @return {number} resolved index
         * @protected
         */
        _resolveIndex: function(index) {
            var imagesLength = this.options.images.length;
            if (index >= imagesLength) {
                return 0;
            } else if (index < 0) {
                return imagesLength -1;
            }
            return index;
        },

        /**
         * Select previous image
         */
        prev: function() {
            this._select(this._resolveIndex(this._getSelected() - 1));
        },

        /**
         * Select next image
         */
        next: function() {
            this._select(this._resolveIndex(this._getSelected() + 1));
        },

        /**
         * Render gallery
         * @protected
         */
        _render: function() {
            if (!this._findSelected()) {
                this.selected = 0;
            }
            if (this.options.showNotice) {
                this._initControl('notice');
            }
            this._initControl('baseImage');
            this.baseImage.trigger('imageupdated');
            if (this.options.showThumbs) {
                this._initControl('thumbs');
            }
            if (this.options.showButtons) {
                this._initControl('slideButtons');
            }
        },

        /**
         * Set selected image index
         * @param {number} index - image index
         * @protected
         */
        _setSelected: function(index) {
            this.selected = index;
        },

        /**
         * Get selected image index
         * @return {number}
         * @protected
         */
        _getSelected: function() {
            if (this.selected === null || typeof this.selected === 'undefined') {
                this.selected = this._findSelected();
            }
            return this.selected;
        },

        /**
         * Find selected image in this.options.images
         * @return {number} - selected image index
         * @protected
         */
        _findSelected: function() {
            var mapped = $.map(this.options.images, function(image, index) {
                return image.selected ? index : null;
            });
            return mapped[0];
        },

        /**
         * Render placeholders for gallery controls
         * @return {Element} DOM-element
         * @protected
         */
        _renderWrapper: function() {
            return $.tmpl($(this.options.wrapperTemplate), {});
        },

        /**
         * Render gallery control (base image, thumbs, slide buttons, etc.)
         * @param {string} control - name of the control
         * @return {Element} DOM-element
         * @protected
         */
        _renderControl: function(control) {
            var options = this.options,
                templateData;
            switch(control) {
                case 'baseImage':
                    templateData = $.extend(
                        {notice: options.notice, fullSizeMode: this.options.fullSizeMode},
                        options.images[this._getSelected()],
                        options.sizes.medium
                    );
                    break;
                case 'thumbs':
                    templateData = {images: this.options.images, size: this.options.sizes.small};
                    break;
                case 'slideButtons':
                    templateData = {};
                    break;
                case 'notice':
                    templateData = {text: this.options.controls.notice.text || ''};
                    break;
            }
            if (this[control]) {
                this[control].remove();
            }
            this[control] = $.tmpl($(options.controls[control].template), templateData);
            this._on(this[control].find('a').add(this[control]), {
                click: function(e){
                    if (this.options.disableLinks || !$(e.target).is("[data-role='zoom-image']")) {
                        e.preventDefault();
                    }
                }
            });
            return this[control];
        },

        /**
         * Append rendered control to this.element
         * @param {string} control - control name
         * @protected
         */
        _initControl: function(control) {
            this.element.find(this.options.controls[control].container)
                .prepend(this._renderControl(control));
        }
    });
    
    return $.mage.gallery;
}));