/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    "jquery",
    'mage/template',
    'Magento_Ui/js/modal/alert',
    "mage/translate",
    "jquery/file-uploader"
], function ($, mageTemplate, alert) {
    'use strict';

    $.widget('mage.mediaUploader', {
        _create: function () {
            var
                self = this,
                progressTmpl = mageTemplate('[data-template="uploader"]');

            this.element.find('input[type=file]').fileupload({
                dataType: 'json',
                formData: {
                    'form_key': window.FORM_KEY
                },
                dropZone: '[data-tab-panel=image-management]',
                sequentialUploads: true,
                acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
                maxFileSize: this.options.maxFileSize,
                add: function (e, data) {
                    var
                        fileSize,
                        tmpl;

                    $.each(data.files, function (index, file) {
                        fileSize = typeof file.size == "undefined" ?
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
                done: function (e, data) {
                    if (data.result && !data.result.error) {
                        self.element.trigger('addItem', data.result);
                    } else {
                        alert({
                            content: $.mage.__('We don\'t recognize or support this file extension type.')
                        });
                    }
                    self.element.find('#' + data.fileId).remove();
                },
                progress: function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    var progressSelector = '#' + data.fileId + ' .progressbar-container .progressbar';
                    self.element.find(progressSelector).css('width', progress + '%');
                },
                fail: function (e, data) {
                    var progressSelector = '#' + data.fileId;
                    self.element.find(progressSelector).removeClass('upload-progress').addClass('upload-failure')
                        .delay(2000)
                        .hide('highlight')
                        .remove();
                }
            });
            
            this.element.find('input[type=file]').fileupload('option', {
                process: [{
                    action: 'load',
                    fileTypes: /^image\/(gif|jpeg|png)$/
                }, {
                    action: 'resize',
                    maxWidth: this.options.maxWidth,
                    maxHeight: this.options.maxHeight
                }, {
                    action: 'save'
                }]
            });
        }
    });

    return $.mage.mediaUploader;
});
