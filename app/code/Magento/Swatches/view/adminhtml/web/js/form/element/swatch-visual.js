/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global $break $ $$ FORM_KEY */

define([
    'jquery',
    'mage/template',
    'uiRegistry',
    'prototype',
    'Magento_Ui/js/form/element/abstract',
    'jquery/ui'
], function (jQuery, mageTemplate, rg, prototype, Abstract) {
    'use strict';

    function oldCode(value, container) {
        var swatchVisualOption = {
            itemCount: 0,
            totalItems: 0,
            rendered: 0,
            isReadOnly: false,

            initialize: function () {
                if (_.isEmpty(value)) {
                    container.addClassName('unavailable');
                }

                jQuery(container).on(
                    'click',
                    '.colorpicker_handler',
                    this.initColorPicker
                );
            },

            /**
             * ColorPicker initialization process
             */
            initColorPicker: function () {
                var element = this,
                    hiddenColorPicker = !jQuery(element).data('colorpickerId');

                jQuery(this).ColorPicker({

                    /**
                     * ColorPicker onShow action
                     */
                    onShow: function () {
                        var color = jQuery(element).parent().parent().prev().prev('input').val(),
                            menu = jQuery(this).parents('.swatch_sub-menu_container');

                        menu.hide();
                        jQuery(element).ColorPickerSetColor(color);
                    },

                    /**
                     * ColorPicker onSubmit action
                     *
                     * @param {String} hsb
                     * @param {String} hex
                     * @param {String} rgb
                     * @param {String} el
                     */
                    onSubmit: function (hsb, hex, rgb, el) {
                        var container = jQuery(el).parent().parent().prev();

                        jQuery(el).ColorPickerHide();
                        container.parent().removeClass('unavailable');
                        container.prev('input').val('#' + hex);
                        container.css('background', '#' + hex);
                    }
                });

                if (hiddenColorPicker) {
                    jQuery(this).ColorPickerShow();
                }
            },

            /**
             * Remove action
             *
             * @param {Object} event
             */
            remove: function (event) {
                var element = $(Event.findElement(event, 'tr')),
                    elementFlags; // !!! Button already have table parent in safari

                // Safari workaround
                element.ancestors().each(function (parentItem) {
                    if (parentItem.hasClassName('option-row')) {
                        element = parentItem;
                        throw $break;
                    } else if (parentItem.hasClassName('box')) {
                        throw $break;
                    }
                });

                if (element) {
                    elementFlags = element.getElementsByClassName('delete-flag');

                    if (elementFlags[0]) {
                        elementFlags[0].value = 1;
                    }

                    element.addClassName('no-display');
                    element.addClassName('template');
                    element.hide();
                    this.totalItems--;
                    this.updateItemsCountField();
                }
            },

            /**
             * Update items count field
             */
            updateItemsCountField: function () {
                $('swatch-visual-option-count-check').value = this.totalItems > 0 ? '1' : '';
            }
        };

        //swatchVisualOption.initColorPicker();

        jQuery('body').on('click', function (event) {
            var element = jQuery(event.target);

            if (
                element.parents('.swatch_sub-menu_container').length === 1 ||
                element.next('div.swatch_sub-menu_container').length === 1
            ) {
                return true;
            }
            jQuery('.swatch_sub-menu_container').hide();
        });

        window.swatchVisualOption = swatchVisualOption;

        rg.set('swatch-visual-options-panel', swatchVisualOption);

        jQuery(function ($) {

            var swatchComponents = {

                /**
                 * div wrapper for to hide all evement
                 */
                wrapper: null,

                /**
                 * iframe component to perform file upload without page reload
                 */
                iframe: null,

                /**
                 * form component for upload image
                 */
                form: null,

                /**
                 * Input file component for upload image
                 */
                inputFile: null,

                /**
                 * Create swatch component for upload files
                 *
                 * @this {swatchComponents}
                 * @public
                 */
                create: function () {
                    this.wrapper = $('<div>').css({
                        display: 'none'
                    }).appendTo($('body'));

                    this.iframe = $('<iframe />', {
                        id: 'upload_iframe',
                        name: 'upload_iframe'
                    }).appendTo(this.wrapper);

                    this.form = $('<form />', {
                        id: 'swatch_form_image_upload',
                        name: 'swatch_form_image_upload',
                        target: 'upload_iframe',
                        method: 'post',
                        enctype: 'multipart/form-data',
                        class: 'ignore-validate',
                        action: 'someUrl'
                    }).appendTo(this.wrapper);

                    this.inputFile = $('<input />', {
                        type: 'file',
                        name: 'datafile',
                        class: 'swatch_option_file'
                    }).appendTo(this.form);

                    $('<input />', {
                        type: 'hidden',
                        name: 'form_key',
                        value: FORM_KEY
                    }).appendTo(this.form);
                }
            };

            swatchVisualOption.initialize();

            /**
             * Create swatch components
             */
            swatchComponents.create();

            /**
             * Register event for swatch input[type=file] change
             */
            swatchComponents.inputFile.change(function () {
                var container = $('#' + $(this).attr('data-called-by')).parents().eq(2).children('.swatch_window'),

                    /**
                     * @this {iframe}
                     */
                    iframeHandler = function () {
                        var imageParams = $.parseJSON($(this).contents().find('body').html()),
                            fullMediaUrl = imageParams['swatch_path'] + imageParams['file_path'];

                        container.prev('input').val(imageParams['file_path']);
                        container.css({
                            'background-image': 'url(' + fullMediaUrl + ')',
                            'background-size': 'cover'
                        });
                        container.parent().removeClass('unavailable');
                    };

                swatchComponents.iframe.off('load');
                swatchComponents.iframe.load(iframeHandler);
                swatchComponents.form.submit();
                $(this).val('');
            });

            /**
             * Register event for choose "upload image" option
             */
            $(container).on('click', '.btn_choose_file_upload', function () {
                swatchComponents.inputFile.attr('data-called-by', $(this).attr('id'));
                swatchComponents.inputFile.click();
            });

            /**
             * Register event for remove option
             */
            $(container).on('click', '.btn_remove_swatch', function () {
                var optionPanel = $(this).parents().eq(2);

                optionPanel.children('input').val('');
                optionPanel.children('.swatch_window').css('background', '');
                optionPanel.addClass('unavailable');
                jQuery('.swatch_sub-menu_container').hide();
            });

            /**
             * Toggle color upload chooser
             */
            $(container).on('click', '.swatch_window', function () {
                jQuery('.swatch_sub-menu_container').hide();
                $(this).next('div').toggle();
            });
        });
    }

    return Abstract.extend({
        defaults: {
            elementId: 0,
            elementName: '',
            value: ''
        },

        /**
         * Extends instance with defaults, extends config with formatted values
         *     and options, and invokes initialize method of AbstractElement class.
         *     If instance's 'customEntry' property is set to true, calls 'initInput'
         */
        initialize: function () {
            this._super();

            if (this.customEntry) {
                this.initInput();
            }

            if (this.filterBy) {
                this.initFilter();
            }

            return this;
        },

        /**
         * Parses options and merges the result with instance
         *
         * @param  {Object} config
         * @returns {Object} Chainable.
         */
        initConfig: function (config) {
            this._super();

            this.elementId = rg.get(this.parentName).recordId;
            this.elementName = 'option_' + this.elementId;

            var elementName = this.elementName;
            var value = this.value;

            var waiting = function () {
                var element = jQuery('#swatch_container_option_' + elementName)[0];
                if (_.isUndefined(element) || _.isEmpty(element)) {
                    setTimeout(waiting, 100);
                } else {
                    oldCode(value, element.parentElement);
                }
            };

            waiting();

            return this;
        }
    });
});
