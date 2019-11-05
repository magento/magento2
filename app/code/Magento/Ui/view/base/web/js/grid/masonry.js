/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/listing',
    'jquery',
    'ko'
], function (Listing, $, ko) {
    'use strict';

    return Listing.extend({
        defaults: {
            template: 'Magento_Ui/grid/masonry',
            imports: {
                rows: '${ $.provider }:data.items',
                errorMessage: '${ $.provider }:data.errorMessage'
            },
            listens: {
                'rows': 'initComponent'
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
            defaultMinRatio: 10
        },

        /**
         * Init observable variables
         * @return {Object}
         */
        initObservable: function () {
            this._super()
                .observe([
                    'rows',
                    'errorMessage'
                ]);

            return this;
        },

        /**
         * Init component handler
         * @param {Object} rows
         * @return {Object}
         */
        initComponent: function (rows) {
            if (!rows || !rows.length) {
                return;
            }
            this.imageMargin = parseInt(this.imageMargin, 10);
            this.container = $('[data-id="' + this.containerId + '"]')[0];

            this.setLayoutStyles();
            this.setEventListener();

            return this;
        },

        /**
         * Set event listener to track resize event
         */
        setEventListener: function () {
            var running = false,
                handler = function () {
                    this.containerWidth = window.innerWidth;
                    this.setLayoutStyles();
                }.bind(this);

            window.addEventListener('resize', function () {
                if (!running) {
                    running = true;

                    if (window.requestAnimationFrame) {
                        window.requestAnimationFrame(function () {
                            handler();
                            running = false;
                        });
                    } else {
                        setTimeout(function () {
                            handler();
                            running = false;
                        }, 66);
                    }
                }
            });
        },

        /**
         * Set layout styles inside the container
         */
        setLayoutStyles: function () {
            var containerWidth = parseInt(this.container.clientWidth, 10),
                rowImages = [],
                ratio = 0,
                rowHeight = 0,
                calcHeight = 0,
                isLastRow = false,
                rowNumber = 1;

            this.setMinRatio();

            this.rows().forEach(function (image, index) {
                ratio += parseFloat((image.width / image.height).toFixed(2));
                rowImages.push(image);

                if (ratio < this.minRatio && index + 1 !== this.rows().length) {
                    // Row has more space for images and the image is not the last one - proceed to the next iteration
                    return;
                }

                ratio = Math.max(ratio, this.minRatio);
                calcHeight = (containerWidth - this.imageMargin * rowImages.length) / ratio;
                rowHeight = calcHeight < this.maxImageHeight ? calcHeight : this.maxImageHeight;
                isLastRow = index + 1 === this.rows().length;

                this.assignImagesToRow(rowImages, rowNumber, rowHeight, isLastRow);

                rowImages = [];
                ratio = 0;
                rowNumber++;

            }.bind(this));
        },

        /**
         * Apply styles, css classes and add properties for images in the row
         *
         * @param {Object[]} images
         * @param {Number} rowNumber
         * @param {Number} rowHeight
         * @param {Boolean} isLastRow
         */
        assignImagesToRow: function (images, rowNumber, rowHeight, isLastRow) {
            var imageWidth;

            images.forEach(function (img) {
                imageWidth = rowHeight * (img.width / img.height).toFixed(2);
                this.setImageStyles(img, imageWidth, rowHeight);
                this.setImageClass(img, {
                    bottom: isLastRow
                });
                img.rowNumber = rowNumber;
            }.bind(this));

            images[0].firstInRow = true;
            images[images.length - 1].lastInRow = true;
        },

        /**
         * Wait for container to initialize
         */
        waitForContainer: function (callback) {
            if (typeof this.container === 'undefined') {
                setTimeout(function () {
                    this.waitForContainer(callback);
                }.bind(this), 500);
            } else {
                callback();
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
            if (!img.styles) {
                img.styles = ko.observable();
            }
            img.styles({
                width: parseInt(width, 10) + 'px',
                height: parseInt(height, 10) + 'px'
            });
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
            var minRatio = null;

            for (var width in this.containerWidthToMinRatio) {
                if (this.containerWidthToMinRatio.hasOwnProperty(width) &&
                    this.containerWidth <= width
                ) {
                    minRatio = this.containerWidthToMinRatio[width]
                }
            }

            this.minRatio = minRatio ? minRatio : this.defaultMinRatio;
        },

        /**
         * Checks if grid has data.
         *
         * @returns {Boolean}
         */
        hasData: function () {
            return !!this.rows() && !!this.rows().length;
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
