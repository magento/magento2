/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'uiComponent'
], function (ko, Component) {
    'use strict';

    /**
     * Returns closest existing page number to page argument
     * @param {Number} value
     * @param {Number} max
     * @returns {Number} closest existing page number
     */
    function getInRange(value, max) {
        return Math.min(Math.max(1, value), max);
    }

    return Component.extend({
        defaults: {
            template: 'ui/grid/paging',
            pageSize: 20,
            current: 1,

            imports: {
                totalSelected: '${ $.provider }:config.multiselect.total',
                totalRecords: '${ $.provider }:data.totalRecords'
            },

            exports: {
                pageSize: '${ $.provider }:params.paging.pageSize',
                current: '${ $.provider }:params.paging.current',
                pages: '${ $.provider }:data.pages'
            },

            links: {
                pageSize: '${ $.storageConfig.path }.pageSize'
            },

            listens: {
                'pageSize': 'onSizeChange',
                'pageSize totalRecords': 'countPages'
            }
        },

        initialize: function () {
            this._super()
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
                    'pages'
                ]);

            this._current = ko.pureComputed({
                read: function () {
                    return +this.current();
                },

                /**
                 * Validates page change according to user's input.
                 * Sets current observable to result of validation.
                 * Calls reload method then.
                 */
                write: function (value) {
                    var valid;

                    value = +value;
                    valid = !isNaN(value) ? getInRange(value, this.pages()) : 1;

                    this.current(valid);
                    this._current.notifySubscribers(value);
                },

                owner: this
            });

            return this;
        },

        /**
         * Increments current observable prop by val and call reload method
         * @param {String} val
         */
        go: function (val) {
            var current = this.current;

            current(current() + val);
        },

        /**
         * Calls go method with 1 as agrument
         */
        next: function () {
            this.go(1);
        },

        /**
         * Calls go method with -1 as agrument
         */
        prev: function () {
            this.go(-1);
        },

        /**
         * Compares current and pages observables and returns boolean result
         *
         * @returns {Boolean} is current equal to pages property
         */
        isLast: function () {
            return this.current() === this.pages();
        },

        /**
         * Compares current observable to 1.
         *
         * @returns {Boolean} is current page first
         */
        isFirst: function () {
            return this.current() === 1;
        },

        /**
         * Calculates number of pages.
         */
        countPages: function () {
            var pages = Math.ceil(this.totalRecords() / this.pageSize());

            this.pages(pages || 1);
        },

        /**
         * Is being triggered on user interaction with page size select.
         * Resets current page to first if needed.
         */
        onSizeChange: function (size) {
            if (size * this.current() > this.totalRecords()) {
                this.current(1);
            }
        }
    });
});
