/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'underscore',
    'mageUtils',
    'uiLayout',
    'uiComponent'
], function (ko, _, utils, layout, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'ui/grid/paging/paging',
            totalTmpl: 'ui/grid/paging-total',
            pageSize: 20,
            current: 1,
            selectProvider: '',
            componentType: 'paging',

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
                current: '${ $.provider }:params.paging.current',
                pages: '${ $.provider }:data.pages'
            },

            listens: {
                'pages': 'onPagesChange',
                'pageSize totalRecords': 'countPages',
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
                .countPages();

            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Paging} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe([
                    'totalSelected',
                    'totalRecords',
                    'pageSize',
                    'current',
                    'pages',
                    'options'
                ]);

            this._current = ko.pureComputed({
                read: this.current,

                /**
                 * Validates page change according to user's input.
                 * Sets current observable to result of validation.
                 * Calls reload method then.
                 */
                write: function (value) {
                    value = this.normalize(value);

                    this.current(value);
                    this._current.notifySubscribers(value);
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
            this.current(this.normalize(value));

            return this;
        },

        /**
         * Increments current page value.
         *
         * @returns {Paging} Chainable.
         */
        next: function () {
            this.setPage(this.current() + 1);

            return this;
        },

        /**
         * Decrements current page value.
         *
         * @returns {Paging} Chainable.
         */
        prev: function () {
            this.setPage(this.current() - 1);

            return this;
        },

        /**
         * Goes to the first page.
         *
         * @returns {Paging} Chainable.
         */
        goFirst: function () {
            this.current(1);

            return this;
        },

        /**
         * Goes to the last page.
         *
         * @returns {Paging} Chainable.
         */
        goLast: function () {
            this.current(this.pages());

            return this;
        },

        /**
         * Checks if current page is the first one.
         *
         * @returns {Boolean}
         */
        isFirst: function () {
            return this.current() === 1;
        },

        /**
         * Checks if current page is the last one.
         *
         * @returns {Boolean}
         */
        isLast: function () {
            return this.current() === this.pages();
        },

        /**
         * Converts provided value to a number and puts
         * it in range between 1 and total amount of pages.
         *
         * @param {(Number|String)} value - Value to be normalized.
         * @returns {Number}
         */
        normalize: function (value) {
            var total = this.pages();

            value = +value;

            if (isNaN(value)) {
                return 1;
            }

            return utils.inRange(Math.round(value), 1, total);
        },

        /**
         * Calculates number of pages.
         */
        countPages: function () {
            var pages = Math.ceil(this.totalRecords() / this.pageSize());

            this.pages(pages || 1);
        },

        /**
         * Listens changes of the 'pages' property.
         * Might change current page if its' value
         * is greater than total amount of pages.
         *
         * @param {Number} pages - Total amount of pages.
         */
        onPagesChange: function (pages) {
            var current = this.current;

            current(utils.inRange(current(), 1, pages));
        }
    });
});
