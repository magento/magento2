/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global $break $ $$ */

define([
    'jquery',
    'mage/template',
    'uiRegistry',
    'jquery/ui',
    'prototype'
], function (jQuery, mageTemplate, rg) {
    'use strict';

    return function (config) {
        var swatchOptionTextDefaultInputType = 'radio',
            swatchTextOption = {
                table: $('swatch-text-options-table'),
                itemCount: 0,
                totalItems: 0,
                rendered: 0,
                isReadOnly: config.isReadOnly,
                template: mageTemplate('#swatch-text-row-template'),

                /**
                 * Add option
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
                            'sort_order': this.itemCount + 1
                        };
                        isNewOption = true;
                    }

                    if (!data.intype) {
                        data.intype = swatchOptionTextDefaultInputType;
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
                 * Remove option
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
                    $('swatch-text-option-count-check').value = this.totalItems > 0 ? '1' : '';
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
                    jQuery('#swatch-text-options-panel').on('click', '.delete-option', this.remove.bind(this));
                },

                /**
                 * Render action
                 */
                render: function () {
                    Element.insert($$('[data-role=swatch-text-options-container]')[0], this.elements);
                    this.elements = '';
                },

                /**
                 * Render action with delay (performance fix)
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

        if ($('add_new_swatch_text_option_button')) {
            Event.observe(
                'add_new_swatch_text_option_button',
                'click',
                swatchTextOption.add.bind(swatchTextOption, true)
            );
        }
        jQuery('#swatch-text-options-panel').on('render', function () {
            swatchTextOption.ignoreValidate();

            if (swatchTextOption.rendered) {
                return false;
            }
            jQuery('body').trigger('processStart');
            swatchTextOption.renderWithDelay(config.attributesData, 0, 100, 300);
            swatchTextOption.bindRemoveButtons();
        });

        if (config.isSortable) {
            jQuery(function ($) {
                $('[data-role=swatch-text-options-container]').sortable({
                    distance: 8,
                    tolerance: 'pointer',
                    cancel: 'input, button',
                    axis: 'y',

                    /**
                     * Update components
                     */
                    update: function () {
                        $('[data-role=swatch-text-options-container] [data-role=order]').each(
                            function (index, element) {
                                $(element).val(index + 1);
                            }
                        );
                    }
                });
            });
        }

        jQuery(document).ready(function () {
            if (jQuery('#frontend_input').val() !== 'swatch_text') {
                jQuery('.swatch-text-field-0').removeClass('required-option');
            }
        });

        window.swatchTextOption = swatchTextOption;
        window.swatchOptionTextDefaultInputType = swatchOptionTextDefaultInputType;

        rg.set('swatch-text-options-panel', swatchTextOption);
    };
});
