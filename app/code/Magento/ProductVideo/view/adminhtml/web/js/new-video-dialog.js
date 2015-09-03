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
     */
    $.widget('mage.newVideoDialog', {

        _previewImage: null,

        clickedElement : '',

        _images: {},

        _imageTypes: ['.jpeg', '.pjpeg', '.jpeg', '.jpg', '.pjpeg', '.png', '.gif'],

        _bind: function() {
            var events = {
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
            this._addVideoClass(imageData.url);
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
            if(oldFile && imageData.old_file) {
                var oldImageId = this.findElementId(tmpOldFile);
                var newImageId = this.findElementId(tmpNewFile);
                var fc = jQuery('#item_id').val();

                var suff = 'product[media_gallery][images]' + fc;

                var searchsuff = 'input[name="' + suff + '[value_id]"]';
                var key = jQuery(searchsuff).val();
                if(!key) {
                    return;
                }
                var old_val_id_elem = document.createElement('input');
                jQuery('form[data-form="edit-product"]').append(old_val_id_elem);
                jQuery(old_val_id_elem).attr({
                    type: 'hidden',
                    name: 'product[media_gallery][images][' + newImageId + '][save_data_from]'
                }).val(key);
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
         * @param file
         * @param oldFile
         * @param callback
         * @private
         */
        _uploadImage: function(file, oldFile, callback) {
            var self        = this;
            var url         = this.options.saveVideoUrl;
            var data = {
                files: file,
                url: url
            };
            this._uploadFile('send', data, function(result, textStatus, jqXHR) {
                var data = JSON.parse(result);
                if(data.errorcode) {
                    alert(data.error);
                    return;
                }
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
            });

        },

        /**
         *
         * @returns {*}
         * @private
         */
        _uploadFile: function(method, data, callback) {
            var fu = jQuery('#new_video_screenshot');
            var tmp_input   = document.createElement('input');
            jQuery(tmp_input).attr({
                name: fu.attr('name'),
                value: fu.val(),
                type: 'file',
                'data-ui-ud': fu.attr('data-ui-ud')
            }).css('display', 'none');
            fu.parent().append(tmp_input);
            var fileUploader = jQuery(tmp_input).fileupload();
            fileUploader.fileupload(method, data).success(function(result, textStatus, jqXHR) {
                tmp_input.remove();
                callback.call(null, result, textStatus, jqXHR);
            });
        },


        _addVideoClass: function(url) {
            var class_video = 'video-item';
            jQuery('img[src="' + url + '"]').addClass(class_video);
        },

        _create: function () {
            var imgs = jQuery('#media_gallery_content').data('images') || [];
            for(var i = 0; i < imgs.length; i++) {
                var tmp = imgs[i];
                tmp.subclass = 'video-item';
                this._images[tmp.file] = tmp;
                this._addVideoClass(tmp.url);
            }

            this._bind();
            var widget = this;
            var uploader = jQuery('#new_video_screenshot');
            uploader.on('change', this._onImageInputChange.bind(this));
            uploader.attr('accept', this._imageTypes.join(','));

            this.toggleButtons();
            this.element.modal({
                type: 'slide',
                modalClass: 'mage-new-video-dialog form-inline',
                title: $.mage.__('Create Video'),
                buttons: [{
                    text: $.mage.__('Save'),
                    class: 'action-primary video-create-button',
                    click: function (e) {
                        var nvs = jQuery('#new_video_screenshot');
                        var file = nvs.get(0);
                        if(file && file.files && file.files.length) {
                            file =  file.files[0];
                        } else {
                            file = null;
                        }
                        var reqClass = 'required-entry _required';
                        if (!file) {
                            nvs.addClass(reqClass);
                        }

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

                        widget._uploadImage(file, null, function(code, data) {
                            //uploader.replaceWith(data.file);
                            widget._onClose();
                        });
                        nvs.removeClass(reqClass);
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
                            uploader.replaceWith(inputFile);

                            var fileName = $('#new_video_screenshot').get(0).files;
                            if(!fileName || !fileName.length) {
                                fileName = null;
                            }

                            var callback = function(code, data) {
                                widget._onClose();
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
                           // $('#new-video').modal('closeModal')
                            widget._onClose();
                            var removed = $('[name*="' + $('#new_video_form #item_id').val() + '[removed]"]');
                            removed.val(1);
                            removed.parent().hide();
                        }
                    },
                    {
                        text: $.mage.__('Cancel'),
                        class: 'video-cancel-button',
                        click: function (e) {
                            widget._onClose(e);
                        }
                    }],
                opened: function(e) {
                    $('#video_url').focus();
                    jQuery('button[data-role="close-panel"]').click();
                    var file = jQuery('#file_name').val();
                    if(!file) {
                        return;
                    }
                    var imageData = widget._getImage(file);
                    widget._onPreview(null, imageData.url, false);
                },
                closed: function(e) {
                    if(widget._previewImage) {
                        widget._previewImage.remove();
                        widget._previewImage = null;
                    }
                    var newVideoForm = $('#new_video_form');
                    jQuery(newVideoForm).trigger('reset');
                    jQuery(newVideoForm).find('input[type="hidden"][name!="form_key"]').val('');
                    $('input[name*="' + $('#item_id').val() + '"]').parent().removeClass('active');
                    try {
                        newVideoForm.validation('clearError');
                    } catch(e) {}
                }
            });
        },


        _readPreviewLocal: function(file, callback) {
            if(!window.FileReader) {
                return;
            }
            var fr = new FileReader;
            fr.onloadend = function() {
                callback(fr.result);
            };
            fr.readAsDataURL(file);
        },

        /**
         *  Image file input handler
         * @private
         */
        _onImageInputChange: function() {
            var file = document.getElementById('new_video_screenshot');
            var val = file.value;
            var jFile = jQuery(file);
            var prev = this._getPreviewImage();

            if(!val) {
                return;
            }
            var ext = '.' + val.split('.').pop();
            if(
                ext.length < 2 ||
                this._imageTypes.indexOf(ext) == -1 ||
                !file.files  ||
                !file.files.length

            ) {
                jFile.val('');
                prev.remove();
                this._previewImage = null;
                return;
            } // end if
            file = file.files[0];
            this._onPreview(null, file, true);
        },

        /**
         * Change Preview
         * @param error
         * @param src
         * @param local
         * @private
         */
        _onPreview: function(error, src, local) {
            var img = this._getPreviewImage();
            if(error) {
                return;
            }

            var renderImage = function(src) {
                img.attr({'src': src}).show();
            };

            if(!local) {
                renderImage(src);
            } else {
                this._readPreviewLocal(src, renderImage);
            }
        },

        _getPreviewImage: function() {
            if(!this._previewImage) {
                this._previewImage = jQuery(document.createElement('img')).css({
                    'width' : '145px',
                    'display': 'none',
                    'src': ''
                });
                jQuery(this._previewImage).insertAfter('#new_video_screenshot_preview');
            }
            return this._previewImage;
        },

        /**
         * Close dialog wrap
         * @private
         */
        _onClose: function() {
            $('#new-video').modal('closeModal');
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
         * @param imageData
         */
        saveImageRoles: function(imageData) {
            var data = imageData.file;
            if(!data) {
                throw new Error('You need use _getImae');
            }
            var self = this;
            if (data.length > 0) {
                var containers = $('.video-placeholder').siblings('input');
                $.each(containers, function (i, el) {
                    var start = el.name.indexOf('[') + 1;
                    var end = el.name.indexOf(']');
                    var imageType = el.name.substring(start, end);
                    var imageCheckbox = $('#new_video_form input[value="' + imageType + '"]');
                    self._changeRole(imageType, imageCheckbox.attr('checked'), imageData);
                });
            }
        },

        _changeRole: function(imageType, isEnabled, imageData) {
            var needCheked = true;
            if(!isEnabled) {
                needCheked = jQuery('input[name="product[' + imageType + ']"]').val() == imageData.file;
            }

            if(!needCheked) {
                return;
            }
            jQuery('#media_gallery_content').trigger('setImageType', {
                type:  imageType,
                imageData: isEnabled ? imageData: null
            });
        },

        toggleButtons: function() {
            $('.video-placeholder').click(function() {
                $('.video-create-button').show();
                $('.video-delete-button').hide();
                $('.video-edit').hide();
            });
            $(document).on('click', '.item.image', function() {
                $('.video-create-button').hide();
                $('.video-delete-button').show();
                $('.video-edit').show();
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
