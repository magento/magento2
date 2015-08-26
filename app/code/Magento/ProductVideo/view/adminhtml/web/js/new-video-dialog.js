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
                        var inputFile = uploader.val('').clone(true)
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
                                data['video_url'] = $('#new_video_url').val();
                                data['video_name'] = $('#new_video_name').val();
                                data['video_description'] = $('#new_video_description').val();
                                data['disabled'] = $('#new_video_disabled').prop('checked');
                                data['role'] = $('#new_video_role').val();
                                data['entity_type'] = 'video';
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
                        $('input[name*="' + $('#item_id').val() + '[video_url]"]').val($('#new_video_url').val());
                        $('input[name*="' + $('#item_id').val() + '[video_name]"]').val($('#new_video_name').val());
                        $('input[name*="' + $('#item_id').val() + '[video_description]"]').val($('#new_video_description').val());
                        $('input[name*="' + $('#item_id').val() + '[disabled]"]').val($('#new_video_disabled').prop('checked'));
                        $('#new-video').modal('closeModal');
                    }
                }],
                opened: function() {
                    if ($('#new_video_url').val() != '') {
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
