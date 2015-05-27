/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global FORM_KEY*/
define([
    'jquery',
    'jquery/ui',
    'Magento_Ui/js/dialog/dialog',
    'mage/translate',
    'mage/backend/tree-suggest',
    'mage/backend/validation'
], function ($) {
    'use strict';

    var clearParentCategory = function () {
        $('#new_category_parent').find('option').each(function () {
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

            $.validator.addMethod('validate-parent-category', function () {
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
                type: 'slide',
                dialogClass: 'mage-new-category-dialog form-inline',
                title: $.mage.__('Create Category'),
                buttons: [{
                    text: $.mage.__('Create Category'),
                    class: 'action-primary',
                    click: function () {
                        if (!newCategoryForm.valid()) {
                            return;
                        }
                        var thisButton = $(this);

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
                        }).success(function (data) {
                            if (!data.error) {
                                var $suggest = $('#category_ids-suggest');

                                $suggest.trigger('selectItem', {
                                    id: data.category.entity_id,
                                    label: data.category.name
                                });
                                $('#new_category_name, #new_category_parent-suggest').val('');
                                $suggest.val('');
                                clearParentCategory();
                                widget.element.trigger('closeDialog');
                            } else {
                                $('#new_category_messages').html(data.messages);
                            }
                        }).complete(
                            function () {
                                thisButton.prop('disabled', false);
                            }
                        );
                    }
                }]
            });
        }
    });

    return $.mage.newCategoryDialog;
});
