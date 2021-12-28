/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
    'jquery/file-uploader'
], function ($, mageTemplate, alert, FileUploader) {
    'use strict';

    var fileUploader = new FileUploader({
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
            var self = this,
                progressTmpl = mageTemplate('[data-template="uploader"]'),
                isResizeEnabled = this.options.isResizeEnabled,
                resizeConfiguration = {
                    action: 'resizeImage',
                    maxWidth: this.options.maxWidth,
                    maxHeight: this.options.maxHeight
                };

            if (!isResizeEnabled) {
                resizeConfiguration = {
                    action: 'resizeImage'
                };
            }

            this.element.find('input[type=file]').fileupload({
                dataType: 'json',
                formData: {
                    'form_key': window.FORM_KEY
                },
                dropZone: '[data-tab-panel=image-management]',
                sequentialUploads: true,
                acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
                maxFileSize: this.options.maxFileSize,

                /**
                 * @param {Object} e
                 * @param {Object} data
                 */
                add: function (e, data) {
                    var fileSize,
                        tmpl;

                    $.each(data.files, function (index, file) {
                        fileSize = typeof file.size == 'undefined' ?
                            $.mage.__('We could not detect a size.') :
                            byteConvert(file.size);

                        data.fileId = Math.random().toString(33).substr(2, 18);

                        tmpl = progressTmpl({
                            data: {
                                name: file.name,
                                size: fileSize,
                                id: data.fileId
                            }
                        });

                        $(tmpl).appendTo(self.element);
                    });

                    $(this).fileupload('process', data).done(function () {
                        data.submit();
                    });
                },

                /**
                 * @param {Object} e
                 * @param {Object} data
                 */
                done: function (e, data) {
                    if (data.result && !data.result.error) {
                        self.element.trigger('addItem', data.result);
                    } else {
                        fileUploader.aggregateError(data.files[0].name, data.result.error);
                    }

                    self.element.find('#' + data.fileId).remove();
                },

                /**
                 * @param {Object} e
                 * @param {Object} data
                 */
                progress: function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10),
                        progressSelector = '#' + data.fileId + ' .progressbar-container .progressbar';

                    self.element.find(progressSelector).css('width', progress + '%');
                },

                /**
                 * @param {Object} e
                 * @param {Object} data
                 */
                fail: function (e, data) {
                    var progressSelector = '#' + data.fileId;

                    self.element.find(progressSelector).removeClass('upload-progress').addClass('upload-failure')
                        .delay(2000)
                        .hide('highlight')
                        .remove();
                },

                stop: fileUploader.uploaderConfig.stop
            });

            this.element.find('input[type=file]').fileupload('option', {
                processQueue: [{
                    action: 'loadImage',
                    fileTypes: /^image\/(gif|jpeg|png)$/
                },
                resizeConfiguration,
                {
                    action: 'saveImage'
                }]
            });
        }
    });

    return $.mage.mediaUploader;
});
