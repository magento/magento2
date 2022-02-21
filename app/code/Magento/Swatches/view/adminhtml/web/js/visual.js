/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global FORM_KEY */

/**
 * @api
 */
define([
    'jquery',
    'mage/template',
    'uiRegistry',
    'jquery/colorpicker/js/colorpicker',
    'prototype',
    'jquery/ui',
    'validation'
], function (jQuery, mageTemplate, rg) {
    'use strict';

    return function (config) {
        var swatchOptionVisualDefaultInputType = 'radio',
            swatchVisualOption = {
                table: $('swatch-visual-options-table'),
                itemCount: 0,
                totalItems: 0,
                rendered: 0,
                isReadOnly: config.isReadOnly,
                template: mageTemplate('#swatch-visual-row-template'),

                /**
                 * Add new option using template
                 *
                 * @param {Object} data
                 * @param {Object} render
                 */
                add: function (data, render) {
                    var isNewOption = false,
                        element;

                    if (typeof data.id == 'undefined') {
                        data = {
                            'id': 'option_' + this.itemCount,
                            'sort_order': this.itemCount + 1,
                            'empty_class': 'unavailable'
                        };
                        isNewOption = true;
                    } else if (data.defaultswatch0 === '') {
                        data['empty_class'] = 'unavailable';
                    }

                    if (!data.intype) {
                        data.intype = swatchOptionVisualDefaultInputType;
                    }

                    element = this.template({
                        data: data
                    });

                    if (isNewOption && !this.isReadOnly) {
                        this.enableNewOptionDeleteButton(data.id);
                    }
                    this.itemCount++;
                    this.totalItems++;
                    this.elements += element;

                    if (render) {
                        this.render();
                    }
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
                },

                /**
                 * Enable delete button for new option
                 *
                 * @param {String} id
                 */
                enableNewOptionDeleteButton: function (id) {
                    $$('#delete_button_swatch_container_' + id + ' button').each(function (button) {
                        button.enable();
                        button.removeClassName('disabled');
                    });
                },

                /**
                 * Bind remove button
                 */
                bindRemoveButtons: function () {
                    jQuery('#swatch-visual-options-panel').on('click', '.delete-option', this.remove.bind(this));
                },

                /**
                 * Render options
                 */
                render: function () {
                    Element.insert($$('[data-role=swatch-visual-options-container]')[0], this.elements);
                    this.elements = '';
                },

                /**
                 * Render elements with delay (performance fix)
                 *
                 * @param {Object} data
                 * @param {Number} from
                 * @param {Number} step
                 * @param {Number} delay
                 * @returns {Boolean}
                 */
                renderWithDelay: function (data, from, step, delay) {
                    var arrayLength = data.length,
                        len;

                    for (len = from + step; from < len && from < arrayLength; from++) {
                        this.add(data[from]);
                    }
                    this.render();

                    if (from === arrayLength) {
                        this.updateItemsCountField();
                        this.rendered = 1;
                        jQuery('body').trigger('processStop');

                        return true;
                    }
                    setTimeout(this.renderWithDelay.bind(this, data, from, step, delay), delay);
                },

                /**
                 * Ignore validate action
                 */
                ignoreValidate: function () {
                    var ignore = '.ignore-validate input, ' +
                        '.ignore-validate select, ' +
                        '.ignore-validate textarea';

                    jQuery('#edit_form').data('validator').settings.forceIgnore = ignore;
                }
            };

        if ($('add_new_swatch_visual_option_button')) {
            Event.observe(
                'add_new_swatch_visual_option_button',
                'click',
                swatchVisualOption.add.bind(swatchVisualOption, {}, true)
            );
        }

        jQuery('#swatch-visual-options-panel').on('render', function () {
            swatchVisualOption.ignoreValidate();

            if (swatchVisualOption.rendered) {
                return false;
            }
            jQuery('body').trigger('processStart');
            swatchVisualOption.renderWithDelay(config.attributesData, 0, 100, 300);
            swatchVisualOption.bindRemoveButtons();
            jQuery('#swatch-visual-options-panel').on(
                'click',
                '.colorpicker_handler',
                swatchVisualOption.initColorPicker
            );
        });
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

        if (config.isSortable) {
            jQuery(function ($) {
                $('[data-role=swatch-visual-options-container]').sortable({
                    distance: 8,
                    tolerance: 'pointer',
                    cancel: 'input, button',
                    axis: 'y',

                    /**
                     * Update component
                     */
                    update: function () {
                        $('[data-role=swatch-visual-options-container] [data-role=order]').each(
                            function (index, element) {
                                $(element).val(index + 1);
                            }
                        );
                    }
                });
            });
        }

        window.swatchVisualOption = swatchVisualOption;
        window.swatchOptionVisualDefaultInputType = swatchOptionVisualDefaultInputType;

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

                    this.iframe = $('<iframe></iframe>', {
                        id:  'upload_iframe',
                        name: 'upload_iframe'
                    }).appendTo(this.wrapper);

                    this.form = $('<form></form>', {
                        id: 'swatch_form_image_upload',
                        name: 'swatch_form_image_upload',
                        target: 'upload_iframe',
                        method: 'post',
                        enctype: 'multipart/form-data',
                        class: 'ignore-validate',
                        action: config.uploadActionUrl
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
            $(document).on('click', '.btn_choose_file_upload', function () {
                swatchComponents.inputFile.attr('data-called-by', $(this).attr('id'));
                swatchComponents.inputFile.trigger('click');
            });

            /**
             * Register event for remove option
             */
            $(document).on('click', '.btn_remove_swatch', function () {
                var optionPanel = $(this).parents().eq(2);

                optionPanel.children('input').val('');
                optionPanel.children('.swatch_window').css('background', '');

                optionPanel.addClass('unavailable');

                jQuery('.swatch_sub-menu_container').hide();
            });

            /**
             * Toggle color upload chooser
             */
            $(document).on('click', '.swatches-visual-col', function () {
                var currentElement = $(this).find('.swatch_sub-menu_container');

                jQuery('.swatch_sub-menu_container').not(currentElement).hide();
                currentElement.toggle();
            });
        });
    };
});
