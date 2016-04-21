/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'uiLayout',
    'uiCollection'
], function (_, utils, layout, Collection) {
    'use strict';

    /**
     * Extracts and formats preview of an element.
     *
     * @param {Object} elem - Element whose preview should be extracted.
     * @returns {Object} Formatted data.
     */
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
        return utils.mapRecursive(data, utils.removeEmptyValues.bind(utils));
    }

    return Collection.extend({
        defaults: {
            template: 'ui/grid/filters/filters',
            stickyTmpl: 'ui/grid/sticky/filters',
            _processed: [],
            columnsProvider: 'ns = ${ $.ns }, componentType = columns',
            applied: {
                placeholder: true
            },
            filters: {
                placeholder: true
            },
            templates: {
                filters: {
                    base: {
                        parent: '${ $.$data.filters.name }',
                        name: '${ $.$data.column.index }',
                        provider: '${ $.$data.filters.name }',
                        dataScope: '${ $.$data.column.index }',
                        label: '${ $.$data.column.label }',
                        imports: {
                            visible: '${ $.$data.column.name }:visible'
                        }
                    },
                    text: {
                        component: 'Magento_Ui/js/form/element/abstract',
                        template: 'ui/grid/filters/field'
                    },
                    select: {
                        component: 'Magento_Ui/js/form/element/select',
                        template: 'ui/grid/filters/field',
                        options: '${ JSON.stringify($.$data.column.options) }',
                        caption: ' '
                    },
                    dateRange: {
                        component: 'Magento_Ui/js/grid/filters/range',
                        rangeType: 'date'
                    },
                    textRange: {
                        component: 'Magento_Ui/js/grid/filters/range',
                        rangeType: 'text'
                    }
                }
            },
            chipsConfig: {
                name: '${ $.name }_chips',
                provider: '${ $.chipsConfig.name }',
                component: 'Magento_Ui/js/grid/filters/chips'
            },
            listens: {
                active: 'updatePreviews',
                applied: 'cancel updateActive'
            },
            statefull: {
                applied: true
            },
            exports: {
                applied: '${ $.provider }:params.filters'
            },
            imports: {
                'onColumnsUpdate': '${ $.columnsProvider }:elems'
            },
            modules: {
                columns: '${ $.columnsProvider }',
                chips: '${ $.chipsConfig.provider }'
            }
        },

        /**
         * Initializes filters component.
         *
         * @returns {Filters} Chainable.
         */
        initialize: function () {
            _.bindAll(this, 'updateActive');

            this._super()
                .initChips()
                .cancel();

            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Filters} Chainable.
         */
        initObservable: function () {
            this._super()
                .track({
                    active: [],
                    previews: []
                });

            return this;
        },

        /**
         * Initializes chips component.
         *
         * @returns {Filters} Chainable.
         */
        initChips: function () {
            layout([this.chipsConfig]);

            this.chips('insertChild', this.name);

            return this;
        },

        /**
         * Called when another element was added to filters collection.
         *
         * @returns {Filters} Chainable.
         */
        initElement: function (elem) {
            this._super();

            elem.on('elems', this.updateActive);

            this.updateActive();

            return this;
        },

        /**
         * Clears filters data.
         *
         * @param {Object} [filter] - If provided, then only specified
         *      filter will be cleared. Otherwise, clears all data.
         * @returns {Filters} Chainable.
         */
        clear: function (filter) {
            filter ?
                filter.clear() :
                _.invoke(this.active, 'clear');

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
         * Sets provided data to filter components (without applying it).
         *
         * @param {Object} data - Filters data.
         * @param {Boolean} [partial=false] - Flag that defines whether
         *      to completely replace current filters data or to extend it.
         * @returns {Filters} Chainable.
         */
        setData: function (data, partial) {
            var filters = partial ? this.filters : {};

            data = utils.extend({}, filters, data);

            this.set('filters', data);

            return this;
        },

        /**
         * Creates instance of a filter associated with the provided column.
         *
         * @param {Column} column - Column component for which to create a filter.
         * @returns {Filters} Chainable.
         */
        addFilter: function (column) {
            var index       = column.index,
                processed   = this._processed,
                filter;

            if (!column.filter || _.contains(processed, index)) {
                return this;
            }

            filter = this.buildFilter(column);

            processed.push(index);

            layout([filter]);

            return this;
        },

        /**
         * Creates filter component configuration associated with the provided column.
         *
         * @param {Column} column - Column component whith a basic filter declaration.
         * @returns {Object} Filters' configuration.
         */
        buildFilter: function (column) {
            var filters = this.templates.filters,
                filter  = column.filter,
                type    = filters[filter.filterType];

            if (_.isObject(filter) && type) {
                filter = utils.extend({}, type, filter);
            } else if (_.isString(filter)) {
                filter = filters[filter];
            }

            filter = utils.extend({}, filters.base, filter);

            return utils.template(filter, {
                filters: this,
                column: column
            }, true, true);
        },

        /**
         * Returns an array of range filters.
         *
         * @returns {Array}
         */
        getRanges: function () {
            return this.elems.filter(function (filter) {
                return filter.isRange;
            });
        },

        /**
         * Returns an array of non-range filters.
         *
         * @returns {Array}
         */
        getPlain: function () {
            return this.elems.filter(function (filter) {
                return !filter.isRange;
            });
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
            return _.contains(this.active, filter);
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
        updateActive: function () {
            var applied = _.keys(this.applied);

            this.active = this.elems.filter(function (elem) {
                return _.contains(applied, elem.index);
            });

            return this;
        },

        /**
         * Returns number of applied filters.
         *
         * @returns {Number}
         */
        countActive: function () {
            return this.active.length;
        },

        /**
         * Extract previews of a specified filters.
         *
         * @param {Array} filters - Filters to be processed.
         * @returns {Filters} Chainable.
         */
        updatePreviews: function (filters) {
            var previews = filters.map(extractPreview);

            this.previews = _.compact(previews);

            return this;
        },

        /**
         * Listener of the columns provider children array changes.
         *
         * @param {Array} columns - Current columns list.
         */
        onColumnsUpdate: function (columns) {
            columns.forEach(this.addFilter, this);
        }
    });
});
