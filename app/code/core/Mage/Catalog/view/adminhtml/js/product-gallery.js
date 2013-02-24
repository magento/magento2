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
 * @category    mage
 * @package     mage
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint jquery:true*/
(function($) {
    "use strict";
    /**
     * Product gallery widget
     */
    $.widget('mage.productGallery', {
        options: {
            imageSelector: '[data-role="image"]',
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
                'click [data-role="delete-button"]': function(event) {
                    event.preventDefault();
                    var $imageContainer = $(event.currentTarget).closest(this.options.imageSelector);
                    this.element.trigger('removeItem', $imageContainer.data('imageData'));
                },
                'click [data-role="make-main-button"]': function(event) {
                    event.preventDefault();
                    var $imageContainer = $(event.currentTarget).closest(this.options.imageSelector);
                    var imageData = $imageContainer.data('imageData');
                    this.setMain(imageData);
                },
                'change [data-role="type-selector"]': '_changeType',
                'change [data-role="visibility-trigger"]': '_changeVisibility'
            };
            events['click ' + this.options.imageSelector] = function() {
                $(event.currentTarget).toggleClass('active');
            };
            this._on(events);

            this.element.sortable({
                distance: 8,
                items: this.options.imageSelector,
                tolerance: "pointer",
                cancel: 'input, button, .ui-dialog, .uploader',
                update: $.proxy(function() {
                    this.element.trigger('resort');
                }, this)
            });
        },

        /**
         * Change visibility
         *
         * @param event
         * @private
         */
        _changeVisibility: function(event) {
            var $checkbox = $(event.currentTarget);
            var $imageContainer = $checkbox.closest(this.options.imageSelector);
            $imageContainer.toggleClass('disabled', $checkbox.is(':checked'));
        },

        /**
         * Set image as main
         * @param {Object} imageData
         * @private
         */
        setMain: function(imageData) {
            var baseImage = this.options.types.image;
            var sameImages = $.grep(
                $.map(this.options.types, function(el) {
                    return el;
                }),
                function(el) {
                    return el.value == baseImage.value;
                }
            );

            $.each(sameImages, $.proxy(function(index, image) {
                this.element.trigger('setImageType', {
                    type: image.code,
                    imageData: imageData
                });
            }, this));
        },

        /**
         * Set image
         * @param event
         * @private
         */
        _changeType: function(event) {
            var $checkbox = $(event.currentTarget);
            var $imageContainer = $checkbox.closest(this.options.imageSelector);
            this.element.trigger('setImageType', {
                type: $checkbox.val(),
                imageData: $checkbox.is(':checked') ? $imageContainer.data('imageData') : null
            });
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
                this.setMain(imageData);
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
        _setImageType: function(event, data){
            this.element.find('.type-' + data.type).hide();
            if (data.imageData) {
                this.options.types[data.type].value = data.imageData.file;
                this.findElement(data.imageData).find('.type-' + data.type).show();
            } else {
                this.options.types[data.type].value = null;
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

    // Extension for mage.productGallery - Add advanced settings dialog
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
                'click [data-role="delete-button"]': function() {
                    this.element.find('[data-role="dialog"]').trigger('close');
                }
            };
            events['dblclick ' + this.options.imageSelector] = function(event) {
                this._showDialog($(event.currentTarget).data('imageData'));
            };
            this._on(events);
            this.element.on('sortstart', $.proxy(function() {
                this.element.find('[data-role="dialog"]').trigger('close');
            }, this));

        },

        /**
         * Show dialog
         * @param imageData
         * @private
         */
        _showDialog: function(imageData) {
            var $imageContainer = this.findElement(imageData);
            var dialogElement = $imageContainer.data('dialog');
            if ($imageContainer.is('.removed')) {
                return;
            }

            if (!dialogElement) {
                var $template = this.element.find(this.options.dialogTemplate);
                var imageCountInLine = 6;
                dialogElement = $template.tmpl(imageData);

                dialogElement.on("open", $.proxy(function(event) {
                    var imagesList = this.element.find(this.options.imageSelector + ':not(.removed)');
                    var index = imagesList.index($imageContainer);
                    var positionIndex = Math.floor(index / imageCountInLine + 1) * imageCountInLine - 1;
                    if (positionIndex > imagesList.length - 1) {
                        positionIndex = imagesList.length - 1;
                    }
                    var afterElement = imagesList.get(positionIndex);
                    $(event.target).insertAfter(afterElement);
                    $imageContainer.find('[data-role="type-selector"]').each($.proxy(function(index, checkbox) {
                        var $checkbox = $(checkbox);
                        $checkbox.prop('checked', this.options.types[$checkbox.val()].value == imageData.file);
                    }, this));
                    $(event.target).show();
                }, this));
                dialogElement.on("close", function(event) {
                    $(event.target).hide();
                });
                $imageContainer.data('dialog', dialogElement);
            }
            dialogElement.trigger('open');

        }
    });
})(jQuery);
