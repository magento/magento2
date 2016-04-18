/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable no-undef */
// jscs:disable jsDoc

define([
    'jquery',
    'mage/template',
    'uiRegistry',
    'prototype',
    'jquery/ui'
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

                    if (!this.totalItems) {
                        data.checked = 'checked';
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
                initColorPicker: function () {
                    var element = this,
                        hiddenColorPicker = !jQuery(element).data('colorpickerId');

                    jQuery(this).ColorPicker({
                        onShow: function () {
                            var color = jQuery(element).parent().parent().prev().prev('input').val(),
                                menu = jQuery(this).parents('.swatch_submenu_container');

                            menu.hide();
                            jQuery(element).ColorPickerSetColor(color);
                        },
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
                updateItemsCountField: function () {
                    $('swatch-visual-option-count-check').value = this.totalItems > 0 ? '1' : '';
                },
                enableNewOptionDeleteButton: function (id) {
                    $$('#delete_button_swatch_container_' + id + ' button').each(function (button) {
                        button.enable();
                        button.removeClassName('disabled');
                    });
                },
                bindRemoveButtons: function () {
                    jQuery('#swatch-visual-options-panel').on('click', '.delete-option', this.remove.bind(this));
                },
                render: function () {
                    Element.insert($$('[data-role=swatch-visual-options-container]')[0], this.elements);
                    this.elements = '';
                },
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
                element.parents('.swatch_submenu_container').length === 1 ||
                element.next('div.swatch_submenu_container').length === 1
            ) {
                return true;
            }
            jQuery('.swatch_submenu_container').hide();
        });

        if (config.isSortable) {
            jQuery(function ($) {
                $('[data-role=swatch-visual-options-container]').sortable({
                    distance: 8,
                    tolerance: 'pointer',
                    cancel: 'input, button',
                    axis: 'y',
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
    };
});
