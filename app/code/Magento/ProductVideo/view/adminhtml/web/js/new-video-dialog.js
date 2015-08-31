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

    /**
     * @todo: replace image event handler
     * @todo: remove image event handler
     */
    $.widget('mage.newVideoDialog', {

        clickedElement : '',
        _images: {},

        _bind: function() {
            var events = {
                //'removeImage': '',
                'setImage': '_onSetImage'
            };
            this._on(events);
        },

        /**
         * Remove ".tmp"
         * Evil hack !!
         * @param name
         * @returns {*}
         * @private
         */
        __prepareFilename: function(name) {
            if(!name) {
                return name;
            }
            var tmppost = '.tmp';
            if(name.endsWith(tmppost)) {
                name = name.slice(0, name.length - tmppost.length);
            }
            return name;
        },

        /**
         *  set image data
         * @param file
         * @param imageData
         * @private
         */
        _setImage: function(file, imageData) {
            file = this.__prepareFilename(file);
            this._images[file] = imageData;
            jQuery('#media_gallery_content').trigger('addItem', imageData);
            this.element.trigger('setImage', imageData);
        },

        /**
         * Get image data
         *
         * @param file
         * @returns {*}
         * @private
         */
        _getImage: function(file) {
            file = this.__prepareFilename(file);
            return this._images[file];
        },

        _replaceImage: function(oldFile, newFile, imageData) {
            var tmpOldFile = oldFile;
            var tmpNewFile = newFile;
            oldFile = this.__prepareFilename(oldFile);
            newFile = this.__prepareFilename(newFile);
            if(newFile == oldFile) {
                this._images[newFile] = imageData;
                this.saveImageRoles(imageData);
                return;
            }
            this._removeImage(oldFile);
            this._setImage(newFile, imageData);
            if(oldFile && imageData.old_file) { // For questions about this behavior refer to Vadim Zubovich
                var oldImageId = this.findElementId(tmpOldFile);
                var newImageId = this.findElementId(tmpNewFile);
                var fc = jQuery('#item_id').val();

                var suff = 'product[media_gallery][images]' + fc;

                var searchsuff = 'input[name="' + suff + '[value_id]"]';
                var key = jQuery(searchsuff).val();
                if(!key) {
                    return;
                }
                var old_val_id_elem;
                old_val_id_elem = document.createElement('input');
                old_val_id_elem.setAttribute('type', 'hidden');
                old_val_id_elem.setAttribute('value', key);
                old_val_id_elem.setAttribute('name', 'product[media_gallery][images][' + newImageId + '][save_data_from]');
                jQuery('form[data-form="edit-product"]').append(old_val_id_elem);
            }
        },

        /**
         * Remove image data
         * @param file
         * @private
         */
        _removeImage: function(file) {
            var imageData = this._getImage(file);
            if(!imageData) {
                return;
            }
            jQuery('#media_gallery_content').trigger('removeItem', imageData);
            this.element.trigger('removeImage', imageData);
            delete this._images[file];
        },


        _onSetImage: function(event, imageData) {
            this.saveImageRoles(imageData);
        },

        _onRemoveImage: function(event, imageData) {
        },

        /**
         * @todo: Error handler !
         * @param file
         * @param oldFile
         * @param callback
         * @private
         */
        _uploadImage: function(file, oldFile, callback) {
            var self        = this;
            var url         = this.options.saveVideoUrl;
            var fu          = jQuery('#new_video_screenshot');

            var tmp_input   = document.createElement('input');
            tmp_input.setAttribute('name', fu.attr('name'));
            tmp_input.setAttribute('value', fu.val());
            tmp_input.setAttribute('type', 'file');
            tmp_input.setAttribute('style', 'display: none;');
            tmp_input.setAttribute('data-ui-ud', fu.attr('data-ui-ud'));
            fu.parent().append(tmp_input);

            var fileUploader = jQuery(tmp_input).fileupload();
            fileUploader.fileupload(
                'send',
                {
                    files: file,
                    url: url
                }).success(
                function(result, textStatus, jqXHR) {
                    tmp_input.remove();
                    var data = JSON.parse(result);
                    $.each($('#new_video_form').serializeArray(), function(i, field) {
                        data[field.name] = field.value;
                    });
                    data['disabled'] = $('#new_video_disabled').prop('checked') ? 1 : 0;
                    data['media_type'] = 'external-video';
                    data.old_file = oldFile;
                    oldFile  ?
                        self._replaceImage(oldFile, data.file, data):
                        self._setImage(data.file, data);
                    callback.call(0, data);
                }
            );
        },

        _create: function () {
            var imgs = jQuery('#media_gallery_content').data('images') || [];
            for(var i = 0; i < imgs.length; i++) {
                var tmp = imgs[i];
                this._images[tmp.file] = tmp;
            }
            this._bind();
            var widget = this;
            var newVideoForm = $('#new_video_form');
            var uploader = $('#new_video_screenshot');
            this.toggleButtons();

            $(document).on('click', '.action-delete', function() {
                $('#new-video').modal('closeModal');
            });

            this.element.modal({
                type: 'slide',
                modalClass: 'mage-new-video-dialog form-inline',
                title: $.mage.__('Create Video'),
                buttons: [{
                    text: $.mage.__('Save'),
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
                        if (!file) {
                            return;
                        }
                        var inputFile = uploader;

                        widget._uploadImage(file, null, function(code, data) {
                            uploader.replaceWith(inputFile);
                            $('#new-video').modal('closeModal');
                        });
                    }
                },
                    {
                        text: $.mage.__('Save'),
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

                            var inputFile = uploader;
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
                            var imageData = widget._getImage($('#file_name').val());

                            var fileName = $('#new_video_screenshot').get(0).files[0];
                            uploader.replaceWith(inputFile);

                            var callback = function(code, data) {
                                $('#new-video').modal('closeModal');
                            };

                            if(!fileName) {
                                callback.call(0, imageData);
                                widget._replaceImage(imageData.file, imageData.file, imageData);
                            } else {
                                widget._uploadImage(fileName, imageData.file, callback);
                            }
                        }
                    },
                    {
                        text: $.mage.__('Delete'),
                        class: 'action-primary video-delete-button',
                        click: function (e) {
                            /** @todo: use _removeImage() */
                            $('#new-video').modal('closeModal');
                            var removed = $('[name*="' + $('#new_video_form #item_id').val() + '[removed]"]');
                            removed.val(1);
                            removed.parent().hide();
                        }
                    },
                    {
                        text: $.mage.__('Cancel'),
                        class: 'video-cancel-button',
                        click: function (e) {
                            newVideoForm.validation('clearError');
                            $('#new-video').modal('closeModal');
                        }
                    }],
                opened: function(e) {
                    $('#video_url').focus();

                },
                closed: function() {
                    try {
                        newVideoForm.validation('clearError');
                    } catch(e) {
                        // Crutch hack.
                        // Very strange error.
                    }
                    $('input[name*="' + $('#item_id').val() + '"]').parent().removeClass('active');
                    $('#new_video_form')[0].reset();
                }
            });
        },

        /**
         * Find element by fileName
         */
        findElementId: function (file) {
            var elem = jQuery('.image.item').find('input[value="' + file + '"]');
            if(!elem) {
                return null;
            }
            return jQuery(elem).attr('name').replace('product[media_gallery][images][', '').replace('][file]', '');
        },

        /**
         * @todo : refactoring need
         * @param imageData
         */
        saveImageRoles: function(imageData) {
            var data = imageData.file;
            if(!data) {
                throw new Error('You need use _getImae');
            }
            if (data.length > 0) {
                var containers = $('.video-placeholder').siblings('input');
                $.each(containers, function (i, el) {
                    var start = el.name.indexOf('[') + 1;
                    var end = el.name.indexOf(']');
                    var imageType = el.name.substring(start, end);
                    var imageCheckbox = $('#new_video_form input[value="' + imageType + '"]');
                    jQuery('#media_gallery_content').trigger('setImageType', {
                        type:  imageType,
                        imageData: imageCheckbox.prop('checked') ? imageData : null
                    });
                });
            }
        },

        toggleButtons: function() {
            $('.video-placeholder').click(function() {
                $('.video-create-button').show();
                $('.video-delete-button').hide();
                $('.video-edit').hide();
                $('.modal-title').html('New video');
            });
            $(document).on('click', '.item.image', function() {
                $('.video-create-button').hide();
                $('.video-delete-button').show();
                $('.video-edit').show();
                $('.modal-title').html('Edit video');
            });
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
        }

    });

    return $.mage.newVideoDialog;
});
