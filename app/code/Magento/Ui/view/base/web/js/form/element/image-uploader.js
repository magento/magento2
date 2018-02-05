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
         * @param {FileUploader} fileUploader FileUploader UI Class
         * @param {Event} e
         * @return {void}
         */
        addFileFromMediaGallery: function (fileUploader, e) {
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
         * @param {FileUploader} fileUploader FileUploader UI Class
         * @param {Event} e
         * @return {void}
         */
        openMediaBrowserDialog: function (fileUploader, e) {
            var $buttonEl = $(e.target),
                openDialogUrl = this.mediaGallery.openDialogUrl +
                'target_element_id/' + $buttonEl.attr('id') +
                '/store/' + this.mediaGallery.storeId +
                '/type/image/?isAjax=true';

            browser.openDialog(openDialogUrl, null, null, this.mediaGallery.openDialogTitle);
        }
    });
});