/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'mageUtils',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/lib/validation/validator',
    'Magento_Ui/js/form/element/file-uploader',
    'mage/adminhtml/browser'
], function ($, _, utils, uiAlert, validator, Element, browser) {
    'use strict';

    return Element.extend({

        /**
         * Assign uid for media gallery
         *
         * @return {ImageUploader} Chainable.
         */
        initConfig: function () {
            var mediaGalleryUid = utils.uniqueid();

            this._super();

            _.extend(this, {
                mediaGalleryUid: mediaGalleryUid
            });

            return this;
        },

        /**
         * Add file event callback triggered from media gallery
         *
         * @param {ImageUploader} imageUploader - UI Class
         * @param {Event} e
         */
        addFileFromMediaGallery: function (imageUploader, e) {
            var $buttonEl = $(e.target),
                fileSize = $buttonEl.data('size'),
                fileMimeType = $buttonEl.data('mime-type'),
                filePathname = $buttonEl.val(),
                fileBasename = filePathname.split('/').pop();

            this.addFile({
                type: fileMimeType,
                name: fileBasename,
                size: fileSize,
                url: filePathname
            });
        },

        /**
         * Open the media browser dialog using the
         *
         * @param {ImageUploader} imageUploader - UI Class
         * @param {Event} e
         */
        openMediaBrowserDialog: function (imageUploader, e) {
            var $buttonEl = $(e.target),
                openDialogUrl = this.mediaGallery.openDialogUrl +
                'target_element_id/' + $buttonEl.attr('id') +
                '/store/' + this.mediaGallery.storeId +
                '/type/image/use_storage_root/1?isAjax=true';

            browser.openDialog(openDialogUrl, null, null, this.mediaGallery.openDialogTitle);
        },

        /**
         * Trigger native browser file upload UI via clicking on 'Upload' button
         *
         * @param {ImageUploader} imageUploader - UI Class
         * @param {Event} e
         */
        triggerImageUpload: function (imageUploader, e) {
            $(e.target).closest('.file-uploader').find('input[type="file"]').click();
        },

        /**
         * Get list of file extensions allowed in comma delimited format
         *
         * @return {String}
         */
        getAllowedFileExtensionsInCommaDelimitedFormat: function () {
            var allowedExtensions = this.allowedExtensions.toUpperCase().split(' ');

            // if jpg and jpeg in allowed extensions, remove jpeg from list
            if (allowedExtensions.indexOf('JPG') !== -1 && allowedExtensions.indexOf('JPEG') !== -1) {
                allowedExtensions.splice(allowedExtensions.indexOf('JPEG'), 1);
            }

            return allowedExtensions.join(', ');
        }
    });
});
