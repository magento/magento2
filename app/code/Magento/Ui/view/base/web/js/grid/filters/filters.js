/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'uiLayout',
    'Magento_Ui/js/lib/collapsible'
], function (_, utils, layout, Collapsible) {
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

    return Collapsible.extend({
        defaults: {
            template: 'ui/grid/filters/filters',
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
                        dataScope: 'filters.${ $.$data.column.index }',
                        label: '${ $.$data.column.label }',
                        imports: {
                            visible: '${ $.$data.column.name }:visible'
                        }
                    },
                    text: {
                        component: 'Magento_Ui/js/form/element/abstract',
                        template: 'ui/grid/filters/elements/input'
                    },
                    select: {
                        component: 'Magento_Ui/js/form/element/ui-select',
                        template: 'ui/grid/filters/elements/ui-select',
                        options: '${ JSON.stringify($.$data.column.options) }'
                    },
                    dateRange: {
                        component: 'Magento_Ui/js/grid/filters/group',
                        childDefaults: {
                            component: 'Magento_Ui/js/form/element/date',
                            provider: '${ $.parent }.${ $.name }',
                            dateFormat: 'MM/dd/YYYY',
                            template: 'ui/grid/filters/elements/date'
                        },
                        children: {
                            from: {
                                label: 'from',
                                dataScope: 'from'
                            },
                            to: {
                                label: 'to',
                                dataScope: 'to'
                            }
                        }
                    },
                    textRange: {
                        component: 'Magento_Ui/js/grid/filters/group',
                        childDefaults: {
                            component: 'Magento_Ui/js/form/element/abstract',
                            provider: '${ $.parent }.${ $.name }',
                            template: 'ui/grid/filters/elements/input'
                        },
                        children: {
                            from: {
                                label: 'from',
                                dataScope: 'from'
                            },
                            to: {
                                label: 'to',
                                dataScope: 'to'
                            }
                        }
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
                applied: 'cancel extractActive'
            },
            links: {
                applied: '${ $.storageConfig.path }'
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
            this._super()
                .initChips()
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
         *
         * @param {Object} filter
         * @returns {Filters} Chainable.
         */
        initFilter: function (filter) {
            layout([filter]);

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
         *
         * @param {String} index
         * @returns {Filter|Undefined}
         */
        getFilter: function (index) {
            return this.elems.findWhere({
                index: index
            });
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
         *
         * @param {Column} column
         * @returns {Object}
         */
        buildFilter: function (column) {
            var filters = this.templates.filters,
                filter  = column.filter;

            if (_.isObject(filter) && filter.filterType) {
                filter = utils.extend({}, filters[filter.filterType], filter);
            } else if (_.isString(filter)) {
                filter = filters[filter];
            }

            filter = utils.extend({}, filters.base, filter);
            filter = utils.template(filter, {
                filters: this,
                column: column
            }, true, true);

            return filter;
        },

        /**
         *
         * @param {Column} column
         * @returns {Filters} Chainable
         */
        createFilter: function (column) {
            var index = column.index,
                filter;

            if (!column.filter || this.getFilter(index)) {
                return this;
            }

            filter = this.buildFilter(column);

            this.initFilter(filter);

            return this;
        },

        /**
         *
         * @returns {Filters} Chainable
         */
        updateFilters: function () {
            var columns = this.columns().elems(),
                filters = [],
                filter;

            columns.forEach(function (column) {
                filter = this.getFilter(column.index);

                if (filter) {
                    filters.push(filter);
                }
            }, this);

            this.insertChild(filters);

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
         * Extract previews of a specified filters.
         *
         * @param {Array} filters - Filters to be processed.
         * @returns {Filters} Chainable.
         */
        updatePreviews: function (filters) {
            var previews = filters.map(extractPreview);

            this.previews(_.compact(previews));

            return this;
        },

        /**
         *
         */
        onColumnsUpdate: function (columns) {
            columns.forEach(this.createFilter, this);

            this.updateFilters();
        }
    });
});
