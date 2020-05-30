/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/grid/columns/column',
    'Magento_Ui/js/lib/key-codes'
], function ($, Column, keyCodes) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/columns/image-preview',
            previewImageSelector: '[data-image-preview]',
            visibleRecord: null,
            height: 0,
            displayedRecord: {},
            lastOpenedImage: false,
            fields: {
                previewUrl: 'preview_url',
                title: 'title'
            },
            modules: {
                masonry: '${ $.parentName }',
                thumbnailComponent: '${ $.parentName }.thumbnail_url'
            },
            statefull: {
                sorting: true,
                lastOpenedImage: true
            },
            listens: {
                '${ $.provider }:params.filters': 'hide',
                '${ $.provider }:params.search': 'hide',
                '${ $.provider }:params.paging': 'hide'
            },
            exports: {
                height: '${ $.parentName }.thumbnail_url:previewHeight'
            }
        },

        /**
         * Initialize image preview component
         *
         * @returns {Object}
         */
        initialize: function () {
            this._super();
            $(document).on('keydown', this.handleKeyDown.bind(this));

            return this;
        },

        /**
         * Init observable variables
         * @return {Object}
         */
        initObservable: function () {
            this._super()
                .observe([
                    'visibleRecord',
                    'height',
                    'displayedRecord',
                    'lastOpenedImage'
                ]);

            return this;
        },

        /**
         * Next image preview
         *
         * @param {Object} record
         */
        next: function (record) {
            var recordToShow;

            if (record._rowIndex + 1 === this.masonry().rows().length) {
                return;
            }

            recordToShow = this.getRecord(record._rowIndex + 1);
            recordToShow.rowNumber = record.lastInRow ? record.rowNumber + 1 : record.rowNumber;
            this.show(recordToShow);
        },

        /**
         * Previous image preview
         *
         * @param {Object} record
         */
        prev: function (record) {
            var recordToShow;

            if (record._rowIndex === 0) {
                return;
            }
            recordToShow = this.getRecord(record._rowIndex - 1);

            recordToShow.rowNumber = record.firstInRow ? record.rowNumber - 1 : record.rowNumber;
            this.show(recordToShow);
        },

        /**
         * Get record
         *
         * @param {Integer} recordIndex
         *
         * @return {Object}
         */
        getRecord: function (recordIndex) {
            return this.masonry().rows()[recordIndex];
        },

        /**
         * Set selected row id
         *
         * @param {Number} rowId
         * @private
         */
        _selectRow: function (rowId) {
            this.thumbnailComponent().previewRowId(rowId);
        },

        /**
         * Show image preview
         *
         * @param {Object} record
         */
        show: function (record) {
            var img;

            if (record._rowIndex === this.visibleRecord()) {
                this.hide();

                return;
            }

            this.hide();
            this.displayedRecord(record);
            this._selectRow(record.rowNumber || null);
            this.visibleRecord(record._rowIndex);

            img = $(this.previewImageSelector + ' img');

            if (img.get(0).complete) {
                this.updateHeight();
                this.scrollToPreview();
            } else {
                img.load(function () {
                    this.updateHeight();
                    this.scrollToPreview();
                }.bind(this));
            }

            this.lastOpenedImage(record._rowIndex);
        },

        /**
         * Update image preview section height
         */
        updateHeight: function () {
            this.height($(this.previewImageSelector).height() + 'px');
        },

        /**
         * Close image preview
         */
        hide: function () {
            this.lastOpenedImage(false);
            this.visibleRecord(null);
            this.height(0);
            this._selectRow(null);
        },

        /**
         * Returns visibility for given record.
         *
         * @param {Object} record
         * @return {*|bool}
         */
        isVisible: function (record) {
            if (this.lastOpenedImage() === record._rowIndex &&
                this.visibleRecord() === null
            ) {
                this.show(record);
            }

            return this.visibleRecord() === record._rowIndex || false;
        },

        /**
         * Returns preview image url for a given record.
         *
         * @param {Object} record
         * @return {String}
         */
        getUrl: function (record) {
            return record[this.fields.previewUrl];
        },

        /**
         * Returns image title for a given record.
         *
         * @param {Object} record
         * @return {String}
         */
        getTitle: function (record) {
            return record[this.fields.title];
        },

        /**
         * Get styles for preview
         *
         * @returns {Object}
         */
        getStyles: function () {
            return {
                'margin-top': '-' + this.height()
            };
        },

        /**
         * Scroll to preview window
         */
        scrollToPreview: function () {
            $(this.previewImageSelector).get(0).scrollIntoView({
                behavior: 'smooth',
                block: 'center',
                inline: 'nearest'
            });
        },

        /**
         * Handle keyboard navigation for image preview
         *
         * @param {Object} e
         */
        handleKeyDown: function (e) {
            var key = keyCodes[e.keyCode];

            if (this.visibleRecord() !== null && document.activeElement.tagName !== 'INPUT') {
                if (key === 'pageLeftKey') {
                    this.prev(this.displayedRecord());
                } else if (key === 'pageRightKey') {
                    this.next(this.displayedRecord());
                }
            }
        }
    });
});
