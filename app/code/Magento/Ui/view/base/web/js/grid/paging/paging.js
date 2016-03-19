/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'underscore',
    'mageUtils',
    'uiLayout',
    'uiElement'
], function (ko, _, utils, layout, Element) {
    'use strict';

    return Element.extend({
        defaults: {
            template: 'ui/grid/paging/paging',
            totalTmpl: 'ui/grid/paging-total',
            pageSize: 20,
            current: 1,
            selectProvider: '',

            sizesConfig: {
                component: 'Magento_Ui/js/grid/paging/sizes',
                name: '${ $.name }_sizes',
                storageConfig: {
                    provider: '${ $.storageConfig.provider }',
                    namespace: '${ $.storageConfig.namespace }'
                }
            },

            imports: {
                pageSize: '${ $.sizesConfig.name }:value',
                totalSelected: '${ $.selectProvider }:totalSelected',
                totalRecords: '${ $.provider }:data.totalRecords'
            },

            exports: {
                pageSize: '${ $.provider }:params.paging.pageSize',
                current: '${ $.provider }:params.paging.current'
            },

            listens: {
                'pages': 'onPagesChange',
                'pageSize': 'onPageSizeChange',
                'totalRecords': 'updateCounter',
                '${ $.provider }:params.filters': 'goFirst'
            },

            modules: {
                sizes: '${ $.sizesConfig.name }'
            }
        },

        /**
         * Initializes paging component.
         *
         * @returns {Paging} Chainable.
         */
        initialize: function () {
            this._super()
                .initSizes()
                .updateCounter();

            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Paging} Chainable.
         */
        initObservable: function () {
            this._super()
                .track([
                    'totalSelected',
                    'totalRecords',
                    'pageSize',
                    'pages',
                    'current'
                ]);

            this._current = ko.pureComputed({
                read: ko.getObservable(this, 'current'),

                /**
                 * Validates page change according to user's input.
                 * Sets current observable to result of validation.
                 * Calls reload method then.
                 */
                write: function (value) {
                    this.setPage(value)
                        ._current.notifySubscribers(this.current);
                },

                owner: this
            });

            return this;
        },

        /**
         * Initializes sizes component.
         *
         * @returns {Paging} Chainable.
         */
        initSizes: function () {
            layout([this.sizesConfig]);

            return this;
        },

        /**
         * Sets cursor to the provied value.
         *
         * @param {(Number|String)} value - New value of the cursor.
         * @returns {Paging} Chainable.
         */
        setPage: function (value) {
            this.current = this.normalize(value);

            return this;
        },

        /**
         * Increments current page value.
         *
         * @returns {Paging} Chainable.
         */
        next: function () {
            this.setPage(this.current + 1);

            return this;
        },

        /**
         * Decrements current page value.
         *
         * @returns {Paging} Chainable.
         */
        prev: function () {
            this.setPage(this.current - 1);

            return this;
        },

        /**
         * Goes to the first page.
         *
         * @returns {Paging} Chainable.
         */
        goFirst: function () {
            this.current = 1;

            return this;
        },

        /**
         * Goes to the last page.
         *
         * @returns {Paging} Chainable.
         */
        goLast: function () {
            this.current = this.pages;

            return this;
        },

        /**
         * Checks if current page is the first one.
         *
         * @returns {Boolean}
         */
        isFirst: function () {
            return this.current === 1;
        },

        /**
         * Checks if current page is the last one.
         *
         * @returns {Boolean}
         */
        isLast: function () {
            return this.current === this.pages;
        },

        /**
         * Updates number of pages.
         */
        updateCounter: function () {
            this.pages = Math.ceil(this.totalRecords / this.pageSize) || 1;

            return this;
        },

        /**
         * Calculates new page cursor based on the
         * previous and current page size values.
         *
         * @returns {Number} Updated cursor value.
         */
        updateCursor: function () {
            var cursor  = this.current - 1,
                size    = this.pageSize,
                oldSize = this.previousSize,
                delta   = cursor * (oldSize  - size) / size;

            delta = size > oldSize ?
                Math.ceil(delta) :
                Math.floor(delta);

            cursor += delta + 1;

            this.previousSize = size;

            this.setPage(cursor);

            return this;
        },

        /**
         * Converts provided value to a number and puts
         * it in range between 1 and total amount of pages.
         *
         * @param {(Number|String)} value - Value to be normalized.
         * @returns {Number}
         */
        normalize: function (value) {
            value = +value;

            if (isNaN(value)) {
                return 1;
            }

            return utils.inRange(Math.round(value), 1, this.pages);
        },

        /**
         * Handles changes of the page size.
         */
        onPageSizeChange: function () {
            this.updateCounter()
                .updateCursor();
        },

        /**
         * Handles changes of the pages amount.
         */
        onPagesChange: function () {
            this.updateCursor();
        }
    });
});
