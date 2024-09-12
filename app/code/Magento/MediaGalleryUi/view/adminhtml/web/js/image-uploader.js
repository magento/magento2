/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable no-undef */

define([
    'uiComponent',
    'jquery',
    'underscore',
    'Magento_Ui/js/lib/validation/validator',
    'mage/translate',
    'jquery/uppy-core'
], function (Component, $, _, validator, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            imageUploadInputSelector: '#image-uploader-form',
            directoriesPath: 'media_gallery_listing.media_gallery_listing.media_gallery_directories',
            actionsPath: 'media_gallery_listing.media_gallery_listing.media_gallery_columns.thumbnail_url',
            messagesPath: 'media_gallery_listing.media_gallery_listing.messages',
            imageUploadUrl: '',
            acceptFileTypes: '',
            allowedExtensions: '',
            maxFileSize: '',
            maxFileNameLength: 90,
            loader: false,
            modules: {
                directories: '${ $.directoriesPath }',
                actions: '${ $.actionsPath }',
                mediaGridMessages: '${ $.messagesPath }',
                sortBy: '${ $.sortByName }',
                listingPaging: '${ $.listingPagingName }'
            }
        },

        /**
         * Init component
         *
         * @return {exports}
         */
        initialize: function () {
            this._super().observe(
                [
                    'loader',
                    'count'
                ]
            );

            return this;
        },

        /**
         * Initializes file upload library
         */
        initializeFileUpload: function () {

            let id = this.imageUploadInputSelector,
                arrayFromObj = Array.from,
                options = {
                    proudlyDisplayPoweredByUppy: false,
                    target: id,
                    hideUploadButton: true,
                    hideRetryButton: true,
                    hideCancelButton: true,
                    inline: true,
                    showRemoveButtonAfterComplete: true,
                    showProgressDetails: false,
                    showSelectedFiles: false,
                    hideProgressAfterFinish: true
                },

                uppyDashboard = new Uppy.Uppy({
                    autoProceed: true,
                    // validation before file get added
                    onBeforeFileAdded: (currentFile) => {
                        if (!this.isSizeExceeded(currentFile).passed) {
                            this.addValidationErrorMessage(
                                $t('Cannot upload "%1". File exceeds maximum file size limit.')
                                    .replace('%1', currentFile.name)
                            );
                            return false;
                        } else if (!this.isFileNameLengthExceeded(currentFile).passed) {
                            this.addValidationErrorMessage(
                                $t('Cannot upload "%1". Filename is too long, must be 90 characters or less.')
                                    .replace('%1', currentFile.name)
                            );
                            return false;
                        }

                        // code to allow duplicate files from same folder
                        const modifiedFile = {
                            ...currentFile,
                            id:  currentFile.id + '-' + Date.now()
                        };

                        this.showLoader();
                        this.count(1);
                        return modifiedFile;
                    },
                    meta: {
                        'isAjax': true,
                        'form_key': window.FORM_KEY
                    }
                });

            // initialize Uppy upload
            uppyDashboard.use(Uppy.Dashboard, options);

            // drop area for file upload
            uppyDashboard.use(Uppy.DropTarget, {
                target: document.body,
                onDragOver: () => {
                    // override Array.from method of legacy-build.min.js file
                    Array.from = null;
                },
                onDragLeave: () => {
                    Array.from = arrayFromObj;
                }
            });

            // upload files on server
            uppyDashboard.use(Uppy.XHRUpload, {
                endpoint: this.imageUploadUrl,
                fieldName: 'image'
            });

            uppyDashboard.on('file-added', () => {
                uppyDashboard.setMeta({
                    target_folder: this.getTargetFolder()
                });
            });

            uppyDashboard.on('upload-success', (file, response) => {
                let data = {
                    files : [file]
                };

                if (!response) {
                    this.showErrorMessage(data, $t('Could not upload the asset.'));
                    return;
                }

                if (!response.body.success) {
                    this.showErrorMessage(data, response.body.message);
                    return;
                }

                this.showSuccessMessage(data);
                this.hideLoader();
                this.actions().reloadGrid();
            });

            uppyDashboard.on('complete', () => {
                this.openNewestImages();
                this.mediaGridMessages().scheduleCleanup();
                Array.from = arrayFromObj;
            });

            // handle network failure or some other upload issue
            uppyDashboard.on('error', () => {
                this.showUploadErrorMessage();
            });
        },

        /**
         * Show upload error message
         */
        showUploadErrorMessage: function () {
            let bodyObj = $('body');

            bodyObj.notification('clear');
            bodyObj.notification('add', {
                error: true,
                message: $.mage.__(
                    'A technical problem with the server created an error. ' +
                    'Try again to continue what you were doing. If the problem persists, try again later.'
                ),

                /**
                 * @param {String} message
                 */
                insertMethod: function (message) {
                    let $wrapper = $('<div></div>').html(message);

                    $('.page-main-actions').after($wrapper);
                }
            });

            this.hideLoader();
        },

        /**
         * Add error message after validation error.
         *
         * @param {String} message
         */
        addValidationErrorMessage: function (message) {
            this.mediaGridMessages().add('error', message);

            this.count() < 2 || this.mediaGridMessages().scheduleCleanup();
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
         * Checks if name length of provided file exceeds
         * defined in configuration size limits.
         *
         * @param {Object} file - File to be checked.
         * @returns {Boolean}
         */
        isFileNameLengthExceeded: function (file) {
            return validator('max_text_length', file.name, this.maxFileNameLength);
        },

        /**
         * Go to recently uploaded images if at least one uploaded successfully
         */
        openNewestImages: function () {
            this.mediaGridMessages().get().each(function (message) {
                if (message.code === 'success') {
                    this.actions().deselectImage();
                    this.sortBy().selectDefaultOption();
                    this.listingPaging().goFirst();

                    return false;
                }
            }.bind(this));
        },

        /**
         * Show error messages with file name.
         *
         * @param {Object} data
         * @param {String} message
         */
        showErrorMessage: function (data, message) {
            data.files.each(function (file) {
                this.mediaGridMessages().add(
                    'error',
                    file.name + ': ' + $t(message)
                );
            }.bind(this));

            this.hideLoader();
        },

        /**
         * Show success message, and files counts
         */
        showSuccessMessage: function () {
            this.mediaGridMessages().messages.remove(function (item) {
                return item.code === 'success';
            });
            this.mediaGridMessages().add('success', $t('Assets have been successfully uploaded!'));
            this.count(this.count() + 1);

        },

        /**
         * Gets Media Gallery selected folder
         *
         * @returns {String}
         */
        getTargetFolder: function () {

            if (_.isUndefined(this.directories().activeNode()) ||
                _.isNull(this.directories().activeNode())) {
                return '/';
            }

            return this.directories().activeNode();
        },

        /**
         * Shows spinner loader
         */
        showLoader: function () {
            this.loader(true);
        },

        /**
         * Hides spinner loader
         */
        hideLoader: function () {
            this.loader(false);
        }
    });
});
