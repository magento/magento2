/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/dynamic-rows/dynamic-rows'
], function (dynamicRows) {
    'use strict';

    return dynamicRows.extend({
        defaults: {
            dataProvider: '',
            insertData: [],
            listens: {
                'insertData': 'processingInsertData'
            }
        },

        /**
         * Calls 'initObservable' of parent
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe([
                    'insertData'
                ]);

            return this;
        },

        /**
         * Parsed data
         *
         * @param {Array} data - array with data
         * about selected records
         */
        processingInsertData: function (data) {
            if (!data.length) {
                return false;
            }

            data.each(function (options) {
                options.options.each(function (option) {
                    var path = this.dataScope + '.' + this.index + '.' + this.recordIterator;

                    this.source.set(path, option);
                    this.addChild(option, false);
                }, this);
            }, this);
        },

        /**
         * Set empty array to dataProvider
         */
        clearDataProvider: function () {
            this.source.set(this.dataProvider, []);
        }
    });
});
