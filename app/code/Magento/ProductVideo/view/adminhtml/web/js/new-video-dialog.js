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
        _create: function () {
            var widget = this;
            var newVideoForm = $('#new_video_form');
            var uploader = $('#new_video_screenshot');

            this.element.modal({
                type: 'slide',
                modalClass: 'mage-new-video-dialog form-inline',
                title: $.mage.__('Create Video'),
                buttons: [{
                    text: $.mage.__('Create Video'),
                    class: 'action-primary video-create-button',
                    click: function (e) {
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
                                data['video_url'] = $('#video_url').val();
                                data['video_name'] = $('#video_name').val();
                                data['video_description'] = $('#video_description').val();
                                data['disabled'] = $('#new_video_disabled').prop('checked') ? 1 : 0;
                                data['role'] = $('#new_video_role').val();
                                data['media_type'] = 'external-video';
                                if ($('#video_base_image').prop('checked') == true) {
                                    $('[name="product[image]"]').prop('checked', true);
                                } else if (
                                    $('#video_base_image').prop('checked') == false
                                    && $('#file_name').val() == $('[name="product[image]"]').val()
                                ) {
                                    $('[name="product[image]"]').val('')
                                }
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
                        var inputFile = uploader.val('').clone(true);
                        $('input[name*="' + $('#item_id').val() + '[video_url]"]').val($('#video_url').val());
                        $('input[name*="' + $('#item_id').val() + '[video_name]"]').val($('#video_name').val());
                        $('input[name*="' + $('#item_id').val() + '[video_description]"]').val($('#video_description').val());
                        var flagChecked = $('#new_video_disabled').prop('checked') ? 1 : 0;
                        $('input[name*="' + $('#item_id').val() + '[disabled]"]').val(flagChecked);
                        if (flagChecked == true) {
                            $('[name*="' + $('#item_id').val() + '"]').siblings('.image-fade').css('visibility', 'visible');
                        } else {
                            $('[name*="' + $('#item_id').val() + '"]').siblings('.image-fade').css('visibility', 'hidden');
                        }
                        if ($('#video_base_image').prop('checked') == true) {
                            $('[name="product[image]"]').prop('checked', true);
                        } else if (
                            $('#video_base_image').prop('checked') == false
                            && $('#file_name').val() == $('[name="product[image]"]').val()
                        ) {
                            $('[name="product[image]"]').val('')
                        }
                        uploader.replaceWith(inputFile);
                        $('#new-video').modal('closeModal');
                    }
                }],
                opened: function() {
                    if ($('#video_url').val() != '') {
                        $('.video-create-button')[0].hide();
                        $('.video-edit')[0].show();
                    } else {
                        $('.video-create-button')[0].show();
                        $('.video-edit')[0].hide();
                    }
                },
                closed: function() {
                    $('input[name*="' + $('#item_id').val() + '"]').parent().removeClass('active');
                }
            });
        }
    });

    return $.mage.newVideoDialog;
});
