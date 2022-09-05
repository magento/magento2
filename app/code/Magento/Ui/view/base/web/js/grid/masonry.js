/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/listing',
    'Magento_Ui/js/lib/view/utils/raf',
    'jquery',
    'ko',
    'underscore'
], function (Listing, raf, $, ko, _) {
    'use strict';

    return Listing.extend({
        defaults: {
            template: 'ui/grid/masonry',
            imageRows: {},
            imports: {
                rows: '${ $.provider }:data.items',
                errorMessage: '${ $.provider }:data.errorMessage'
            },
            listens: {
                rows: 'initComponent'
            },

            /**
             * Images container id
             * @param string
             */
            containerId: null,

            /**
             * Minimum aspect ratio for each image
             * @param int
             */
            minRatio: null,

            /**
             * Container width
             * @param int
             */
            containerWidth: window.innerWidth,

            /**
             * Margin between images
             * @param int
             */
            imageMargin: 20,

            /**
             * Maximum image height value
             * @param int
             */
            maxImageHeight: 240,

            /**
             * The value is minimum image width to height ratio when container width is less than the key
             * @param {Object}
             */
            containerWidthToMinRatio: {
                640: 3,
                1280: 5,
                1920: 8
            },

            /**
             * Default minimal image width to height ratio.
             * Applied when container width is greater than max width in the containerWidthToMinRatio matrix.
             * @param int
             */
            defaultMinRatio: 10,

            /**
             * Layout update FPS during window resizing
             */
            refreshFPS: 60
        },

        /**
         * Init observable variables
         * @return {Object}
         */
        initObservable: function () {
            this._super()
                .observe([
                    'errorMessage'
                ]);

            return this;
        },

        /**
         * Init component handler
         *
         * @param {Object} images
         * @return {Object}
         */
        initComponent: function (images) {
            if (!images.length) {
                return;
            }
            this.imageMargin = parseInt(this.imageMargin, 10);

            this.setMinRatio();
            this.clearImageRows();
            this.initRows();
            this.setLayoutStylesWhenLoaded();
            this.setEventListener();

            return this;
        },

        /**
         * Initialize rows
         */
        initRows: function () {
            var ratio = 0,
                rowNumber = 1;

            this.rows.forEach(function (image, index) {
                this.initRow(rowNumber);

                image.styles = ko.observable({});
                image.ratio = parseFloat((image.width / image.height).toFixed(2));
                image.rowNumber = rowNumber;
                ratio += image.ratio;
                this.assignImageToRow(image, rowNumber);

                if (ratio < this.minRatio && index + 1 !== this.rows.length) {
                    // Row has more space for images and the image is not the last one - proceed to the next iteration
                    return;
                }

                this.assignRatioToRow(ratio, rowNumber);

                ratio = 0;
                rowNumber++;
            }.bind(this));
        },

        /**
         * Initialize row list by row number
         *
         * @param {Number} rowNumber
         */
        initRow: function (rowNumber) {
            if (!this.imageRows.hasOwnProperty(rowNumber)) {
                this.imageRows[rowNumber] = {
                    ratio: 0,
                    images: []
                };
            }
        },

        /**
         * Clear image rows before initialize
         */
        clearImageRows: function () {
            this.imageRows = {};
        },

        /**
         * Assign image to row
         *
         * @param {Object} image
         * @param {Number} rowNumber
         */
        assignImageToRow: function (image, rowNumber) {
            this.imageRows[rowNumber].images.push(image);
        },

        /**
         * Assign image to row
         *
         * @param {Number} ratio
         * @param {Number} rowNumber
         */
        assignRatioToRow: function (ratio, rowNumber) {
            this.imageRows[rowNumber].ratio = ratio;
        },

        /**
         * Set event listener to track resize event
         */
        setEventListener: function () {
            window.addEventListener('resize', function () {
                this.updateStyles();
            }.bind(this));
        },

        /**
         * Updates styles for component.
         */
        updateStyles: function () {
            raf(function () {
                this.containerWidth = window.innerWidth;
                this.setLayoutStylesWhenLoaded();
            }.bind(this), this.refreshFPS);
        },

        /**
         * Set layout styles inside the container
         */
        setLayoutStyles: function () {
            var containerWidth = parseInt(this.container.clientWidth, 10),
                isLastRow = false,
                ratio,
                rowHeight,
                calcHeight;

            _.each(this.imageRows, function (row, rowNumber) {
                ratio = Math.max(row.ratio, this.minRatio);
                calcHeight = (containerWidth - this.imageMargin * row.images.length) / ratio;
                rowHeight = calcHeight < this.maxImageHeight ? calcHeight : this.maxImageHeight;
                isLastRow = parseInt(rowNumber, 10) === Object.keys(this.imageRows).length;

                this.updateImagesInRow(row.images, rowHeight, isLastRow);

            }.bind(this));
        },

        /**
         * Apply styles, css classes and add properties for images in the row
         *
         * @param {Object[]} images
         * @param {Number} rowHeight
         * @param {Boolean} isLastRow
         */
        updateImagesInRow: function (images, rowHeight, isLastRow) {
            var imageWidth;

            images.forEach(function (img) {
                imageWidth = rowHeight * (img.width / img.height).toFixed(2);
                this.setImageStyles(img, imageWidth, rowHeight);
                this.setImageClass(img, {
                    bottom: isLastRow
                });
            }.bind(this));

            images[0].firstInRow = true;
            images[images.length - 1].lastInRow = true;
        },

        /**
         * Wait for container to initialize
         */
        waitForContainer: function (callback) {
            this.container = $('[data-id="' + this.containerId + '"]')[0];

            if (typeof this.container === 'undefined') {
                setTimeout(function () {
                    this.waitForContainer(callback);
                }.bind(this), 500);
            } else {
                setTimeout(callback, 0);
            }
        },

        /**
         * Set layout styles when container element is loaded.
         */
        setLayoutStylesWhenLoaded: function () {
            this.waitForContainer(function () {
                this.setLayoutStyles();
            }.bind(this));
        },

        /**
         * Set styles for every image in layout
         *
         * @param {Object} img
         * @param {Number} width
         * @param {Number} height
         */
        setImageStyles: function (img, width, height) {
            var styles = img.styles();

            styles = _.extend(styles, {
                width: parseInt(width, 10).toString() + 'px',
                height: parseInt(height, 10).toString() + 'px'
            });
            img.styles(styles);
        },

        /**
         * Set css classes to and an image
         *
         * @param {Object} image
         * @param {Object} classes
         */
        setImageClass: function (image, classes) {
            if (!image.css) {
                image.css = ko.observable(classes);
            }
            image.css(classes);
        },

        /**
         * Set min ratio for images in layout
         */
        setMinRatio: function () {
            var minRatio = _.find(
                this.containerWidthToMinRatio,

                /**
                 * Find the minimal ratio for container width in the matrix
                 *
                 * @param {Number} ratio
                 * @param {Number} width
                 * @returns {Boolean}
                 */
                function (ratio, width) {
                    return this.containerWidth <= width;
                },
                this
            );

            this.minRatio = minRatio ? minRatio : this.defaultMinRatio;
        },

        /**
         * Returns error message returned by the data provider
         *
         * @returns {String|null}
         */
        getErrorMessageUnsanitizedHtml: function () {
            return this.errorMessage();
        }
    });
});
