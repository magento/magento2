/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
/* global Base64 */
/* eslint-disable no-undef */
define([
    'jquery',
    'underscore',
    'mageUtils',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/lib/validation/validator',
    'Magento_Ui/js/form/element/abstract',
    'mage/backend/notification',
    'mage/translate',
    'jquery/jquery.cookie',
    'jquery/uppy-core',
    'mage/adminhtml/tools'
], function ($, _, utils, uiAlert, validator, Element, notification, $t) {
    'use strict';

    return Element.extend({
        defaults: {
            value: [],
            aggregatedErrors: [],
            maxFileSize: false,
            isMultipleFiles: false,
            placeholderType: 'document', // 'image', 'video'
            allowedExtensions: false,
            previewTmpl: 'ui/form/element/uploader/preview',
            dropZone: '[data-role=drop-zone]',
            isLoading: false,
            uploaderConfig: {
                dataType: 'json',
                sequentialUploads: true,
                formData: {
                    'form_key': window.FORM_KEY
                }
            },
            tracks: {
                isLoading: true
            }
        },

        /**
         * Initializes file uploader plugin on provided input element.
         *
         * @param {HTMLInputElement} fileInput
         * @returns {FileUploader} Chainable.
         */
        initUploader: function (fileInput) {
            _.extend(this.uploaderConfig, {
                dropZone: $(fileInput).closest(this.dropZone),
                change: this.onFilesChoosed.bind(this),
                drop: this.onFilesChoosed.bind(this),
                add: this.onBeforeFileUpload.bind(this),
                fail: this.onFail.bind(this),
                done: this.onFileUploaded.bind(this),
                start: this.onLoadingStart.bind(this),
                stop: this.onLoadingStop.bind(this)
            });

            // uppy implementation
            if (fileInput !== undefined) {
                let targetElement = $(fileInput).closest('.file-uploader-area')[0],
                    dropTargetElement = $(fileInput).closest(this.dropZone)[0],
                    formKey = window.FORM_KEY !== undefined ? window.FORM_KEY : $.cookie('form_key'),
                    fileInputName = this.fileInputName,
                    arrayFromObj = Array.from,
                    options = {
                        proudlyDisplayPoweredByUppy: false,
                        target: targetElement,
                        hideUploadButton: true,
                        hideRetryButton: true,
                        hideCancelButton: true,
                        inline: true,
                        showRemoveButtonAfterComplete: true,
                        showProgressDetails: false,
                        showSelectedFiles: false,
                        allowMultipleUploads: false,
                        hideProgressAfterFinish: true
                    };

                if (fileInputName === undefined) {
                    fileInputName = $(fileInput).attr('name');
                }
                // handle input type file
                this.replaceInputTypeFile(fileInput);

                const uppy = new Uppy.Uppy({
                    autoProceed: true,

                    onBeforeFileAdded: (currentFile) => {
                        let file = currentFile,
                            allowed = this.isFileAllowed(file);

                        if (this.disabled()) {
                            this.notifyError($t('The file upload field is disabled.'));
                            return false;
                        }

                        if (!allowed.passed)  {
                            this.aggregateError(file.name, allowed.message);
                            this.uploaderConfig.stop();
                            return false;
                        }

                        // code to allow duplicate files from same folder
                        const modifiedFile = {
                            ...currentFile,
                            id:  currentFile.id + '-' + Date.now()
                        };

                        this.onLoadingStart();
                        return modifiedFile;
                    },

                    meta: {
                        'form_key': formKey,
                        'param_name': fileInputName,
                        isAjax : true
                    }
                });

                // initialize Uppy upload
                uppy.use(Uppy.Dashboard, options);

                // drop area for file upload
                uppy.use(Uppy.DropTarget, {
                    target: dropTargetElement,
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
                    endpoint: this.uploaderConfig.url,
                    fieldName: fileInputName
                });

                uppy.on('upload-success', (file, response) => {
                    let data = {
                        files : [response.body],
                        result : response.body
                    };

                    this.onFileUploaded('', data);
                });

                uppy.on('upload-error', (file, error) => {
                    console.error(error.message);
                    console.error(error.status);
                });

                uppy.on('complete', () => {
                    this.onLoadingStop();
                    Array.from = arrayFromObj;
                });
            }
            return this;
        },

        /**
         * Replace Input type File with Span
         * and bind click event
         */
        replaceInputTypeFile: function (fileInput) {
            let fileId = fileInput.id, fileName = fileInput.name,
                spanElement = '<span id=\'' + fileId + '\'></span>';

            $('#' + fileId).closest('.file-uploader-area').attr('upload-area-id', fileName);
            $(fileInput).replaceWith(spanElement);
            $('#' + fileId).closest('.file-uploader-area').find('.file-uploader-button:first').on('click', function () {
                $('#' + fileId).closest('.file-uploader-area').find('.uppy-Dashboard-browse').trigger('click');
            });
        },

        /**
         * Defines initial value of the instance.
         *
         * @returns {FileUploader} Chainable.
         */
        setInitialValue: function () {
            var value = this.getInitialValue(),
                imageSize = this.setImageSize;

            _.each(value, function (val) {
                if (val.type !== undefined && val.type.indexOf('image') >= 0) {
                    imageSize(val);
                }
            }, this);

            value = value.map(this.processFile, this);

            this.initialValue = value.slice();

            this.value(value);
            this.on('value', this.onUpdate.bind(this));
            this.isUseDefault(this.disabled());

            return this;
        },

        /**
         * Set image size for already loaded image
         *
         * @param value
         * @returns {Promise<void>}
         */
        async setImageSize(value) {
            let response = await fetch(value.url),
                blob = await response.blob();

            value.size = blob.size;
        },

        /**
         * Empties files list.
         *
         * @returns {FileUploader} Chainable.
         */
        clear: function () {
            this.value.removeAll();

            return this;
        },

        /**
         * Checks if files list contains any items.
         *
         * @returns {Boolean}
         */
        hasData: function () {
            return !!this.value().length;
        },

        /**
         * Resets files list to its' initial value.
         *
         * @returns {FileUploader}
         */
        reset: function () {
            var value = this.initialValue.slice();

            this.value(value);

            return this;
        },

        /**
         * Adds provided file to the files list.
         *
         * @param {Object} file
         * @returns {FileUploader} Chainable.
         */
        addFile: function (file) {
            file = this.processFile(file);

            this.isMultipleFiles ?
                this.value.push(file) :
                this.value([file]);

            return this;
        },

        /**
         * Retrieves from the list file which matches
         * search criteria implemented in itertor function.
         *
         * @param {Function} fn - Function that will be invoked
         *      for each file in the list.
         * @returns {Object}
         */
        getFile: function (fn) {
            return _.find(this.value(), fn);
        },

        /**
         * Removes provided file from thes files list.
         *
         * @param {Object} file
         * @returns {FileUploader} Chainable.
         */
        removeFile: function (file) {
            this.value.remove(file);

            return this;
        },

        /**
         * May perform modifications on the provided
         * file object before adding it to the files list.
         *
         * @param {Object} file
         * @returns {Object} Modified file object.
         */
        processFile: function (file) {
            file.previewType = this.getFilePreviewType(file);

            if (!file.id && file.name) {
                file.id = Base64.idEncode(file.name);
            }

            this.observe.call(file, true, [
                'previewWidth',
                'previewHeight'
            ]);

            return file;
        },

        /**
         * Formats incoming bytes value to a readable format.
         *
         * @param {Number} bytes
         * @returns {String}
         */
        formatSize: function (bytes) {
            var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'],
                i;

            if (bytes === 0) {
                return '0 Byte';
            }

            i = window.parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));

            return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
        },

        /**
         * Returns path to the files' preview image.
         *
         * @param {Object} file
         * @returns {String}
         */
        getFilePreview: function (file) {
            return file.url;
        },

        /**
         * Returns path to the file's preview template.
         *
         * @returns {String}
         */
        getPreviewTmpl: function () {
            return this.previewTmpl;
        },

        /**
         * Checks if provided file is allowed to be uploaded.
         *
         * @param {Object} file
         * @returns {Object} Validation result.
         */
        isFileAllowed: function (file) {
            var result;

            _.every([
                this.isExtensionAllowed(file),
                this.isSizeExceeded(file)
            ], function (value) {
                result = value;

                return value.passed;
            });

            return result;
        },

        /**
         * Checks if extension of provided file is allowed.
         *
         * @param {Object} file - File to be checked.
         * @returns {Boolean}
         */
        isExtensionAllowed: function (file) {
            return validator('validate-file-type', file.name, this.allowedExtensions);
        },

        /**
         * Get simplified file type.
         *
         * @param {Object} file - File to be checked.
         * @returns {String}
         */
        getFilePreviewType: function (file) {
            var type;

            if (!file.type) {
                return 'document';
            }

            if (file.name.indexOf('?rand') !== -1 && file.type.indexOf('?rand') !== -1) {
                file.name = file.name.split('?')[0];
                file.type = file.type.split('?')[0];
            }

            type = file.type.split('/')[0];

            return type !== 'image' && type !== 'video' ? 'document' : type;
        },

        /**
         * Checks if size of provided file exceeds
         * defined in configuration size limits.
         *
         * @param {Object} file - File to be checked.
         * @returns {Boolean}
         */
        isSizeExceeded: function (file) {
            return validator('validate-max-size', file.size, this.maxFileSize);
        },

        /**
         * Displays provided error message.
         *
         * @param {String} msg
         * @returns {FileUploader} Chainable.
         */
        notifyError: function (msg) {
            var data = {
                content: msg
            };

            if (this.isMultipleFiles) {
                data.modalClass = '_image-box';
            }

            uiAlert(data);

            return this;
        },

        /**
         * Performs data type conversions.
         *
         * @param {*} value
         * @returns {Array}
         */
        normalizeData: function (value) {
            return utils.isEmpty(value) ? [] : value;
        },

        /**
         * Checks if files list is different
         * from its' initial value.
         *
         * @returns {Boolean}
         */
        hasChanged: function () {
            var value = this.value(),
                initial = this.initialValue;

            return !utils.equalArrays(value, initial);
        },

        /**
         * Handler which is invoked when files are choosed for upload.
         * May be used for implementation of additional validation rules,
         * e.g. total files and a total size rules.
         *
         * @param {Event} event - Event object.
         * @param {Object} data - File data that will be uploaded.
         */
        onFilesChoosed: function (event, data) {
            // no option exists in file uploader for restricting upload chains to single files
            // this enforces that policy
            if (!this.isMultipleFiles) {
                data.files.splice(1);
            }
        },

        /**
         * Handler which is invoked prior to the start of a file upload.
         *
         * @param {Event} event - Event object.
         * @param {Object} data - File data that will be uploaded.
         */
        onBeforeFileUpload: function (event, data) {
            var file = data.files[0],
                allowed = this.isFileAllowed(file),
                target = $(event.target);

            if (this.disabled()) {
                this.notifyError($t('The file upload field is disabled.'));

                return;
            }

            if (allowed.passed) {
                target.on('fileuploadsend', function (eventBound, postData) {
                    postData.data.append('param_name', this.paramName);
                }.bind(data));

                target.fileupload('process', data).done(function () {
                    data.submit();
                });
            } else {
                this.aggregateError(file.name, allowed.message);

                // if all files in upload chain are invalid, stop callback is never called; this resolves promise
                if (this.aggregatedErrors.length === data.originalFiles.length) {
                    this.uploaderConfig.stop();
                }
            }
        },

        /**
         * Add error message associated with filename for display when upload chain is complete
         *
         * @param {String} filename
         * @param {String} message
         */
        aggregateError: function (filename, message) {
            this.aggregatedErrors.push({
                filename: filename,
                message: message
            });
        },

        /**
         * @param {Event} event
         * @param {Object} data
         */
        onFail: function (event, data) {
            console.error(data.jqXHR.responseText);
            console.error(data.jqXHR.status);
        },

        /**
         * Handler of the file upload complete event.
         *
         * @param {Event} event
         * @param {Object} data
         */
        onFileUploaded: function (event, data) {
            var uploadedFilename = data.files[0].name,
                file = data.result,
                error = file.error;

            error ?
                this.aggregateError(uploadedFilename, error) :
                this.addFile(file);
        },

        /**
         * Load start event handler.
         */
        onLoadingStart: function () {
            this.isLoading = true;
        },

        /**
         * Load stop event handler.
         */
        onLoadingStop: function () {
            var aggregatedErrorMessages = [];

            this.isLoading = false;

            if (!this.aggregatedErrors.length) {
                return;
            }

            if (!this.isMultipleFiles) { // only single file upload occurred; use first file's error message
                aggregatedErrorMessages.push(this.aggregatedErrors[0].message);
            } else { // construct message from all aggregatedErrors
                _.each(this.aggregatedErrors, function (error) {
                    notification().add({
                        error: true,
                        message: '%s' + error.message, // %s to be used as placeholder for html injection

                        /**
                         * Adds constructed error notification to aggregatedErrorMessages
                         *
                         * @param {String} constructedMessage
                         */
                        insertMethod: function (constructedMessage) {
                            var escapedFileName = $('<div>').text(error.filename).html(),
                                errorMsgBodyHtml = '<strong>%s</strong> %s.<br>'
                                    .replace('%s', escapedFileName)
                                    .replace('%s', $t('was not uploaded'));

                            // html is escaped in message body for notification widget; prepend unescaped html here
                            constructedMessage = constructedMessage.replace('%s', errorMsgBodyHtml);

                            aggregatedErrorMessages.push(constructedMessage);
                        }
                    });
                });
            }

            this.notifyError(aggregatedErrorMessages.join(''));

            // clear out aggregatedErrors array for this completed upload chain
            this.aggregatedErrors = [];
        },

        /**
         * Handler function which is supposed to be invoked when
         * file input element has been rendered.
         *
         * @param {HTMLInputElement} fileInput
         */
        onElementRender: function (fileInput) {
            this.initUploader(fileInput);
        },

        /**
         * Handler of the preview image load event.
         *
         * @param {Object} file - File associated with an image.
         * @param {Event} event
         */
        onPreviewLoad: function (file, event) {
            var img = event.currentTarget;

            if (img.alt === file.name && /gif|png|jpe?g|webp/.test(file.url) && file.url.indexOf('?rand') === -1) {
                file.url += '?rand=' + Date.now();
                img.src = file.url;
            }

            file.previewWidth = img.naturalWidth;
            file.previewHeight = img.naturalHeight;
        },

        /**
         * Restore value to default
         */
        restoreToDefault: function () {
            var defaultValue = utils.copy(this.default);

            defaultValue.map(this.processFile, this);
            this.value(defaultValue);
        },

        /**
         * Update whether value differs from default value
         */
        setDifferedFromDefault: function () {
            var value = utils.copy(this.value());

            this.isDifferedFromDefault(!_.isEqual(value, this.default));
        }
    });
});
