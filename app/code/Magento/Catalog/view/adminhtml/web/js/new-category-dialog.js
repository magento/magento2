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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
/*global FORM_KEY*/
define([
    "jquery",
    "jquery/ui",
    "jquery/template",
    "mage/translate",
    "mage/backend/tree-suggest",
    "mage/backend/validation"
], function($){
    'use strict';
    var clearParentCategory = function () {
        $('#new_category_parent').find('option').each(function(){
            $('#new_category_parent-suggest').treeSuggest('removeOption', null, this);
        });
    };

    $.widget('mage.newCategoryDialog', {
        _create: function () {
            var widget = this;
            $('#new_category_parent').before($('<input>', {
                id: 'new_category_parent-suggest',
                placeholder: $.mage.__('start typing to search category')
            }));

            $('#new_category_parent-suggest').treeSuggest(this.options.suggestOptions)
                .on('suggestbeforeselect', function (event) {
                    clearParentCategory();
                    $(event.target).treeSuggest('close');
                    $('#new_category_name').focus();
                });

            $.validator.addMethod('validate-parent-category', function() {
                return $('#new_category_parent').val() || $('#new_category_parent-suggest').val() === '';
            }, $.mage.__('Choose existing category.'));
            var newCategoryForm = $('#new_category_form');
            newCategoryForm.mage('validation', {
                errorPlacement: function (error, element) {
                    error.insertAfter(element.is('#new_category_parent') ?
                        $('#new_category_parent-suggest').closest('.mage-suggest') :
                        element);
                }
            }).on('highlight.validate', function (e) {
                var options = $(this).validation('option');
                if ($(e.target).is('#new_category_parent')) {
                    options.highlight($('#new_category_parent-suggest').get(0),
                        options.errorClass, options.validClass || '');
                }
            });

            this.element.dialog({
                title: $.mage.__('Create Category'),
                autoOpen: false,
                minWidth: 560,
                dialogClass: 'mage-new-category-dialog form-inline',
                modal: true,
                multiselect: true,
                resizable: false,
                open: function() {
                    // fix for suggest field - overlapping dialog z-index
                    $('#new_category_parent-suggest').css('z-index', $.ui.dialog.maxZ + 1);
                    var enteredName = $('#category_ids-suggest').val();
                    $('#new_category_name').val(enteredName);
                    if (enteredName === '') {
                        $('#new_category_name').focus();
                    }
                    $('#new_category_messages').html('');
                },
                close: function() {
                    $('#new_category_name, #new_category_parent-suggest').val('');
                    var validationOptions = newCategoryForm.validation('option');
                    validationOptions.unhighlight($('#new_category_parent-suggest').get(0),
                        validationOptions.errorClass, validationOptions.validClass || '');
                    newCategoryForm.validation('clearError');
                    $('#category_ids-suggest').focus();
                },
                buttons: [{
                    text: $.mage.__('Create Category'),
                    'class': 'action-create primary',
                    'data-action': 'save',
                    click: function(event) {
                        if (!newCategoryForm.valid()) {
                            return;
                        }

                        var thisButton = $(event.target).closest('[data-action=save]');
                        thisButton.prop('disabled', true);
                        $.ajax({
                            type: 'POST',
                            url: widget.options.saveCategoryUrl,
                            data: {
                                general: {
                                    name: $('#new_category_name').val(),
                                    is_active: 1,
                                    include_in_menu: 1
                                },
                                parent: $('#new_category_parent').val(),
                                use_config: ['available_sort_by', 'default_sort_by'],
                                form_key: FORM_KEY,
                                return_session_messages_only: 1
                            },
                            dataType: 'json',
                            context: $('body')
                        })
                            .success(
                                function (data) {
                                    if (!data.error) {
                                        $('#category_ids-suggest').trigger('selectItem', {
                                            id: data.category.entity_id,
                                            label: data.category.name
                                        });
                                        $('#new_category_name, #new_category_parent-suggest').val('');
                                        $('#category_ids-suggest').val('');
                                        clearParentCategory();
                                        widget.element.dialog('close');
                                    } else {
                                        $('#new_category_messages').html(data.messages);
                                    }
                                }
                            )
                            .complete(
                                function () {
                                    thisButton.prop('disabled', false);
                                }
                            );
                    }
                }]
            });
        }
    });
});