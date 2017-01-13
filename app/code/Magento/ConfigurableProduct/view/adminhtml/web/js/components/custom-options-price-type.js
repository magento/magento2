/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/form/element/select'
], function (_, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            isConfigurable: false,
            isFiltered: null,
            defaultOptions: null,
            filteredOptions: null,
            bannedOptions: []
        },

        /**
         * Updates options.
         *
         * @param {Boolean} variationsEmpty
         * @returns {Boolean}
         */
        updateOptions: function (variationsEmpty) {
            var isFiltered = this.isConfigurable || !variationsEmpty,
                value;

            if (this.isFiltered !== isFiltered) {
                value = this.value();

                this.options(isFiltered ? this.getFilteredOptions() : this.getDefaultOptions());
                this.value(value);
            }

            return isFiltered;
        },

        /**
         * Get default list of options.
         *
         * @returns {Array}
         */
        getDefaultOptions: function () {
            if (this.defaultOptions === null) {
                this.defaultOptions = this.options();
            }

            return this.defaultOptions;
        },

        /**
         * Get filtered list of options.
         *
         * @returns {Array}
         */
        getFilteredOptions: function () {
            var defaultOptions;

            if (this.filteredOptions === null) {
                defaultOptions = this.getDefaultOptions();
                this.filteredOptions = [];

                _.each(defaultOptions, function (option) {
                    if (this.bannedOptions.indexOf(option.value) === -1) {
                        this.filteredOptions.push(option);
                    }
                }, this);
            }

            return this.filteredOptions;
        }
    });
});
