/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'jquery/ui',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'mage/backend/tree-suggest',
    'mage/backend/validation',
    'Magento_ProductVideo/js/get-video-information'
], function ($, _) {
    'use strict';

    $.widget('mage.createVideoPlayer', {
        options: {
            videoId: '',
            videoProvider: '',
            container: '.video-player-container',
            videoClass: 'product-video',
            reset: false,
            useYoutubeNocookie: false,
            metaData: {
                DOM: {
                    title: '.video-information.title span',
                    uploaded: '.video-information.uploaded span',
                    uploader: '.video-information.uploader span',
                    duration: '.video-information.duration span',
                    all: '.video-information span',
                    wrapper: '.video-information'
                },
                data: {
                    title: '',
                    uploaded: '',
                    uploader: '',
                    uploaderUrl: '',
                    duration: ''
                }
            }
        },

        _FINISH_CREATE_VIDEO_TRIGGER: 'finish_create_video',

        _FINISH_UPDATE_VIDEO_TRIGGER: 'finish_update_video',

        /**
         * @private
         */
        _init: function () {
            if (this.options.reset) {
                this.reset();
            } else {
                this.update();
            }

            this.element.on('reset', $.proxy(this.reset, this));

        },

        /**
         * @returns {Boolean}
         */
        update: function () {
            var checkVideoID =
                this.element.find(this.options.container).find('.' + this.options.videoClass).data('code'),
                eventVideoData = {
                    oldVideoId: checkVideoID ? checkVideoID.toString() : checkVideoID,
                    newVideoId: this.options.videoId ? this.options.videoId.toString() : this.options.videoId
                };

            if (checkVideoID && checkVideoID !== this.options.videoId) {
                this._doUpdate();
                this.element.trigger(this._FINISH_UPDATE_VIDEO_TRIGGER, eventVideoData);
            } else if (checkVideoID && checkVideoID === this.options.videoId) {
                return false;
            } else if (!checkVideoID) {
                this._doUpdate();
                this.element.trigger(this._FINISH_CREATE_VIDEO_TRIGGER, eventVideoData);
            }

        },

        /**
         * @private
         */
        _doUpdate: function () {
            var uploaderLinkUrl,
                uploaderLink;

            this.reset();
            this.element.find(this.options.container).append(
                '<div class="' +
                this.options.videoClass +
                '" data-type="' +
                this.options.videoProvider +
                '" data-code="' +
                this.options.videoId +
                '" data-youtubenocookie="' +
                this.options.useYoutubeNocookie +
                '" data-width="100%" data-height="100%"></div>'
            );
            this.element.find(this.options.metaData.DOM.wrapper).show();
            this.element.find(this.options.metaData.DOM.title).text(this.options.metaData.data.title);
            this.element.find(this.options.metaData.DOM.uploaded).text(this.options.metaData.data.uploaded);
            this.element.find(this.options.metaData.DOM.duration).text(this.options.metaData.data.duration);

            if (this.options.videoProvider === 'youtube') {
                uploaderLinkUrl = 'https://youtube.com/channel/' + this.options.metaData.data.uploaderUrl;
            } else if (this.options.videoProvider === 'vimeo') {
                uploaderLinkUrl = this.options.metaData.data.uploaderUrl;
            }
            uploaderLink = document.createElement('a');
            uploaderLink.setAttribute('href', uploaderLinkUrl);
            uploaderLink.setAttribute('target', '_blank');
            uploaderLink.innerText = this.options.metaData.data.uploader;
            this.element.find(this.options.metaData.DOM.uploader)[0].appendChild(uploaderLink);
            this.element.find('.' + this.options.videoClass).productVideoLoader();

        },

        /**
         * Reset
         */
        reset: function () {
            this.element.find(this.options.container).find('.' + this.options.videoClass).remove();
            this.element.find(this.options.metaData.DOM.wrapper).hide();
            this.element.find(this.options.metaData.DOM.all).text('');

        }
    });

    $.widget('mage.updateInputFields', {
        options: {
            reset: false,
            DOM: {
                urlField: 'input[name="video_url"]',
                titleField: 'input[name="video_title"]',
                fileField: '#file_name',
                descriptionField: 'textarea[name="video_description"]',
                thumbnailLocation: '.field-new_video_screenshot_preview .admin__field-control'
            },
            data: {
                url: '',
                title: '',
                description: '',
                thumbnail: ''
            }
        },

        /**
         * @private
         */
        _init: function () {
            if (this.options.reset) {
                this.reset();
            } else {
                this.update();
            }
        },

        /**
         * Update
         */
        update: function () {
            $(this.options.DOM.titleField).val(this.options.data.title);
            $(this.options.DOM.descriptionField).val(this.options.data.description);
        },

        /**
         * Reset
         */
        reset: function () {
            $(this.options.DOM.fileField).val('');
            $(this.options.DOM.urlField).val('');
            $(this.options.DOM.titleField).val('');
            $(this.options.DOM.descriptionField).val('');
        }
    });

    /**
     */
    $.widget('mage.newVideoDialog', {

        _previewImage: null,

        clickedElement: '',

        _images: {},

        _imageTypes: [
            '.jpeg',
            '.pjpeg',
            '.jpeg',
            '.jpg',
            '.pjpeg',
            '.png',
            '.gif'
        ],

        _imageProductGalleryWrapperSelector: '#image-container',

        _videoPreviewInputSelector: '#new_video_screenshot',

        _videoPreviewRemoteSelector: '',

        _videoDisableinputSelector: '#new_video_disabled',

        _videoPreviewImagePointer: '#new_video_screenshot_preview',

        _videoFormSelector: '#new_video_form',

        _itemIdSelector: '#item_id',

        _videoUrlSelector: '[name="video_url"]',

        _videoImageFilenameselector: '#file_name',

        _videoUrlWidget: null,

        _videoInformationBtnSelector: '[name="new_video_get"]',

        _editVideoBtnSelector: '.image',

        _deleteGalleryVideoSelector: '[data-role=delete-button]',

        _deleteGalleryVideoSelectorBtn: null,

        _videoInformationGetBtn: null,

        _videoInformationGetUrlField: null,

        _videoInformationGetEditBtn: null,

        _isEditPage: false,

        _onlyVideoPlayer: false,

        _tempPreviewImageData: null,

        _videoPlayerSelector: '.mage-new-video-dialog',

        _videoRequestComplete: null,

        _gallery: null,

        /**
         * Bind events
         * @private
         */
        _bind: function () {
            var events = {
                'setImage': '_onSetImage'
            };

            this._on(events);

            this._videoUrlWidget = this.element.find(this._videoUrlSelector).videoData({
                youtubeKey: this.options.youTubeApiKey,
                eventSource: 'focusout'
            });

            this._videoInformationGetBtn = this.element.find(this._videoInformationBtnSelector);
            this._videoInformationGetUrlField = this.element.find(this._videoUrlSelector);
            this._videoInformationGetEditBtn = this._gallery.find(this._editVideoBtnSelector);

            this._videoInformationGetBtn.on('click', $.proxy(this._onGetVideoInformationClick, this));
            this._videoInformationGetUrlField.on('focusout', $.proxy(this._onGetVideoInformationFocusOut, this));

            this._videoUrlWidget.on('updated_video_information', $.proxy(this._onGetVideoInformationSuccess, this));
            this._videoUrlWidget.on('error_updated_information', $.proxy(this._onGetVideoInformationError, this));
            this._videoUrlWidget.on(
                'request_video_information',
                $.proxy(this._onGetVideoInformationStartRequest, this)
            );
        },

        /**
         * Fired when user click on button "Get video information"
         * @private
         */
        _onGetVideoInformationClick: function () {
            var videoForm = this.element.find(this._videoFormSelector);

            videoForm.validation();

            if (this.element.find(this._videoUrlSelector).valid()) {
                this._onlyVideoPlayer = false;
                this._isEditPage = false;
                this._videoUrlWidget.trigger('update_video_information');
            }
        },

        /**
         * Fired when user do focus out from url field
         * @private
         */
        _onGetVideoInformationFocusOut: function () {
            this._videoUrlWidget.trigger('update_video_information');
        },

        /**
         * @private
         */
        _onGetVideoInformationStartRequest: function () {
            var videoForm = this.element.find(this._videoFormSelector);

            try {
                videoForm.validation('clearError');
            } catch (e) {
                // Do nothing
            }

            this._videoRequestComplete = false;
        },

        /**
         * Fired when user click Edit Video button
         * @private
         */
        _onGetVideoInformationEditClick: function () {
            this._onlyVideoPlayer = true;
            this._isEditPage = true;
            this._videoUrlWidget.trigger('update_video_information');
        },

        /**
         * Fired when successfully received information about the video.
         * @param {Object} e
         * @param {Object} data
         * @private
         */
        _onGetVideoInformationSuccess: function (e, data) {
            var self = this;

            self.element.on('finish_update_video finish_create_video', $.proxy(function (element, playerData) {
                if (!self._onlyVideoPlayer ||
                    !self._isEditPage && playerData.oldVideoId !== playerData.newVideoId ||
                    playerData.oldVideoId && playerData.oldVideoId !== playerData.newVideoId
                ) {
                    self.element.updateInputFields({
                        reset: false,
                        data: {
                            title: data.title,
                            description: data.description
                        }
                    });
                    this._loadRemotePreview(data.thumbnail);
                }
                self._onlyVideoPlayer = true;
            }, this))
                .createVideoPlayer({
                    videoId: data.videoId,
                    videoProvider: data.videoProvider,
                    useYoutubeNocookie: data.useYoutubeNocookie,
                    reset: false,
                    metaData: {
                        DOM: {
                            title: '.video-information.title span',
                            uploaded: '.video-information.uploaded span',
                            uploader: '.video-information.uploader span',
                            duration: '.video-information.duration span',
                            all: '.video-information span',
                            wrapper: '.video-information'
                        },
                        data: {
                            title: data.title,
                            uploaded: data.uploaded,
                            uploader: data.channel,
                            duration: data.duration,
                            uploaderUrl: data.channelId
                        }
                    }
                })
                .off('finish_update_video finish_create_video');

            this._videoRequestComplete = true;
        },

        /**
         * Load preview from youtube/vimeo
         * @param {String} sourceUrl
         * @private
         */
        _loadRemotePreview: function (sourceUrl) {
            var url = this.options.saveRemoteVideoUrl,
                self = this;

            this._getPreviewImage().attr('src', sourceUrl).hide();
            this._blockActionButtons(true, true);
            $.ajax({
                url: url,
                data: 'remote_image=' + sourceUrl,
                type: 'post',
                success: $.proxy(function (result) {
                    this._tempPreviewImageData = result;
                    this._getPreviewImage().attr('src', sourceUrl).show();
                    this._blockActionButtons(false, true);
                }, self)
            });
        },

        /**
         * Fired when receiving information about the video ended with error
         * @private
         */
        _onGetVideoInformationError: function () {
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
                return name;
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
            this._gallery.trigger('addItem', imageData);
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
         * Replace image (update)
         * @param {String} oldFile
         * @param {String} newFile
         * @param {Object} imageData
         * @private
         */
        _replaceImage: function (oldFile, newFile, imageData) {
            var tmpNewFile = newFile,
                tmpOldImage,
                newImageId,
                oldNewFilePosition,
                fc,
                suff,
                searchsuff,
                key,
                oldValIdElem;

            oldFile = this.__prepareFilename(oldFile);
            newFile = this.__prepareFilename(newFile);
            tmpOldImage = this._images[oldFile];

            if (newFile === oldFile) {
                this._images[newFile] = imageData;
                this.saveImageRoles(imageData);
                this._updateVisibility(imageData);
                this._updateImageTitle(imageData);

                return null;
            }

            this._removeImage(oldFile);
            this._setImage(newFile, imageData);

            if (!oldFile || !imageData.oldFile) {
                return null;
            }

            newImageId = this.findElementId(tmpNewFile);
            fc = this.element.find(this._itemIdSelector).val();

            suff = 'product[media_gallery][images]' + fc;

            searchsuff = 'input[name="' + suff + '[value_id]"]';
            key = this._gallery.find(searchsuff).val();

            if (!key) {
                return null;
            }

            oldValIdElem = document.createElement('input');
            this._gallery.find('form[data-form="edit-product"]').append(oldValIdElem);
            $(oldValIdElem).attr({
                type: 'hidden',
                name: 'product[media_gallery][images][' + newImageId + '][save_data_from]'
            }).val(key);

            oldNewFilePosition = parseInt(tmpOldImage.position, 10);
            imageData.position = oldNewFilePosition;

            this._gallery.trigger('setPosition', {
                imageData: imageData,
                position: oldNewFilePosition
            });
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

            this._gallery.trigger('removeItem', imageData);
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
         *
         * Wrap _uploadFile
         * @param {String} file
         * @param {String} oldFile
         * @param {Function} callback
         * @private
         */
        _uploadImage: function (file, oldFile, callback) {
            var url = this.options.saveVideoUrl,
                data = {
                    files: file,
                    url: url
                };

            this._blockActionButtons(true, true);
            this._uploadFile(data, $.proxy(function (result) {
                this._onImageLoaded(result, file, oldFile, callback);
                this._blockActionButtons(false);
            }, this));

        },

        /**
         * @param {String} result
         * @param {String} file
         * @param {String} oldFile
         * @param {Function} callback
         * @private
         */
        _onImageLoaded: function (result, file, oldFile, callback) {
            var data;

            try {
                data = JSON.parse(result);
            } catch (e) {
                data = result;
            }

            if (this.element.find('#video_url').parent().find('.image-upload-error').length > 0) {
                this.element.find('.image-upload-error').remove();
            }

            if (data.errorcode || data.error) {
                this.element.find('#video_url').parent().append('<div class="image-upload-error">' +
                '<div class="image-upload-error-cross"></div><span>' + data.error + '</span></div>');

                return;
            }
            $.each(this.element.find(this._videoFormSelector).serializeArray(), function (i, field) {
                data[field.name] = field.value;
            });
            data.disabled = this.element.find(this._videoDisableinputSelector).prop('checked') ? 1 : 0;
            data['media_type'] = 'external-video';
            data.oldFile = oldFile;

            oldFile ?
                this._replaceImage(oldFile, data.file, data) :
                this._setImage(data.file, data);
            callback.call(0, data);
        },

        /**
         * File uploader
         * @private
         */
        _uploadFile: function (data, callback) {
            let form = this.element.find(this._videoFormSelector).get(0),
                formData = new FormData(form);

            $.ajax({
                type: 'post',
                url: data.url,
                processData: false,
                contentType: false,
                data: formData,
                success: function (result, textStatus, jqXHR) {
                    // eslint-disable-next-line no-useless-call
                    callback.call(null, result, textStatus, jqXHR);
                }
            });
        },

        /**
         * Update style
         * @param {String} url
         * @private
         */
        _addVideoClass: function (url) {
            var classVideo = 'video-item';

            this._gallery.find('img[src="' + url + '"]').addClass(classVideo);
        },

        /**
         * Build widget
         * @private
         */
        _create: function () {
            var imgs = _.values(this.element.closest(this.options.videoSelector).data('images')) || [],
                widget,
                uploader,
                tmp,
                i;

            this._gallery =  this.element.closest(this.options.videoSelector);

            for (i = 0; i < imgs.length; i++) {
                tmp = imgs[i];
                this._images[tmp.file] = tmp;

                if (tmp['media_type'] === 'external-video') {
                    tmp.subclass = 'video-item';
                    this._addVideoClass(tmp.url);
                }
            }

            this._gallery.on('openDialog', $.proxy(this._onOpenDialog, this));
            this._bind();
            this.createVideoItemIcons();
            widget = this;
            uploader = this.element.find(this._videoPreviewInputSelector);
            uploader.on('change', this._onImageInputChange.bind(this));
            uploader.attr('accept', this._imageTypes.join(','));

            this.element.modal({
                type: 'slide',
                //appendTo: this._gallery,
                modalClass: 'mage-new-video-dialog form-inline',
                title: $.mage.__('New Video'),
                buttons: [
                    {
                        text: $.mage.__('Save'),
                        class: 'action-primary video-create-button',
                        click: $.proxy(widget._onCreate, widget)
                    },
                    {
                        text: $.mage.__('Cancel'),
                        class: 'video-cancel-button',
                        click: $.proxy(widget._onCancel, widget)
                    },
                    {
                        text: $.mage.__('Delete'),
                        class: 'video-delete-button',
                        click: $.proxy(widget._onDelete, widget)
                    },
                    {
                        text: $.mage.__('Save'),
                        class: 'action-primary video-edit',
                        click: $.proxy(widget._onUpdate, widget)
                    }
                ],

                /**
                 * @returns {null}
                 */
                opened: function () {
                    var roles,
                        file,
                        modalTitleElement,
                        imageData,
                        modal = widget.element.closest('.mage-new-video-dialog');

                    widget.element.find('#video_url').focus();
                    roles = widget.element.find('.video_image_role');
                    roles.prop('disabled', false);
                    file = widget.element.find('#file_name').val();
                    widget._onGetVideoInformationEditClick();
                    modalTitleElement = modal.find('.modal-title');

                    if (!file) {
                        widget._blockActionButtons(true);

                        modal.find('.video-delete-button').hide();
                        modal.find('.video-edit').hide();
                        modal.find('.video-create-button').show();
                        roles.prop('checked', widget._gallery.find('.image.item:not(.removed)').length < 1);
                        modalTitleElement.text($.mage.__('New Video'));
                        widget._isEditPage = false;

                        return null;
                    }
                    widget._blockActionButtons(false);
                    modalTitleElement.text($.mage.__('Edit Video'));
                    widget._isEditPage = true;
                    imageData = widget._getImage(file);

                    if (!imageData) {
                        imageData = {
                            url: _.find(widget._gallery.find('.product-image'), function (image) {
                                return image.src.indexOf(file) > -1;
                            }).src
                        };
                    }

                    widget._onPreview(null, imageData.url, false);
                },

                /**
                 * Closed
                 */
                closed: function () {
                    widget._onClose();
                    widget.createVideoItemIcons();
                }
            });
            this.toggleButtons();
        },

        /**
         * @param {String} status
         * @private
         */
        _blockActionButtons: function (status) {
            this.element
                .closest('.mage-new-video-dialog')
                .find('.page-actions-buttons button.video-create-button, .page-actions-buttons button.video-edit')
                .attr('disabled', status);
        },

        /**
         * Check form
         * @param {Function} callback
         */
        isValid: function (callback) {
            var videoForm = this.element.find(this._videoFormSelector),
                videoLoaded = true;

            this._blockActionButtons(true);

            this._videoUrlWidget.trigger('validate_video_url', $.proxy(function () {

                videoForm.mage('validation', {

                    /**
                     * @param {jQuery} error
                     * @param {jQuery} element
                     */
                    errorPlacement: function (error, element) {
                        error.insertAfter(element);
                    }
                }).on('highlight.validate', function () {
                    $(this).validation('option');
                });

                videoForm.validation();

                if (this._videoRequestComplete === false) {
                    videoLoaded = false;
                }

                callback(videoForm.valid() && videoLoaded);
            }, this));

            this._blockActionButtons(false);
        },

        /**
         * Create video item icons
         */
        createVideoItemIcons: function () {
            var $imageWidget = this._gallery.find('.product-image.video-item'),
                $productGalleryWrapper = $(this._imageProductGalleryWrapperSelector).find('.product-image.video-item');

            $imageWidget.parent().addClass('video-item');
            $productGalleryWrapper.parent().addClass('video-item');
            $imageWidget.removeClass('video-item');
            $productGalleryWrapper.removeClass('video-item');
            $('.video-item .action-delete').attr('title', $.mage.__('Delete video'));
            $('.video-item .action-delete span').html($.mage.__('Delete video'));
        },

        /**
         * Fired when click on create video
         * @private
         */
        _onCreate: function () {
            var nvs = this.element.find(this._videoPreviewInputSelector),
                file = nvs.get(0),
                reqClass = 'required-entry _required';

            if (file && file.files && file.files.length) {
                file = file.files[0];
            } else {
                file = null;
            }

            if (!file && !this._tempPreviewImageData) {
                nvs.addClass(reqClass);
            }

            this.isValid($.proxy(
                function (videoValidStatus) {

                    if (!videoValidStatus) {
                        return;
                    }

                    if (this._tempPreviewImageData) {
                        this._onImageLoaded(this._tempPreviewImageData, null, null, $.proxy(this.close, this));
                    } else {
                        this._uploadImage(file, null, $.proxy(function () {
                            this.close();
                        }, this));
                    }

                    nvs.removeClass(reqClass);
                },
                this
            ));
        },

        /**
         * Fired when click on update video
         * @private
         */
        _onUpdate: function () {
            var inputFile, itemId, _inputSelector, mediaFields, imageData, flagChecked, fileName, callback;

            this.isValid($.proxy(
                function (videoValidStatus) {

                    if (!videoValidStatus) {
                        return;
                    }

                    imageData = this.imageData || {};
                    inputFile = this.element.find(this._videoPreviewInputSelector);
                    itemId = this.element.find(this._itemIdSelector).val();
                    itemId = itemId.slice(1, itemId.length - 1);
                    _inputSelector = '[name*="[' + itemId + ']"]';
                    mediaFields = this._gallery.find('input' + _inputSelector);
                    $.each(mediaFields, function (i, el) {
                        var elName = el.name,
                            start = elName.indexOf(itemId) + itemId.length + 2,
                            fieldName = elName.substring(start, el.name.length - 1),
                            _field = this.element.find('#' + fieldName),
                            _tmp;

                        if (_field.length > 0) {
                            _tmp = _inputSelector.slice(0, _inputSelector.length - 2) + '[' + fieldName + ']"]';
                            this._gallery.find(_tmp).val(_field.val());
                            imageData[fieldName] = _field.val();
                        }
                    }.bind(this));
                    flagChecked = this.element.find(this._videoDisableinputSelector).prop('checked') ? 1 : 0;
                    this._gallery.find('input[name*="' + itemId + '][disabled]"]').val(flagChecked);
                    this._gallery.find(_inputSelector).siblings('.image-fade').css(
                        'visibility',
                        flagChecked ? 'visible' : 'hidden'
                    );
                    imageData.disabled = flagChecked;

                    if (this._tempPreviewImageData) {
                        this._onImageLoaded(
                            this._tempPreviewImageData,
                            null,
                            imageData.file,
                            $.proxy(this.close, this)
                        );

                        return;
                    }
                    fileName = inputFile.get(0).files;

                    if (!fileName || !fileName.length) {
                        fileName = null;
                    }
                    inputFile.replaceWith(inputFile);

                    callback = $.proxy(function () {
                        this.close();
                    }, this);

                    if (fileName) {
                        this._uploadImage(fileName, imageData.file, callback);
                    } else {
                        this._replaceImage(imageData.file, imageData.file, imageData);
                        callback(0, imageData);
                    }
                },
                this
            ));
        },

        /**
         * Delegates call to producwt gallery to update video visibility.
         *
         * @param {Object} imageData
         */
        _updateVisibility: function (imageData) {
            this._gallery.trigger('updateVisibility', {
                disabled: imageData.disabled,
                imageData: imageData
            });
        },

        /**
         * Delegates call to product gallery to update video title.
         *
         * @param {Object} imageData
         */
        _updateImageTitle: function (imageData) {
            this._gallery.trigger('updateImageTitle', {
                imageData: imageData
            });
        },

        /**
         * Fired when clicked on cancel
         * @private
         */
        _onCancel: function () {
            this.close();
        },

        /**
         * Fired when clicked on delete
         * @private
         */
        _onDelete: function () {
            var filename = this.element.find(this._videoImageFilenameselector).val();

            this._removeImage(filename);
            this.close();
        },

        /**
         * @param {String} file
         * @param {Function} callback
         * @private
         */
        _readPreviewLocal: function (file, callback) {
            var fr = new FileReader;

            if (!window.FileReader) {
                return;
            }

            /**
             * On load end
             */
            fr.onloadend = function () {
                callback(fr.result);
            };
            fr.readAsDataURL(file);
        },

        /**
         *  Image file input handler
         * @private
         */
        _onImageInputChange: function () {
            var jFile = this.element.find(this._videoPreviewInputSelector),
                file = jFile[0],
                val = jFile.val(),
                prev = this._getPreviewImage(),
                ext = '.' + val.split('.').pop();

            if (!val) {
                return;
            }
            ext = ext ? ext.toLowerCase() : '';

            if (
                ext.length < 2 ||
                this._imageTypes.indexOf(ext.toLowerCase()) === -1 || !file.files || !file.files.length
            ) {
                prev.remove();
                this._previewImage = null;
                jFile.val('');

                return;
            } // end if
            file = file.files[0];
            this._tempPreviewImageData = null;
            this._onPreview(null, file, true);
        },

        /**
         * Change Preview
         * @param {String} error
         * @param {String} src
         * @param {Boolean} local
         * @private
         */
        _onPreview: function (error, src, local) {
            var img, renderImage;

            img = this._getPreviewImage();

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
                return;
            }

            if (!local) {
                renderImage(src);
            } else {
                this._readPreviewLocal(src, renderImage);
            }
        },

        /**
         *
         * Return preview image imstance
         * @returns {null}
         * @private
         */
        _getPreviewImage: function () {

            if (!this._previewImage) {
                this._previewImage = $(document.createElement('img')).css({
                    'width': '100%',
                    'display': 'none',
                    'src': ''
                });
                $(this._previewImage).insertAfter(this.element.find(this._videoPreviewImagePointer));
                $(this._previewImage).attr('data-role', 'video_preview_image');
            }

            return this._previewImage;
        },

        /**
         * Close slideout dialog
         */
        close: function () {
            this.element.modal('closeModal');
        },

        /**
         * Close dialog wrap
         * @private
         */
        _onClose: function () {
            var newVideoForm;

            this._isEditPage = true;
            this.imageData = null;

            if (this._previewImage) {
                this._previewImage.remove();
                this._previewImage = null;
            }
            this._tempPreviewImageData = null;
            this.element.trigger('reset');
            newVideoForm = this.element.find(this._videoFormSelector);

            $(newVideoForm).find('input[type="hidden"][name!="form_key"]').val('');
            this._gallery.find(
                'input[name*="' + this.element.find(this._itemIdSelector).val() + '"]'
            ).parent().removeClass('active');

            try {
                newVideoForm.validation('clearError');
            } catch (e) {

            }
            newVideoForm.trigger('reset');
        },

        /**
         * Find element by fileName
         * @param {String} file
         */
        findElementId: function (file) {
            var elem = this._gallery.find('.image.item').find('input[value="' + file + '"]');

            if (!elem.length) {
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

            if (data && data.length > 0) {
                containers = this._gallery.find('.image-placeholder').siblings('input');
                $.each(containers, function (i, el) {
                    var start = el.name.indexOf('[') + 1,
                        end = el.name.indexOf(']'),
                        imageType = el.name.substring(start, end),
                        imageCheckbox = self.element.find(
                            self._videoFormSelector + ' input[value="' + imageType + '"]'
                        );

                    self._changeRole(imageType, imageCheckbox.prop('checked'), imageData);
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
                needCheked = this._gallery.find('input[name="product[' + imageType + ']"]').val() === imageData.file;
            }

            if (!needCheked) {
                return null;
            }

            this._gallery.trigger('setImageType', {
                type: imageType,
                imageData: isEnabled ? imageData : null
            });
        },

        /**
         * On open dialog
         * @param {Object} e
         * @param {Object} imageData
         * @private
         */
        _onOpenDialog: function (e, imageData) {
            var formFields, flagChecked, file,
                modal = this.element.closest('.mage-new-video-dialog');

            if (imageData['media_type'] === 'external-video') {
                this.imageData = imageData;
                modal.find('.video-create-button').hide();
                modal.find('.video-delete-button').show();
                modal.find('.video-edit').show();
                modal.createVideoPlayer({
                    reset: true
                }).createVideoPlayer('reset');

                formFields = modal.find(this._videoFormSelector).find('.edited-data');

                $.each(formFields, function (i, field) {
                    $(field).val(imageData[field.name]);
                });

                flagChecked = imageData.disabled > 0;
                modal.find(this._videoDisableinputSelector).prop('checked', flagChecked);

                file = modal.find('#file_name').val(imageData.file);

                $.each(modal.find('.video_image_role'), function () {
                    $(this).prop('checked', false).prop('disabled', false);
                });

                $.each(this._gallery.find('.image-placeholder').siblings('input:hidden'), function () {
                    var start, end, imageRole;

                    if ($(this).val() === file.val()) {
                        start = this.name.indexOf('[') + 1;
                        end = this.name.length - 1;
                        imageRole = this.name.substring(start, end);
                        modal.find('#new_video_form input[value="' + imageRole + '"]').prop('checked', true);
                    }
                });
            }

        },

        /**
         * Toggle buttons
         */
        toggleButtons: function () {
            var self = this,
                modal = this.element.closest('.mage-new-video-dialog');

            modal.find('.video-placeholder, .add-video-button-container > button').click(function () {
                modal.find('.video-create-button').show();
                modal.find('.video-delete-button').hide();
                modal.find('.video-edit').hide();
                modal.createVideoPlayer({
                    reset: true
                }).createVideoPlayer('reset').updateInputFields({
                    reset: true
                }).updateInputFields('reset');
            });
            this._gallery.on('click', '.item.video-item', function () {
                modal.find('.video-create-button').hide();
                modal.find('.video-delete-button').show();
                modal.find('.video-edit').show();
                modal.find('.mage-new-video-dialog').createVideoPlayer({
                    reset: true
                }).createVideoPlayer('reset');
            });
            this._gallery.on('click', '.item.video-item:not(.removed)', function () {
                var flagChecked,
                    file,
                    formFields = modal.find('.edited-data'),
                    container = $(this);

                $.each(formFields, function (i, field) {
                    $(field).val(container.find('input[name*="' + field.name + '"]').val());
                });

                flagChecked = container.find('input[name*="disabled"]').val() > 0;
                self._gallery.find(self._videoDisableinputSelector).prop('checked', flagChecked);

                file = self._gallery.find('#file_name').val(container.find('input[name*="file"]').val());

                $.each(self._gallery.find('.video_image_role'), function () {
                    $(this).prop('checked', false).prop('disabled', false);
                });

                $.each(self._gallery.find('.image-placeholder').siblings('input:hidden'), function () {
                    var start, end, imageRole;

                    if ($(this).val() !== file.val()) {
                        return null;
                    }

                    start = this.name.indexOf('[') + 1;
                    end = this.name.length - 1;
                    imageRole = this.name.substring(start, end);
                    self._gallery.find('input[value="' + imageRole + '"]').prop('checked', true);
                });
            });
        }
    });

    $('#group-fields-image-management > legend > span').text($.mage.__('Images and Videos'));

    return $.mage.newVideoDialog;
});
