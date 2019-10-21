/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
/* global Base64 */
define([
    'jquery',
    'underscore',
    'mageUtils',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/lib/validation/validator',
    'Magento_Ui/js/form/element/abstract',
    'mage/backend/notification',
    'mage/translate',
    'jquery/file-uploader',
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
            this.$fileInput = fileInput;

            _.extend(this.uploaderConfig, {
                dropZone:   $(fileInput).closest(this.dropZone),
                change:     this.onFilesChoosed.bind(this),
                drop:       this.onFilesChoosed.bind(this),
                add:        this.onBeforeFileUpload.bind(this),
                done:       this.onFileUploaded.bind(this),
                start:      this.onLoadingStart.bind(this),
                stop:       this.onLoadingStop.bind(this)
            });

            $(fileInput).fileupload(this.uploaderConfig);

            return this;
        },

        /**
         * Defines initial value of the instance.
         *
         * @returns {FileUploader} Chainable.
         */
        setInitialValue: function () {
            var value = this.getInitialValue();

            value = value.map(this.processFile, this);

            this.initialValue = value.slice();

            this.value(value);
            this.on('value', this.onUpdate.bind(this));
            this.isUseDefault(this.disabled());

            return this;
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
                file.id = Base64.mageEncode(file.name);
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
         * @param {Event} e - Event object.
         * @param {Object} data - File data that will be uploaded.
         */
        onFilesChoosed: function (e, data) {
            // no option exists in fileuploader for restricting upload chains to single files; this enforces that policy
            if (!this.isMultipleFiles) {
                data.files.splice(1);
            }
        },

        /**
         * Handler which is invoked prior to the start of a file upload.
         *
         * @param {Event} e - Event object.
         * @param {Object} data - File data that will be uploaded.
         */
        onBeforeFileUpload: function (e, data) {
            var file     = data.files[0],
                allowed  = this.isFileAllowed(file),
                target   = $(e.target);

            if (this.disabled()) {
                this.notifyError($t('The file upload field is disabled.'));

                return;
            }

            if (allowed.passed) {
                target.on('fileuploadsend', function (event, postData) {
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
         * Handler of the file upload complete event.
         *
         * @param {Event} e
         * @param {Object} data
         */
        onFileUploaded: function (e, data) {
            var uploadedFilename = data.files[0].name,
                file    = data.result,
                error   = file.error;

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
                            var errorMsgBodyHtml = '<strong>%s</strong> %s.<br>'
                                .replace('%s', error.filename)
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
         * @param {Event} e
         */
        onPreviewLoad: function (file, e) {
            var img = e.currentTarget;

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
