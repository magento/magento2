/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global FORM_KEY*/
define([
    'jquery',
    'jquery/ui',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'mage/backend/tree-suggest',
    'mage/backend/validation'
], function ($) {
    'use strict';

    $.widget('mage.newVideoDialog', {

        clickedElement : '',

        _create: function () {
            var widget = this;
            var newVideoForm = $('#new_video_form');
            var uploader = $('#new_video_screenshot');
            this.toggleButtons();

            this.element.modal({
                type: 'slide',
                modalClass: 'mage-new-video-dialog form-inline',
                title: $.mage.__('Create Video'),
                buttons: [{
                    text: $.mage.__('Create Video'),
                    class: 'action-primary video-create-button',
                    click: function (e) {
                        var newVideoForm = $('#new_video_form');
                        newVideoForm.mage('validation', {
                            errorPlacement: function (error, element) {
                                error.insertAfter(element);
                            }
                        }).on('highlight.validate', function (e) {
                            var options = $(this).validation('option');
                        });
                        newVideoForm.validation();
                        if (!newVideoForm.valid()) {
                            return;
                        }
                        var file = $('#new_video_screenshot').get(0).files[0];
                        var inputFile = uploader.val('').clone(true);
                        $('#new_video_screenshot').fileupload().fileupload(
                            'send',
                            {
                                files: file,
                                url: widget.options.saveVideoUrl,
                            }
                        ).success(
                            function(result, textStatus, jqXHR)
                            {
                                var data = JSON.parse(result);
                                var formData = $.each($('#new_video_form').serializeArray(), function(i, field) {
                                    data[field.name] = field.value;
                                });
                                data['disabled'] = $('#new_video_disabled').prop('checked') ? 1 : 0;
                                data['media_type'] = 'external-video';
                                widget.saveImageRoles(data['file']);
                                $('#media_gallery_content').trigger('addItem', data);
                                $('#new-video').modal('closeModal');
                                uploader.replaceWith(inputFile);
                            }
                        );
                    }
                },
                {
                    text: $.mage.__('Edit'),
                    class: 'action-primary video-edit',
                    click: function (e) {
                        var newVideoForm = $('#new_video_form');
                        newVideoForm.mage('validation', {
                            errorPlacement: function (error, element) {
                                error.insertAfter(element);
                            }
                        }).on('highlight.validate', function (e) {
                            var options = $(this).validation('option');
                        });
                        newVideoForm.validation();
                        if (!newVideoForm.valid()) {
                            return;
                        }
                        var inputFile = uploader.val('').clone(true);
                        var mediaFields = $('input[name*="' + $('#item_id').val() + '"]');
                        $.each(mediaFields, function(i, el){
                            var fieldHash = $('#item_id').val();
                            var start = el.name.indexOf(fieldHash) + $('#item_id').val().length + 1;
                            var fieldName = el.name.substring(start, el.name.length - 1);
                            if ($('#' + fieldName).length > 0) {
                                $('input[name*="' + $('#item_id').val() + '[' + fieldName + ']"]').val($('#' + fieldName).val());
                            }
                        });
                        var flagChecked = $('#new_video_disabled').prop('checked') ? 1 : 0;
                        $('input[name*="' + $('#item_id').val() + '[disabled]"]').val(flagChecked);

                        if (flagChecked == true) {
                            $('[name*="' + $('#item_id').val() + '"]').siblings('.image-fade').css('visibility', 'visible');
                        } else {
                            $('[name*="' + $('#item_id').val() + '"]').siblings('.image-fade').css('visibility', 'hidden');
                        }

                        widget.saveImageRoles($('#file_name').val());
                        uploader.replaceWith(inputFile);
                        $('#new-video').modal('closeModal');
                    }
                }],
                opened: function(e) {
                    $('#video_url').focus();
                    $(document).on('click', '.item.image', function() {
                        var formFields = $('#new_video_form').find('.edited-data');
                        var container = $(this);

                        $.each(formFields, function (i, field) {
                            $(field).val(container.find('input[name*="' + field.name + '"]').val());
                        });

                        var flagChecked = (container.find('input[name*="disabled"]').val() == 1) ? true : false;
                        $('#new_video_disabled').prop('checked', flagChecked);

                        var file = $('#file_name').val(container.find('input[name*="file"]').val());

                        $.each($('.video_image_role'), function(){
                            $(this).prop('checked', false).prop('disabled', false);
                        });

                        $.each($('.video-placeholder').siblings('input:hidden'), function() {
                            if ($(this).val() == file.val()) {
                                var start = this.name.indexOf('[') + 1;
                                var end = this.name.length - 1;
                                var imageRole = this.name.substring(start, end);
                                $('#new_video_form input[value="' + imageRole + '"]').prop('checked', true);
                            }
                        });

                    });
                },
                closed: function() {
                    newVideoForm.validation('clearError');
                    $('input[name*="' + $('#item_id').val() + '"]').parent().removeClass('active');
                    $('#new_video_form')[0].reset();
                }
            });
        },

        saveImageRoles: function(data) {
            if (data.length > 0) {
                var containers = $('.video-placeholder').siblings('input');
                $.each(containers, function (i, el) {
                    var start = el.name.indexOf('[') + 1;
                    var end = el.name.indexOf(']');
                    var imageType = el.name.substring(start, end);
                    var imageCheckbox = $('#new_video_form input[value="' + imageType + '"]');
                    if ($(el).val() != '' && $(el).val() == data && imageCheckbox.prop('checked') == false) {
                        $(el).val('');
                    }
                    if (imageCheckbox.prop('checked') ) {
                        $(el).val(data);
                    }
                })
            }
        },

        toggleButtons: function() {
            $('.video-placeholder').click(function() {
                $('.video-create-button').show();
                $('.video-edit').hide();
            });
            $(document).on('click', '.item.image', function() {
                $('.video-create-button').hide();
                $('.video-edit').show();
            });
        },

    });

    return $.mage.newVideoDialog;
});
