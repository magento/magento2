/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui",
    "jquery/template"
], function($){
    "use strict";
    
    /**
     * Product gallery widget
     */
    $.widget('mage.productGallery', {
        options: {
            imageSelector: '[data-role=image]',
            template: '.image-template',
            types: null,
            initialized: false
        },

        /**
         * Gallery creation
         * @protected
         */
        _create: function() {
            this.options.types = this.options.types || this.element.data('types');
            this.options.images = this.options.images || this.element.data('images');
            this.$template = this.element.find(this.options.template);
            this._bind();
            $.each(this.options.images, $.proxy(function(index, imageData) {
                this.element.trigger('addItem', imageData);
            }, this));
            this.options.initialized = true;
        },

        /**
         * Bind handler to elements
         * @protected
         */
        _bind: function() {
            var events = {
                addItem: '_addItem',
                removeItem: '_removeItem',
                setImageType: '_setImageType',
                setPosition: '_setPosition',
                resort: '_resort',
                'mouseup [data-role=delete-button]': function(event) {
                    event.preventDefault();
                    var $imageContainer = $(event.currentTarget).closest(this.options.imageSelector);
                    this.element.find('[data-role=dialog]').trigger('close');
                    this.element.trigger('removeItem', $imageContainer.data('imageData'));
                },
                'mouseup [data-role=make-base-button]': function(event) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    var $imageContainer = $(event.currentTarget).closest(this.options.imageSelector);
                    var imageData = $imageContainer.data('imageData');
                    this.setBase(imageData);
                }
            };
            this._on(events);

            this.element.sortable({
                distance: 8,
                items: this.options.imageSelector,
                tolerance: "pointer",
                cancel: 'input, button, .uploader',
                update: $.proxy(function() {
                    this.element.trigger('resort');
                }, this)
            });
        },

        /**
         * Set image as main
         * @param {Object} imageData
         * @private
         */
        setBase: function(imageData) {
            var baseImage = this.options.types.image;
            var sameImages = $.grep(
                $.map(this.options.types, function(el) {
                    return el;
                }),
                function(el) {
                    return el.value == baseImage.value;
                }
            );
            var isImageOpened = this.findElement(imageData).hasClass('active');

            $.each(sameImages, $.proxy(function(index, image) {
                this.element.trigger('setImageType', {
                    type: image.code,
                    imageData: imageData
                });
                if (isImageOpened) {
                    this.element.find('.item').addClass('selected');
                    this.element.find('[data-role=type-selector]').prop({'checked': true});
                }
            }, this));
        },

        /**
         * Find element by fileName
         * @param {Object} data
         * @returns {Element}
         */
        findElement: function(data) {
            return this.element.find(this.options.imageSelector).filter(function() {
                return $(this).data('imageData').file == data.file;
            }).first();
        },

        /**
         * Add image
         * @param event
         * @param imageData
         * @private
         */
        _addItem: function(event, imageData) {
            var count = this.element.find(this.options.imageSelector).length;
            imageData = $.extend({
                file_id: Math.random().toString(33).substr(2, 18),
                disabled: 0,
                position: count + 1
            }, imageData);

            var element = this.$template.tmpl(imageData).data('imageData', imageData);
            if (count === 0) {
                element.prependTo(this.element);
            } else {
                element.insertAfter(this.element.find(this.options.imageSelector + ':last'));
            }

            if (!this.options.initialized && this.options.images.length === 0 ||
                this.options.initialized && this.element.find(this.options.imageSelector + ':not(.removed)').length == 1
            ) {
                this.setBase(imageData);
            }
            $.each(this.options.types, $.proxy(function(index, image) {
                if (imageData.file == image.value) {
                    this.element.trigger('setImageType', {
                        type: image.code,
                        imageData: imageData
                    });
                }
            }, this));
        },

        /**
         * Remove Image
         * @param {jQuery.Event} event
         * @param imageData
         * @private
         */
        _removeItem: function(event, imageData) {
            var $imageContainer = this.findElement(imageData);
            $imageContainer.addClass('removed').hide().find('.is-removed').val(1);
        },

        /**
         * Set image type
         * @param event
         * @param data
         * @private
         */
        _setImageType: function(event, data) {
            if (data.type === 'image') {
                this.element.find('.base-image').removeClass('base-image');
            }
            if (data.imageData) {
                this.options.types[data.type].value = data.imageData.file;
                if (data.type === 'image') {
                    this.findElement(data.imageData).addClass('base-image');
                }
            } else {
                this.options.types[data.type].value = 'no_selection';
            }
            this.element.find('.image-' + data.type).val(this.options.types[data.type].value || 'no_selection');
        },

        /**
         * Resort images
         * @private
         */
        _resort: function() {
            this.element.find('.position').each($.proxy(function(index, element) {
                var value = $(element).val();
                if (value != index) {
                    this.element.trigger('moveElement', {
                        imageData: $(element).closest(this.options.imageSelector).data('imageData'),
                        position: index
                    });
                    $(element).val(index);
                }
            }, this));
        },

        /**
         * Set image position
         * @param event
         * @param data
         * @private
         */
        _setPosition: function(event, data) {
            var $element = this.findElement(data.imageData);
            var curIndex = this.element.find(this.options.imageSelector).index($element);
            var newPosition = data.position + (curIndex > data.position ? -1 : 0);
            if (data.position != curIndex) {
                if (data.position === 0) {
                    this.element.prepend($element);
                } else {
                    $element.insertAfter(
                        this.element.find(this.options.imageSelector).eq(newPosition)
                    );
                }
                this.element.trigger('resort');
            }
        }
    });

    // Extension for mage.productGallery - Add advanced settings block
    $.widget('mage.productGallery', $.mage.productGallery, {
        options: {
            dialogTemplate: '.dialog-template'
        },

        /**
         * Bind handler to elements
         * @protected
         */
        _bind: function() {
            this._super();
            var events = {
                'change [data-role=visibility-trigger]': '_changeVisibility',
                'change [data-role=type-selector]': '_changeType'
            };

            events['click [data-role=close-panel]'] = $.proxy(function() {
                this.element.find('[data-role=dialog]').trigger('close');
            }, this);
            events['mouseup ' + this.options.imageSelector] = function(event) {
                if (!$(event.currentTarget).is('.ui-sortable-helper')) {
                    $(event.currentTarget).addClass('active');
                    this._showDialog($(event.currentTarget).data('imageData'));
                }
            };
            events['click .action-remove'] = function (event) {
                var $imageContainer = $(event.currentTarget).closest('[data-role=dialog]').data('imageContainer');
                this.element.find('[data-role=dialog]').trigger('close');
                this.element.trigger('removeItem', $imageContainer.data('imageData'));
            };
            this._on(events);
            this.element.on('sortstart', $.proxy(function() {
                this.element.find('[data-role=dialog]').trigger('close');
            }, this));

            this.element.on('change', '[data-role=type-selector]', function() {
                var parent = $(this).closest('.item'),
                    selectedClass = 'selected';
                parent.toggleClass(selectedClass, $(this).prop('checked'));
            });
        },

        /**
         * Set Position of Image Pointer
         * @param image
         * @param panel
         * @private
         */
        _setImagePointerPosition: function(image, panel) {
            var position = image.position(),
                posX = position.left,
                imageWidth = image.width(),
                pointer = $('.image-pointer', panel),
                pointerWidth = pointer.width(),
                padding = -3,
                pointerOffset = posX + padding + pointerWidth / 2 + imageWidth / 2;

            pointer.css({left: pointerOffset});
        },

        /**
         * Show dialog
         * @param imageData
         * @private
         */
        _showDialog: function(imageData) {
            var $imageContainer = this.findElement(imageData);
            var dialogElement = $imageContainer.data('dialog');
            if ($imageContainer.is('.removed') || (dialogElement && dialogElement.is(':visible'))) {
                return;
            }
            this.element.find('[data-role=dialog]').trigger('close');
            if (!dialogElement) {
                var $template = this.element.find(this.options.dialogTemplate),
                    imageCountInRow = 5;

                dialogElement = $template.tmpl(imageData);

                dialogElement
                    .data('imageContainer', $imageContainer)
                    .on('open', $.proxy(function(event) {
                        var imagesList = this.element.find(this.options.imageSelector + ':not(.removed), .image-placeholder');
                        var index = imagesList.index($imageContainer);
                        var positionIndex = Math.floor(index / imageCountInRow + 1) * imageCountInRow - 1;
                        if (positionIndex > imagesList.length - 1) {
                            positionIndex = imagesList.length - 1;
                        }
                        var afterElement = imagesList.get(positionIndex);

                        $(event.target)
                            .insertAfter(afterElement)
                            .slideDown(400);

                        $(event.target)
                            .find('[data-role=type-selector]')
                            .each($.proxy(function(index, checkbox) {
                                var $checkbox = $(checkbox),
                                    parent = $checkbox.closest('.item'),
                                    selectedClass = 'selected',
                                    isChecked = this.options.types[$checkbox.val()].value == imageData.file;
                                $checkbox.prop(
                                    'checked',
                                    isChecked
                                );
                                parent.toggleClass(selectedClass, isChecked);
                            }, this));
                        this._setImagePointerPosition($imageContainer, dialogElement);

                    }, this))
                    .on('close', $.proxy(function(event) {
                        $imageContainer.removeClass('active');
                        $(event.target)
                            .slideUp(400);
                    }, this));

                $imageContainer.data('dialog', dialogElement);
            }
            dialogElement.trigger('open');
        },

        /**
         * Change visibility
         *
         * @param event
         * @private
         */
        _changeVisibility: function(event) {
            var $checkbox = $(event.currentTarget);
            var $imageContainer = $checkbox.closest('[data-role=dialog]').data('imageContainer');
            $imageContainer.toggleClass('hidden-for-front', $checkbox.is(':checked'));
        },

        /**
         * Set image
         * @param event
         * @private
         */
        _changeType: function(event) {
            var $checkbox = $(event.currentTarget);
            var $imageContainer = $checkbox.closest('[data-role=dialog]').data('imageContainer');
            this.element.trigger('setImageType', {
                type: $checkbox.val(),
                imageData: $checkbox.is(':checked') ? $imageContainer.data('imageData') : null
            });
        }
    });
    
    return $.mage.productGallery;
});