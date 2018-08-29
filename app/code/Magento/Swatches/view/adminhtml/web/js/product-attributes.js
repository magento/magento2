/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/prompt',
    'uiRegistry',
    'collapsable'
], function ($, alert, prompt, rg) {
    'use strict';

    return function (optionConfig) {
        var swatchProductAttributes = {
                frontendInput: $('#frontend_input'),
                isFilterable: $('#is_filterable'),
                isFilterableInSearch: $('#is_filterable_in_search'),
                backendType: $('#backend_type'),
                usedForSortBy: $('#used_for_sort_by'),
                frontendClass: $('#frontend_class'),
                isWysiwygEnabled: $('#is_wysiwyg_enabled'),
                isHtmlAllowedOnFront: $('#is_html_allowed_on_front'),
                isRequired: $('#is_required'),
                isUnique: $('#is_unique'),
                defaultValueText: $('#default_value_text'),
                defaultValueTextarea: $('#default_value_textarea'),
                defaultValueDate: $('#default_value_date'),
                defaultValueYesno: $('#default_value_yesno'),
                isGlobal: $('#is_global'),
                useProductImageForSwatch: $('#use_product_image_for_swatch'),
                updateProductPreviewImage: $('#update_product_preview_image'),
                usedInProductListing: $('#used_in_product_listing'),
                isVisibleOnFront: $('#is_visible_on_front'),
                position: $('#position'),
                attrTabsFront: $('#product_attribute_tabs_front'),

                /**
                 * @returns {*|jQuery|HTMLElement}
                 */
                get tabsFront() {
                    return this.attrTabsFront.length ? this.attrTabsFront.closest('li') : $('#front_fieldset-wrapper');
                },
                selectFields: ['select', 'multiselect', 'price', 'swatch_text', 'swatch_visual'],

                /**
                 * @this {swatchProductAttributes}
                 */
                toggleApplyVisibility: function (select) {
                    if ($(select).val() === 1) {
                        $(select).next('select').removeClass('no-display');
                        $(select).next('select').removeClass('ignore-validate');
                    } else {
                        $(select).next('select').addClass('no-display');
                        $(select).next('select').addClass('ignore-validate');
                        $(select).next('select option:selected').each(function () {
                            this.selected = false;
                        });
                    }
                },

                /**
                 * @this {swatchProductAttributes}
                 */
                checkOptionsPanelVisibility: function () {
                    var selectOptionsPanel = $('#manage-options-panel'),
                        visualOptionsPanel = $('#swatch-visual-options-panel'),
                        textOptionsPanel = $('#swatch-text-options-panel');

                    this._hidePanel(selectOptionsPanel);
                    this._hidePanel(visualOptionsPanel);
                    this._hidePanel(textOptionsPanel);

                    switch (this.frontendInput.val()) {
                        case 'swatch_visual':
                            this._showPanel(visualOptionsPanel);
                            break;

                        case 'swatch_text':
                            this._showPanel(textOptionsPanel);
                            break;

                        case 'select':
                        case 'multiselect':
                            this._showPanel(selectOptionsPanel);
                            break;
                    }
                },

                /**
                 * @this {swatchProductAttributes}
                 */
                bindAttributeInputType: function () {
                    this.checkOptionsPanelVisibility();
                    this.switchDefaultValueField();

                    if (!~$.inArray(this.frontendInput.val(), this.selectFields)) {
                        // not in array
                        this.isFilterable.selectedIndex = 0;
                        this._disable(this.isFilterable);
                        this._disable(this.isFilterableInSearch);
                    } else {
                        // in array
                        this._enable(this.isFilterable);
                        this._enable(this.isFilterableInSearch);
                        this.backendType.val('int');
                    }

                    if (this.frontendInput.val() === 'multiselect' ||
                        this.frontendInput.val() === 'gallery' ||
                        this.frontendInput.val() === 'textarea'
                    ) {
                        this._disable(this.usedForSortBy);
                    } else {
                        this._enable(this.usedForSortBy);
                    }

                    if (this.frontendInput.val() === 'swatch_text') {
                        $('.swatch-text-field-0').addClass('required-option');
                    } else {
                        $('.swatch-text-field-0').removeClass('required-option');
                    }

                    this.setRowVisibility(this.isWysiwygEnabled, false);
                    this.setRowVisibility(this.isHtmlAllowedOnFront, false);

                    switch (this.frontendInput.val()) {
                        case 'textarea':
                            this.setRowVisibility(this.isWysiwygEnabled, true);

                            if (this.isWysiwygEnabled.val() === '0') {
                                this._enable(this.isHtmlAllowedOnFront);
                            }
                            this.frontendClass.val('');
                            this._disable(this.frontendClass);
                            break;

                        case 'text':
                            this.setRowVisibility(this.isHtmlAllowedOnFront, true);
                            this._enable(this.frontendClass);
                            break;

                        case 'select':
                        case 'multiselect':
                            this.setRowVisibility(this.isHtmlAllowedOnFront, true);
                            this.frontendClass.val('');
                            this._disable(this.frontendClass);
                            break;
                        default:
                            this.frontendClass.val('');
                            this._disable(this.frontendClass);
                    }

                    this.switchIsFilterable();
                },

                /**
                 * @this {swatchProductAttributes}
                 */
                switchIsFilterable: function () {
                    if (this.isFilterable.selectedIndex === 0) {
                        this._disable(this.position);
                    } else {
                        this._enable(this.position);
                    }
                },

                /**
                 * @this {swatchProductAttributes}
                 */
                switchDefaultValueField: function () {
                    var currentValue = this.frontendInput.val(),
                        defaultValueTextVisibility = false,
                        defaultValueTextareaVisibility = false,
                        defaultValueDateVisibility = false,
                        defaultValueYesnoVisibility = false,
                        scopeVisibility = true,
                        useProductImageForSwatch = false,
                        defaultValueUpdateImage = false,
                        optionDefaultInputType = '',
                        isFrontTabHidden = false,
                        thing = this;

                    if (!this.frontendInput.length) {
                        return;
                    }

                    switch (currentValue) {
                        case 'select':
                            optionDefaultInputType = 'radio';
                            break;

                        case 'multiselect':
                            optionDefaultInputType = 'checkbox';
                            break;

                        case 'date':
                            defaultValueDateVisibility = true;
                            break;

                        case 'boolean':
                            defaultValueYesnoVisibility = true;
                            break;

                        case 'textarea':
                        case 'texteditor':
                            defaultValueTextareaVisibility = true;
                            break;

                        case 'media_image':
                            defaultValueTextVisibility = false;
                            break;

                        case 'price':
                            scopeVisibility = false;
                            break;

                        case 'swatch_visual':
                            useProductImageForSwatch = true;
                            defaultValueUpdateImage = true;
                            defaultValueTextVisibility = false;
                            break;

                        case 'swatch_text':
                            useProductImageForSwatch = false;
                            defaultValueUpdateImage = true;
                            defaultValueTextVisibility = false;
                            break;
                        default:
                            defaultValueTextVisibility = true;
                            break;
                    }

                    delete optionConfig.hiddenFields['swatch_visual'];
                    delete optionConfig.hiddenFields['swatch_text'];

                    if (currentValue === 'media_image') {
                        this.tabsFront.hide();
                        this.setRowVisibility(this.isRequired, false);
                        this.setRowVisibility(this.isUnique, false);
                        this.setRowVisibility(this.frontendClass, false);
                    } else if (optionConfig.hiddenFields[currentValue]) {
                        $.each(optionConfig.hiddenFields[currentValue], function (key, option) {
                            switch (option) {
                                case '_front_fieldset':
                                    thing.tabsFront.hide();
                                    isFrontTabHidden = true;
                                    break;

                                case '_default_value':
                                    defaultValueTextVisibility = false;
                                    defaultValueTextareaVisibility = false;
                                    defaultValueDateVisibility = false;
                                    defaultValueYesnoVisibility = false;
                                    break;

                                case '_scope':
                                    scopeVisibility = false;
                                    break;
                                default:
                                    thing.setRowVisibility($('#' + option), false);
                            }
                        });

                        if (!isFrontTabHidden) {
                            thing.tabsFront.show();
                        }

                    } else {
                        this.tabsFront.show();
                        this.showDefaultRows();
                    }

                    this.setRowVisibility(this.defaultValueText, defaultValueTextVisibility);
                    this.setRowVisibility(this.defaultValueTextarea, defaultValueTextareaVisibility);
                    this.setRowVisibility(this.defaultValueDate, defaultValueDateVisibility);
                    this.setRowVisibility(this.defaultValueYesno, defaultValueYesnoVisibility);
                    this.setRowVisibility(this.isGlobal, scopeVisibility);

                    /* swatch attributes */
                    this.setRowVisibility(this.useProductImageForSwatch, useProductImageForSwatch);
                    this.setRowVisibility(this.updateProductPreviewImage, defaultValueUpdateImage);

                    $('input[name=\'default[]\']').each(function () {
                        $(this).attr('type', optionDefaultInputType);
                    });
                },

                /**
                 * @this {swatchProductAttributes}
                 */
                showDefaultRows: function () {
                    this.setRowVisibility(this.isRequired, true);
                    this.setRowVisibility(this.isUnique, true);
                    this.setRowVisibility(this.frontendClass, true);
                },

                /**
                 * @param {Object} el
                 * @param {Boolean} isVisible
                 * @this {swatchProductAttributes}
                 */
                setRowVisibility: function (el, isVisible) {
                    if (isVisible) {
                        el.show();
                        el.closest('.field').show();
                    } else {
                        el.hide();
                        el.closest('.field').hide();
                    }
                },

                /**
                 * @param {Object} el
                 * @this {swatchProductAttributes}
                 */
                _disable: function (el) {
                    el.attr('disabled', 'disabled');
                },

                /**
                 * @param {Object} el
                 * @this {swatchProductAttributes}
                 */
                _enable: function (el) {
                    if (!el.attr('readonly')) {
                        el.removeAttr('disabled');
                    }
                },

                /**
                 * @param {Object} el
                 * @this {swatchProductAttributes}
                 */
                _showPanel: function (el) {
                    el.closest('.fieldset').show();
                    this._render(el.attr('id'));
                },

                /**
                 * @param {Object} el
                 * @this {swatchProductAttributes}
                 */
                _hidePanel: function (el) {
                    el.closest('.fieldset').hide();
                },

                /**
                 * @param {String} id
                 * @this {swatchProductAttributes}
                 */
                _render: function (id) {
                    rg.get(id, function () {
                        $('#' + id).trigger('render');
                    });
                },

                /**
                 * @param {String} promptMessage
                 * @this {swatchProductAttributes}
                 */
                saveAttributeInNewSet: function (promptMessage) {

                    prompt({
                        content: promptMessage,
                        actions: {

                            /**
                             * @param {String} val
                             * @this {actions}
                             */
                            confirm: function (val) {
                                var rules = ['required-entry', 'validate-no-html-tags'],
                                    newAttributeSetNameInputId = $('#new_attribute_set_name'),
                                    editForm = $('#edit_form'),
                                    newAttributeSetName = val,
                                    i;

                                if (!newAttributeSetName) {
                                    return;
                                }

                                for (i = 0; i < rules.length; i++) {
                                    if (!$.validator.methods[rules[i]](newAttributeSetName)) {
                                        alert({
                                            content: $.validator.messages[rules[i]]
                                        });

                                        return;
                                    }
                                }

                                if (newAttributeSetNameInputId.length) {
                                    newAttributeSetNameInputId.val(newAttributeSetName);
                                } else {
                                    editForm.append(new Element('input', {
                                            type: 'hidden',
                                            id: newAttributeSetNameInputId,
                                            name: 'new_attribute_set_name',
                                            value: newAttributeSetName
                                        })
                                    );
                                }
                                // Temporary solution will replaced after refactoring of attributes functionality
                                editForm.triggerHandler('save');
                            }
                        }
                    });
                }
            };

        $(function () {
            var editForm = $('#edit_form');

            $('#frontend_input').bind('change', function () {
                swatchProductAttributes.bindAttributeInputType();
            });
            $('#is_filterable').bind('change', function () {
                swatchProductAttributes.switchIsFilterable();
            });

            swatchProductAttributes.bindAttributeInputType();

            // @todo: refactor collapsable component
            $('.attribute-popup .collapse, [data-role="advanced_fieldset-content"]')
                .collapsable()
                .collapse('hide');

            editForm.on('submit', function () {
                var activePanel,
                    swatchValues = [],
                    swatchVisualPanel = $('#swatch-visual-options-panel'),
                    swatchTextPanel = $('#swatch-text-options-panel');

                activePanel = swatchTextPanel.is(':visible') ? swatchTextPanel : swatchVisualPanel;

                activePanel
                    .find('table input')
                    .each(function () {
                        swatchValues.push(this.name + '=' + $(this).val());
                    });

                $('<input>')
                    .attr({
                        type: 'hidden',
                        name: 'serialized_swatch_values'
                    })
                    .val(JSON.stringify(swatchValues))
                    .prependTo(editForm);

                [swatchVisualPanel, swatchTextPanel].forEach(function (el) {
                    $(el).find('table')
                        .replaceWith($('<div>').text($.mage.__('Sending swatch values as package.')));
                });
            });
        });

        window.saveAttributeInNewSet = swatchProductAttributes.saveAttributeInNewSet;
        window.toggleApplyVisibility = swatchProductAttributes.toggleApplyVisibility;
    };
});
