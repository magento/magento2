/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable no-undef */

/**
 * @api
 */

/*global byteConvert*/
define([
    'jquery',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/form/element/file-uploader',
    'mage/translate',
    'jquery/uppy-core'
], function ($, mageTemplate, alert, FileUploader) {
    'use strict';

    let fileUploader = new FileUploader({
        dataScope: '',
        isMultipleFiles: true
    });

    fileUploader.initUploader();

    $.widget('mage.mediaUploader', {

        /**
         *
         * @private
         */
        _create: function () {
            let self = this,
                arrayFromObj = Array.from,
                progressTmpl = mageTemplate('[data-template="uploader"]'),
                uploaderElement = '#fileUploader',
                targetElement = this.element.find('.fileinput-button.form-buttons')[0],
                uploadUrl = $(uploaderElement).attr('data-url'),
                fileId = null,
                allowedExt = ['jpeg', 'jpg', 'png', 'gif'],
                allowedResize = false,
                options = {
                    proudlyDisplayPoweredByUppy: false,
                    target: targetElement,
                    hideUploadButton: true,
                    hideRetryButton: true,
                    hideCancelButton: true,
                    inline: true,
                    debug:true,
                    showRemoveButtonAfterComplete: true,
                    showProgressDetails: false,
                    showSelectedFiles: false,
                    hideProgressAfterFinish: true
                };

            $(document).on('click', uploaderElement ,function () {
                $(uploaderElement).closest('.fileinput-button.form-buttons')
                    .find('.uppy-Dashboard-browse').trigger('click');
            });

            const uppy = new Uppy.Uppy({
                autoProceed: true,

                onBeforeFileAdded: (currentFile) => {
                    let fileSize,
                        tmpl;

                    fileSize = typeof currentFile.size == 'undefined' ?
                        $.mage.__('We could not detect a size.') :
                        byteConvert(currentFile.size);

                    // check if file is allowed to upload and resize
                    allowedResize = $.inArray(currentFile.extension?.toLowerCase(), allowedExt) !== -1;

                    if (!allowedResize)  {
                        fileUploader.aggregateError(currentFile.name,
                            $.mage.__('Disallowed file type.'));
                        fileUploader.onLoadingStop();
                        return false;
                    }

                    fileId = Math.random().toString(33).substr(2, 18);

                    tmpl = progressTmpl({
                        data: {
                            name: currentFile.name,
                            size: fileSize,
                            id: fileId
                        }
                    });

                    // code to allow duplicate files from same folder
                    const modifiedFile = {
                        ...currentFile,
                        id:  currentFile.id + '-' + fileId,
                        tempFileId:  fileId
                    };

                    $(tmpl).appendTo(self.element);
                    return modifiedFile;
                },

                meta: {
                    'form_key': window.FORM_KEY,
                    isAjax : true
                }
            });

            // initialize Uppy upload
            uppy.use(Uppy.Dashboard, options);

            // Resize Image as per configuration
            if (this.options.isResizeEnabled) {
                uppy.use(Uppy.Compressor, {
                    maxWidth: this.options.maxWidth,
                    maxHeight: this.options.maxHeight,
                    quality: 0.92,
                    beforeDraw() {
                        if (!allowedResize) {
                            this.abort();
                        }
                    }
                });
            }

            // drop area for file upload
            uppy.use(Uppy.DropTarget, {
                target: targetElement,
                onDragOver: () => {
                    // override Array.from method of legacy-build.min.js file
                    Array.from = null;
                },
                onDragLeave: () => {
                    Array.from = arrayFromObj;
                }
            });

            // upload files on server
            uppy.use(Uppy.XHRUpload, {
                endpoint: uploadUrl,
                fieldName: 'image'
            });

            uppy.on('upload-success', (file, response) => {
                if (response.body && !response.body.error) {
                    self.element.trigger('addItem', response.body);
                } else {
                    fileUploader.aggregateError(file.name, response.body.error);
                }

                self.element.find('#' + file.tempFileId).remove();
            });

            uppy.on('upload-progress', (file, progress) => {
                let progressWidth = parseInt(progress.bytesUploaded / progress.bytesTotal * 100, 10),
                    progressSelector = '#' + file.tempFileId + ' .progressbar-container .progressbar';

                self.element.find(progressSelector).css('width', progressWidth + '%');
            });

            uppy.on('upload-error', (error, file) => {
                let progressSelector = '#' + file.tempFileId;

                self.element.find(progressSelector).removeClass('upload-progress').addClass('upload-failure')
                    .delay(2000)
                    .hide('highlight')
                    .remove();
            });

            uppy.on('complete', () => {
                fileUploader.uploaderConfig.stop();
                $(window).trigger('reload.MediaGallery');
                Array.from = arrayFromObj;
            });

        }
    });

    return $.mage.mediaUploader;
});
