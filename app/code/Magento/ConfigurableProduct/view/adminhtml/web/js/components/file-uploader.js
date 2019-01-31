/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/form/element/file-uploader',
    'underscore'
], function (Element, _) {
    'use strict';

    return Element.extend({
        processedFile: {},
        actionsListOpened: false,
        thumbnailUrl: '',
        thumbnail: null,
        smallImage: null,
        defaults: {
            fileInputName: ''
        },

        /**
         * Initialize observables.
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super().observe(['processedFile', 'actionsListOpened', 'thumbnailUrl', 'thumbnail', 'smallImage']);

            return this;
        },

        /** @inheritdoc */
        setInitialValue: function () {
            var value = this.getInitialValue();

            if (!_.isString(value)) {
                this._super();
            }

            return this;
        },

        /**
         * Adds provided file to the files list.
         *
         * @param {Object} file
         * @returns {Object} Chainable.
         */
        addFile: function (file) {
            this.processedFile(this.processFile(file));

            this.value(this.processedFile().file);

            return this;
        },

        /**
         * Toggle actions list.
         *
         * @returns {Object} Chainable.
         */
        toggleActionsList: function () {
            if (this.actionsListOpened()) {
                this.actionsListOpened(false);
            } else {
                this.actionsListOpened(true);
            }

            return this;
        },

        /**
         * Close action list.
         *
         * @returns {Object} Chainable
         */
        closeList: function () {
            if (this.actionsListOpened()) {
                this.actionsListOpened(false);
            }

            return this;
        },

        /**
         * Delete Image
         *
         * @returns {Object} Chainable
         */
        deleteImage: function () {
            this.processedFile({});
            this.value(null);
            this.thumbnail(null);
            this.thumbnailUrl(null);
            this.smallImage(null);

            return this;
        }
    });
});
