/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable no-undef */
// jscs:disable jsDoc

define([
    'jquery',
    'mage/template',
    'uiRegistry',
    'jquery/ui',
    'prototype',
    'form',
    'validation',
    'mage/translate'
], function (jQuery, mageTemplate, rg) {
    'use strict';

    return function (config) {
        var optionPanel = jQuery('#manage-options-panel'),
            editForm = jQuery('#edit_form'),
            attributeOption = {
                table: $('attribute-options-table'),
                itemCount: 0,
                totalItems: 0,
                rendered: 0,
                template: mageTemplate('#row-template'),
                newOptionClass: 'new-option',
                isReadOnly: config.isReadOnly,
                add: function (data, render) {
                    var isNewOption = false,
                        element;

                    if (typeof data.id == 'undefined') {
                        data = {
                            'id': 'option_' + this.itemCount,
                            'sort_order': this.itemCount + 1,
                            'rowClasses': this.newOptionClass
                        };
                        isNewOption = true;
                    }

                    if (!data.intype) {
                        data.intype = this.getOptionInputType();
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
                        this.updateItemsCountField();
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

                    if (element.hasClassName(this.newOptionClass)) {
                        element.remove();
                    }
                },
                updateItemsCountField: function () {
                    $('option-count-check').value = this.totalItems > 0 ? '1' : '';
                },
                enableNewOptionDeleteButton: function (id) {
                    $$('#delete_button_container_' + id + ' button').each(function (button) {
                        button.enable();
                        button.removeClassName('disabled');
                    });
                },
                bindRemoveButtons: function () {
                    jQuery('#swatch-visual-options-panel').on('click', '.delete-option', this.remove.bind(this));
                },
                render: function () {
                    Element.insert($$('[data-role=options-container]')[0], this.elements);
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
                },
                getOptionInputType: function () {
                    var optionDefaultInputType = 'radio';

                    if ($('frontend_input') && $('frontend_input').value === 'multiselect') {
                        optionDefaultInputType = 'checkbox';
                    }

                    return optionDefaultInputType;
                }
            },
            tableBody = jQuery(),
            activePanelClass = 'selected-type-options';

        if ($('add_new_option_button')) {
            Event.observe('add_new_option_button', 'click', attributeOption.add.bind(attributeOption, {}, true));
        }
        $('manage-options-panel').on('click', '.delete-option', function (event) {
            attributeOption.remove(event);
        });

        optionPanel.on('render', function () {
            attributeOption.ignoreValidate();

            if (attributeOption.rendered) {
                return false;
            }
            jQuery('body').trigger('processStart');
            attributeOption.renderWithDelay(config.attributesData, 0, 100, 300);
            attributeOption.bindRemoveButtons();
        });

        if (config.isSortable) {
            jQuery(function ($) {
                $('[data-role=options-container]').sortable({
                    distance: 8,
                    tolerance: 'pointer',
                    cancel: 'input, button',
                    axis: 'y',
                    update: function () {
                        $('[data-role=options-container] [data-role=order]').each(function (index, element) {
                            $(element).val(index + 1);
                        });
                    }
                });
            });
        }
        editForm.on('beforeSubmit', function () {
            var optionContainer = optionPanel.find('table tbody'),
                optionsValues;

            if (optionPanel.hasClass(activePanelClass)) {
                optionsValues = jQuery.map(
                    optionContainer.find('tr'),
                    function (row) {
                        return jQuery(row).find('input, select, textarea').serialize();
                    }
                );
                jQuery('<input>')
                    .attr({
                        type: 'hidden',
                        name: 'serialized_options'
                    })
                    .val(JSON.stringify(optionsValues))
                    .prependTo(editForm);
            }
            tableBody = optionContainer.detach();
        });
        editForm.on('afterValidate.error highlight.validate', function () {
            if (optionPanel.hasClass(activePanelClass)) {
                optionPanel.find('table').append(tableBody);
                jQuery('input[name="serialized_options"]').remove();
            }
        });
        window.attributeOption = attributeOption;
        window.optionDefaultInputType = attributeOption.getOptionInputType();

        rg.set('manage-options-panel', attributeOption);
    };
});
