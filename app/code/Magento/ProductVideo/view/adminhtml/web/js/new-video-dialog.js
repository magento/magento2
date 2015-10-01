/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true $:true*/
define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'jquery/ui',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'mage/backend/tree-suggest',
    'mage/backend/validation'
], function ($, alert) {
    'use strict';

    /**
     */
    $.widget('mage.newVideoDialog', {

        _previewImage: null,

        clickedElement: '',

        _images: {},

        _imageTypes: [
            'image/jpeg',
            'image/pjpeg',
            'image/jpeg',
            'image/pjpeg',
            'image/png',
            'image/gif'
        ],

        /**
         * Bind events
         * @private
         */
        _bind: function () {
            var events = {
                'setImage': '_onSetImage'
            };

            this._on(events);
        },

        /**
         * Remove ".tmp"
         * @param {String} name
         * @returns {*}
         * @private
         */
        __prepareFilename: function (name) {
            var tmppost = '.tmp';

            if (!name) {
                return null;
            }

            if (name.endsWith(tmppost)) {
                name = name.slice(0, name.length - tmppost.length);
            }

            return name;
        },

        /**
         * Set image data
         * @param {String} file
         * @param {Object} imageData
         * @private
         */
        _setImage: function (file, imageData) {
            file = this.__prepareFilename(file);
            this._images[file] = imageData;
            $('#media_gallery_content').trigger('addItem', imageData);
            this.element.trigger('setImage', imageData);
            this._addVideoClass(imageData.url);
        },

        /**
         * Get image data
         *
         * @param {String} file
         * @returns {*}
         * @private
         */
        _getImage: function (file) {
            file = this.__prepareFilename(file);

            return this._images[file];
        },

        /**
         * Replace image
         * @param {String} oldFile
         * @param {String} newFile
         * @param {Object} imageData
         * @private
         */
        _replaceImage: function (oldFile, newFile, imageData) {
            var tmpNewFile = newFile,
                newImageId,
                fc,
                suff,
                searchsuff,
                key,
                oldValIdElem;

            oldFile = this.__prepareFilename(oldFile);
            newFile = this.__prepareFilename(newFile);

            if (newFile === oldFile) {
                this._images[newFile] = imageData;
                this.saveImageRoles(imageData);

                return null;
            }

            this._removeImage(oldFile);
            this._setImage(newFile, imageData);

            if (oldFile && imageData.oldFile) {
                newImageId = this.findElementId(tmpNewFile),
                    fc = $('#item_id').val(),
                    suff = 'product[media_gallery][images]' + fc,
                    searchsuff = 'input[name="' + suff + '[value_id]"]',
                    key = $(searchsuff).val();

                if (!key) {
                    return null;
                }

                oldValIdElem = document.createElement('input');
                $('form[data-form="edit-product"]').append(oldValIdElem);
                $(oldValIdElem).attr({
                    type: 'hidden',
                    name: 'product[media_gallery][images][' + newImageId + '][save_data_from]'
                }).val(key);
            }
        },

        /**
         * Remove image data
         * @param {String} file
         * @private
         */
        _removeImage: function (file) {
            var imageData = this._getImage(file);

            if (!imageData) {
                return null;
            }

            $('#media_gallery_content').trigger('removeItem', imageData);
            this.element.trigger('removeImage', imageData);
            delete this._images[file];
        },

        /**
         * Fired when image setted
         * @param {Event} event
         * @param {Object} imageData
         * @private
         */
        _onSetImage: function (event, imageData) {
            this.saveImageRoles(imageData);
        },

        /**
         * Upload local image
         * @param {String} file
         * @param {String} oldFile
         * @param {Function} callback
         * @private
         */
        _uploadImage: function (file, oldFile, callback) {
            var self        = this,
                url         = this.options.saveVideoUrl,
                uploadData = {
                    files: file,
                    url: url
                };

            this._uploadFile('send', uploadData, function (result) {
                var data = JSON.parse(result);

                if (data && data.errorcode) {
                    alert({
                        content: data.error
                    });

                    return null;
                }

                $.each($('#new_video_form').serializeArray(), function (i, field) {
                    data[field.name] = field.value;
                });
                data.disabled = $('#new_video_disabled').prop('checked') ? 1 : 0;
                data['media_type'] = 'external-video';
                data['old_file'] = oldFile;
                oldFile  ?
                    self._replaceImage(oldFile, data.file, data) :
                    self._setImage(data.file, data);

                callback.call(0, data);
            });
        },

        /**
         * Upload local file
         * @private
         */
        _uploadFile: function (method, data, callback) {
            var fu = $('#new_video_screenshot'),
                tmpInput   = document.createElement('input'),
                fileUploader = null;

            $(tmpInput).attr({
                name: fu.attr('name'),
                value: fu.val(),
                type: 'file',
                'data-ui-ud': fu.attr('data-ui-ud')
            }).css('display', 'none');
            fu.parent().append(tmpInput);
            fileUploader = $(tmpInput).fileupload();
            fileUploader.fileupload(method, data).success(function (result, textStatus, jqXHR) {
                tmpInput.remove();
                callback.call(null, result, textStatus, jqXHR);
            });
        },

        /**
         * Add video class (gallery items)
         * @param {String} url
         * @private
         */
        _addVideoClass: function (url) {
            var classVideo = 'video-item';

            $('img[src="' + url + '"]').addClass(classVideo);
        },

        /**
         * Fired when widget initialized
         * @private
         */
        _create: function () {
            var imgs = $('#media_gallery_content').data('images') || [],
                widget,
                uploader,
                tmp,
                i;

            for (i = 0; i < imgs.length; i++) {
                tmp = imgs[i];
                tmp.subclass = 'video-item';
                this._images[tmp.file] = tmp;
                this._addVideoClass(tmp.url);
            }

            this._bind();
            widget = this;
            uploader = $('#new_video_screenshot');
            uploader.on('change', this._onImageInputChange.bind(this));
            this.toggleButtons();
            uploader.attr('accept', this._imageTypes.join(','));
            this.element.modal({
                type: 'slide',
                modalClass: 'mage-new-video-dialog form-inline',
                title: $.mage.__('Create Video'),
                buttons: [{
                    text: $.mage.__('Save'),
                    class: 'action-primary video-create-button',

                    /**
                     * Fired when click on Save button
                     */
                    click: function () {
                        var nvs = $('#new_video_screenshot'),
                            file = nvs.get(0),
                            newVideoForm,
                            reqClass = 'required-entry _required';

                        if (file && file.files && file.files.length) {
                            file =  file.files[0];
                        } else {
                            file = null;
                        }

                        if (!file) {
                            nvs.addClass(reqClass);
                        }

                        newVideoForm = $('#new_video_form');
                        newVideoForm.mage('validation', {

                            /**
                             * Set invalid message
                             * @param {jQuery} error
                             * @param {jQuery} element
                             */
                            errorPlacement: function (error, element) {
                                error.insertAfter(element);
                            }
                        }).on('highlight.validate', function () {
                            $(this).validation('option');
                        });
                        newVideoForm.validation();

                        if (!newVideoForm.valid()) {
                            return null;
                        }

                        widget._uploadImage(file, null, function () {
                            //uploader.replaceWith(data.file);
                            widget._onClose();
                        });
                        nvs.removeClass(reqClass);
                    }
                },
                    {
                        text: $.mage.__('Save'),
                        class: 'action-primary video-edit',

                        /**
                         * Fired when video edit
                         */
                        click: function () {
                            var newVideoForm = $('#new_video_form'),
                                inputFile,
                                flagChecked,
                                imageData,
                                fileName,
                                itemVal,
                                mediaFields,

                                /**
                                 * Callback for event "modalClose"
                                 */
                                callback = function () {
                                    widget._onClose();
                                };

                            newVideoForm.mage('validation', {

                                /**
                                 * Set invalid message
                                 * @param {jQuery} error
                                 * @param {jQuery} element
                                 */
                                errorPlacement: function (error, element) {
                                    error.insertAfter(element);
                                }
                            }).on('highlight.validate', function () {
                                $(this).validation('option');
                            });
                            newVideoForm.validation();

                            if (!newVideoForm.valid()) {
                                return null;
                            }

                            inputFile = uploader;
                            itemVal = $('#item_id').val();
                            mediaFields = $('input[name*="' + itemVal + '"]');
                            $.each(mediaFields, function (itmp, el) {
                                var start = el.name.indexOf(itemVal) + itemVal.length + 1,
                                    fieldName = el.name.substring(start, el.name.length - 1),
                                    fieldItem = $('#' + fieldName);

                                if (fieldItem.length > 0) {
                                    $('input[name*="' + itemVal + '[' + fieldName + ']"]').val(fieldItem.val());
                                }
                            });
                            flagChecked = $('#new_video_disabled').prop('checked') ? 1 : 0;
                            $('input[name*="' + itemVal + '[disabled]"]').val(flagChecked);

                            if (flagChecked) {
                                $('[name*="' + itemVal + '"]').siblings('.image-fade').css('visibility', 'visible');
                            } else {
                                $('[name*="' + itemVal + '"]').siblings('.image-fade').css('visibility', 'hidden');
                            }

                            imageData = widget._getImage($('#file_name').val());
                            fileName = $('#new_video_screenshot').get(0).files[0];
                            uploader.replaceWith(inputFile);

                            if (!fileName) {
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

                        /**
                         * Fired when click on delete image button
                         */
                        click: function () {
                            var removed = $('[name*="' + $('#item_id').val() + '[removed]"]');

                            widget._onClose();
                            removed.val(1);
                            removed.parent().hide();
                        }
                    },
                    {
                        text: $.mage.__('Cancel'),
                        class: 'video-cancel-button',

                        /**
                         * Fired when click on Close dialog button
                         * @param {Event} e
                         */
                        click: function (e) {
                            widget._onClose(e);
                        }
                    }],

                /**
                 * Open widged action
                 */
                opened: function () {
                    var file = $('#file_name').val(),
                        imageData;

                    $('#video_url').focus();
                    $('button[data-role="close-panel"]').click();

                    if (!file) {
                        return null;
                    }
                    imageData = widget._getImage(file);
                    widget._onPreview(null, imageData.url, false);
                },

                /**
                 * Closed widged action
                 */
                closed: function () {
                    var newVideoForm = $('#new_video_form');

                    if (widget._previewImage) {
                        widget._previewImage.remove();
                        widget._previewImage = null;
                    }

                    $(newVideoForm).trigger('reset');
                    $(newVideoForm).find('input[type="hidden"][name!="form_key"]').val('');
                    $('input[name*="' + $('#item_id').val() + '"]').parent().removeClass('active');

                    try {
                        newVideoForm.validation('clearError');
                    } catch (e) {

                    }
                }
            });
        },

        /**
         * Read image from local
         * @param {String} file
         * @param {Function} callback
         * @private
         */
        _readPreviewLocal: function (file, callback) {
            var fr;

            if (!window.FileReader) {
                return null;
            }

            fr = new FileReader;

            /**
             * Fired when image loaded
             */
            fr.onloadend = function () {
                callback(fr.result);
            };
            fr.readAsDataURL(file);
        },

        /**
         * Fired when preview input change
         * @private
         */
        _onImageInputChange: function () {
            var file = document.getElementById('new_video_screenshot').files[0];

            if (!file) {
                return null;
            }

            this._onPreview(null, file, true);
        },

        /**
         * Fired when image loaded
         * @param {bool} error
         * @param {String} src
         * @param {bool} local
         * @private
         */
        _onPreview: function (error, src, local) {
            var img = this._getPreviewImage(),

                /**
                 * Callback
                 * @param {String} source
                 */
                renderImage = function (source) {
                    img.attr({
                        'src': source
                    }).show();
                };

            if (error) {
                return null;
            }

            if (!local) {
                renderImage(src);
            } else {
                this._readPreviewLocal(src, renderImage);
            }
        },

        /**
         * Get preview image DOM instance
         * @returns {null}
         * @private
         */
        _getPreviewImage: function () {

            if (!this._previewImage) {
                this._previewImage = $(document.createElement('img')).css({
                    'width': '145px',
                    'display': 'none',
                    'src': ''
                });
                $(this._previewImage).insertAfter('#new_video_screenshot_preview');
            }

            return this._previewImage;
        },

        /**
         * Fired when dialog close
         * @private
         */
        _onClose: function () {
            $('#new-video').modal('closeModal');
        },

        /**
         * Find element by fileName
         * @param {String} file
         * @returns {*}
         */
        findElementId: function (file) {
            var elem = $('.image.item').find('input[value="' + file + '"]');

            if (!elem) {

                return null;
            }

            return $(elem).attr('name').replace('product[media_gallery][images][', '').replace('][file]', '');
        },

        /**
         * Save image roles
         * @param {Object} imageData
         */
        saveImageRoles: function (imageData) {
            var data = imageData.file,
                self = this,
                containers;

            if (!data) {
                throw new Error('You need use _getImae');
            }

            if (data.length > 0) {
                containers = $('.video-placeholder').siblings('input');
                $.each(containers, function (i, el) {
                    var start = el.name.indexOf('[') + 1,
                        end = el.name.indexOf(']'),
                        imageType = el.name.substring(start, end),
                        imageCheckbox = $('input[value="' + imageType + '"]');

                    self._changeRole(imageType, imageCheckbox.attr('checked'), imageData);
                });
            }
        },

        /**
         * Change image role
         * @param {String} imageType - role name
         * @param {bool} isEnabled - role active status
         * @param {Object} imageData - image data object
         * @private
         */
        _changeRole: function (imageType, isEnabled, imageData) {
            var needCheked = true;

            if (!isEnabled) {
                needCheked = $('input[name="product[' + imageType + ']"]').val() === imageData.file;
            }

            if (!needCheked) {

                return null;
            }

            $('#media_gallery_content').trigger('setImageType', {
                type:  imageType,
                imageData: isEnabled ? imageData : null
            });
        },

        /**
         * Register Items event listeners
         */
        toggleButtons: function () {
            $('.video-placeholder').click(function () {
                $('.video-create-button').show();
                $('.video-delete-button').hide();
                $('.video-edit').hide();
                $('.modal-title').html('New video');
            });
            $(document).on('click', '.item.image', function () {
                $('.video-create-button').hide();
                $('.video-delete-button').show();
                $('.video-edit').show();
                $('.modal-title').html('Edit video');
            });
            $(document).on('click', '.item.image', function () {
                var flagChecked,
                    file,
                    formFields = $('#new_video_form').find('.edited-data'),
                    container = $(this);

                $.each(formFields, function (i, field) {
                    $(field).val(container.find('input[name*="' + field.name + '"]').val());
                });

                flagChecked = container.find('input[name*="disabled"]').val() > 0;
                $('#new_video_disabled').prop('checked', flagChecked);

                file = $('#file_name').val(container.find('input[name*="file"]').val());
                $.each($('.video_image_role'), function () {
                    $(this).prop('checked', false).prop('disabled', false);
                });
                $.each($('.video-placeholder').siblings('input:hidden'), function () {
                    var start,
                        end,
                        imageRole;

                    if ($(this).val() !== file.val()) {

                        return null;
                    }
                    start = this.name.indexOf('[') + 1,
                        end = this.name.length - 1,
                        imageRole = this.name.substring(start, end);
                    $('input[value="' + imageRole + '"]').prop('checked', true);
                });
            });
        }
    });
    $('.video-create-button').on('click', function () {
        $('#media_gallery_content').find('.video-item').parent().addClass('video-item');
    });

    return $.mage.newVideoDialog;
});
