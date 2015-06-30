/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'Magento_Ui/js/core/renderer/layout',
    'Magento_Ui/js/lib/collapsible'
], function (_, utils, layout, Collapsible) {
    'use strict';

    function extractPreview(elem) {
        return {
            label: elem.label,
            preview: elem.getPreview(),
            elem: elem
        };
    }

    /**
     * Removes empty properties from the provided object.
     *
     * @param {Object} data - Object to be processed.
     * @returns {Object}
     */
    function removeEmpty(data) {
        data = utils.flatten(data);
        data = _.omit(data, utils.isEmpty);

        return utils.unflatten(data);
    }

    return Collapsible.extend({
        defaults: {
            template: 'ui/grid/filters/filters',
            applied: {
                placeholder: true
            },
            filters: {
                placeholder: true
            },
            links: {
                applied: '${ $.storageConfig.path }'
            },
            exports: {
                applied: '${ $.provider }:params.filters'
            },
            listens: {
                active: 'updatePreviews',
                applied: 'cancel extractActive'
            }
        },

        /**
         * Initializes filters component.
         *
         * @returns {Filters} Chainable.
         */
        initialize: function () {
            this._processedColumns = {};

            this._super()
                .cancel()
                .extractActive();

            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Filters} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe({
                    active: [],
                    previews: []
                });

            return this;
        },

        /**
         * Called when another element was added to current component.
         *
         * @returns {Filters} Chainable.
         */
        initElement: function () {
            this._super()
                .extractActive();

            return this;
        },

        /**
         * Clears filters data.
         *
         * @param {Object} [filter] - If provided, then only specified filter will be cleared.
         *      Otherwise, clears all data.
         *
         * @returns {Filters} Chainable.
         */
        clear: function (filter) {
            filter ?
                filter.clear() :
                this.active.each('clear');

            this.apply();

            return this;
        },

        /**
         * Sets filters data to the applied state.
         *
         * @returns {Filters} Chainable.
         */
        apply: function () {
            this.set('applied', removeEmpty(this.filters));

            return this;
        },

        /**
         * Resets filters to the last applied state.
         *
         * @returns {Filters} Chainable.
         */
        cancel: function () {
            this.set('filters', utils.copy(this.applied));

            return this;
        },

        /**
         * Tells wether filters pannel should be opened.
         *
         * @returns {Boolean}
         */
        isOpened: function () {
            return this.opened() && this.hasVisible();
        },

        /**
         * Tells wether specified filter should be visible.
         *
         * @param {Object} filter
         * @returns {Boolean}
         */
        isFilterVisible: function (filter) {
            return filter.visible() || this.isFilterActive(filter);
        },

        /**
         * Checks if specified filter is active.
         *
         * @param {Object} filter
         * @returns {Boolean}
         */
        isFilterActive: function (filter) {
            return this.active.contains(filter);
        },

        /**
         * Checks if collection has visible filters.
         *
         * @returns {Boolean}
         */
        hasVisible: function () {
            return this.elems.some(this.isFilterVisible, this);
        },

        /**
         * Finds filters whith a not empty data
         * and sets them to the 'active' filters array.
         *
         * @returns {Filters} Chainable.
         */
        extractActive: function () {
            this.active(this.elems.filter('hasData'));

            return this;
        },

        /**
         * Updates previews of a specified filters.
         *
         * @param {Array} filters - Filters to be processed.
         * @returns {Filters} Chainable.
         */
        updatePreviews: function (filters) {
            var previews = filters.map(extractPreview);

            this.previews(_.compact(previews));

            return this;
        }
    });
});
