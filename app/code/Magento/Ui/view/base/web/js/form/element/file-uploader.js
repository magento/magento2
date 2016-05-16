/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'mageUtils',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/lib/validation/validator',
    'Magento_Ui/js/form/element/abstract',
    'jquery/file-uploader'
], function ($, _, utils, uiAlert, validator, Element) {
    'use strict';

    return Element.extend({
        defaults: {
            value: [],
            maxFileSize: false,
            isMultipleFiles: false,
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
         * @returns {FileUploder} Chainable.
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
            uiAlert({
                content: msg
            });

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
         * Abstract handler which is invoked when files are choosed for upload.
         * May be used for implementation of aditional validation rules,
         * e.g. total files and a total size rules.
         *
         * @abstract
         */
        onFilesChoosed: function () {},

        /**
         * Handler which is invoked prior to the start of a file upload.
         *
         * @param {Event} e - Event obejct.
         * @param {Object} data - File data that will be uploaded.
         */
        onBeforeFileUpload: function (e, data) {
            var file     = data.files[0],
                allowed  = this.isFileAllowed(file);

            if (allowed.passed) {
                $(e.target).fileupload('process', data).done(function () {
                    data.submit();
                });
            } else {
                this.notifyError(allowed.message);
            }
        },

        /**
         * Handler of the file upload complete event.
         *
         * @param {Event} e
         * @param {Object} data
         */
        onFileUploaded: function (e, data) {
            var file    = data.result,
                error   = file.error;

            error ?
                this.notifyError(error) :
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
            this.isLoading = false;
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

            file.previewWidth = img.naturalHeight;
            file.previewHeight = img.naturalWidth;
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
