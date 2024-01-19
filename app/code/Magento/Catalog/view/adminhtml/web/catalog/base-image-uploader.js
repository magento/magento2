/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable no-undef */

define([
    'jquery',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    'jquery/ui',
    'jquery/uppy-core',
    'mage/translate',
    'mage/backend/notification'
], function ($, mageTemplate, alert) {
    'use strict';

    $.widget('mage.baseImage', {
        /**
         * Button creation
         * @protected
         */
        options: {
            maxImageUploadCount: 10
        },

        /** @inheritdoc */
        _create: function () {
            var $container = this.element,
                imageTmpl = mageTemplate(this.element.find('[data-template=image]').html()),
                $dropPlaceholder = this.element.find('.image-placeholder'),
                $galleryContainer = $('#media_gallery_content'),
                mainClass = 'base-image',
                maximumImageCount = 5,
                $fieldCheckBox = $container.closest('[data-attribute-code=image]').find(':checkbox'),
                isDefaultChecked = $fieldCheckBox.is(':checked'),
                findElement, updateVisibility;

            if (isDefaultChecked) {
                $fieldCheckBox.trigger('click');
            }

            /**
             * @param {Object} data
             * @return {HTMLElement}
             */
            findElement = function (data) {
                return $container.find('.image:not(.image-placeholder)').filter(function () {
                    if (!$(this).data('image')) {
                        return false;
                    }

                    return $(this).data('image').file === data.file;
                }).first();
            };

            /** Update image visibility. */
            updateVisibility = function () {
                var elementsList = $container.find('.image:not(.removed-item)');

                elementsList.each(function (index) {
                    $(this)[index < maximumImageCount ? 'show' : 'hide']();
                });
                $dropPlaceholder[elementsList.length > maximumImageCount ? 'hide' : 'show']();
            };

            $galleryContainer.on('setImageType', function (event, data) {
                if (data.type === 'image') {
                    $container.find('.' + mainClass).removeClass(mainClass);

                    if (data.imageData) {
                        findElement(data.imageData).addClass(mainClass);
                    }
                }
            });

            $galleryContainer.on('addItem', function (event, data) {
                var tmpl = imageTmpl({
                    data: data
                });

                $(tmpl).data('image', data).insertBefore($dropPlaceholder);

                updateVisibility();
            });

            $galleryContainer.on('removeItem', function (event, image) {
                findElement(image).addClass('removed-item').hide();
                updateVisibility();
            });

            $galleryContainer.on('moveElement', function (event, data) {
                var $element = findElement(data.imageData),
                    $after;

                if (data.position === 0) {
                    $container.prepend($element);
                } else {
                    $after = $container.find('.image').eq(data.position);

                    if (!$element.is($after)) {
                        $element.insertAfter($after);
                    }
                }
                updateVisibility();
            });

            $container.on('click', '[data-role=make-base-button]', function (event) {
                var data;

                event.preventDefault();
                data = $(event.target).closest('.image').data('image');
                $galleryContainer.productGallery('setBase', data);
            });

            $container.on('click', '[data-role=delete-button]', function (event) {
                event.preventDefault();
                $galleryContainer.trigger('removeItem', $(event.target).closest('.image').data('image'));
            });

            $container.sortable({
                axis: 'x',
                items: '.image:not(.image-placeholder)',
                distance: 8,
                tolerance: 'pointer',

                /**
                 * @param {jQuery.Event} event
                 * @param {Object} data
                 */
                stop: function (event, data) {
                    $galleryContainer.trigger('setPosition', {
                        imageData: data.item.data('image'),
                        position: $container.find('.image').index(data.item)
                    });
                    $galleryContainer.trigger('resort');
                }
            }).disableSelection();

            // uppy implemetation
            let targetElement = this.element.find('input[type="file"]').parent()[0],
                url = '{$block->escapeJs($block->getJsUploadUrl())}',
                fileId = null,
                arrayFromObj = Array.from,
                fileObj = [],
                uploaderContainer = this.element.find('input[type="file"]').closest('.image-placeholder'),
                options = {
                    proudlyDisplayPoweredByUppy: false,
                    target: targetElement,
                    hideUploadButton: false,
                    hideRetryButton: true,
                    hideCancelButton: true,
                    inline: true,
                    showRemoveButtonAfterComplete: true,
                    showProgressDetails: false,
                    showSelectedFiles: false,
                    allowMultipleUploads: false,
                    hideProgressAfterFinish: true
                };

            const uppy = new Uppy.Uppy({
                restrictions: {
                    allowedFileTypes: ['.gif', '.jpeg', '.jpg', '.png'],
                    maxFileSize: this.element.data('maxFileSize')
                },

                onBeforeFileAdded: (currentFile) => {

                    if (fileObj.length > this.options.maxImageUploadCount) {
                        $('body').notification('clear').notification('add', {
                            error: true,
                            message: $.mage.__('You can\'t upload more than ' + this.options.maxImageUploadCount +
                                ' images in one time'),

                            /**
                             * @param {*} message
                             */
                            insertMethod: function (message) {
                                $('.page-main-actions').after(message);
                            }
                        });
                        return false;
                    }

                    fileId = Math.random().toString(36).substr(2, 9);
                    // code to allow duplicate files from same folder
                    const modifiedFile = {
                        ...currentFile,
                        id:  currentFile.id + '-' + fileId,
                        tempFileId:  fileId
                    };

                    uploaderContainer.addClass('loading');
                    fileObj.push(currentFile);
                    return modifiedFile;
                },

                meta: {
                    'form_key': window.FORM_KEY,
                    isAjax : true
                }
            });

            // initialize Uppy upload
            uppy.use(Uppy.Dashboard, options);

            // drop area for file upload
            uppy.use(Uppy.DropTarget, {
                target: $dropPlaceholder.closest('[data-attribute-code]')[0],
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
                endpoint: url,
                fieldName: 'image'
            });

            uppy.on('upload-progress', (file, progress) => {
                let progressWidth = parseInt(progress.bytesUploaded / progress.bytesTotal * 100, 10);

                $dropPlaceholder.find('.progress-bar').addClass('in-progress').text(progressWidth + '%');
            });

            uppy.on('upload-success', (file, response) => {
                $dropPlaceholder.find('.progress-bar').text('').removeClass('in-progress');

                if (!response.body) {
                    return;
                }

                if (!response.body.error) {
                    $galleryContainer.trigger('addItem', response.body);
                } else {
                    alert({
                        content: $.mage.__('We don\'t recognize or support this file extension type.')
                    });
                }
            });

            uppy.on('complete', () => {
                uploaderContainer.removeClass('loading');
                Array.from = arrayFromObj;
            });
        }
    });

    return $.mage.baseImage;
});
