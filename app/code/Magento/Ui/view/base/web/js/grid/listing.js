/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'underscore',
    'Magento_Ui/js/lib/spinner',
    'uiLayout',
    'uiCollection'
], function (ko, _, loader, layout, Collection) {
    'use strict';

    return Collection.extend({
        defaults: {
            template: 'ui/grid/listing',
            stickyTmpl: 'ui/grid/sticky/listing',
            positions: false,
            dndConfig: {
                name: '${ $.name }_dnd',
                component: 'Magento_Ui/js/grid/dnd',
                columnsProvider: '${ $.name }',
                enabled: true
            },
            editorConfig: {
                name: '${ $.name }_editor',
                component: 'Magento_Ui/js/grid/editing/editor',
                columnsProvider: '${ $.name }',
                dataProvider: '${ $.provider }',
                enabled: false
            },
            resizeConfig: {
                name: '${ $.name }_resize',
                columnsProvider: '${ $.name }',
                component: 'Magento_Ui/js/grid/resize',
                enabled: false
            },
            imports: {
                rows: '${ $.provider }:data.items'
            },
            listens: {
                elems: 'updatePositions updateVisible',
                '${ $.provider }:reload': 'showLoader',
                '${ $.provider }:reloaded': 'hideLoader'
            },
            modules: {
                dnd: '${ $.dndConfig.name }',
                resize: '${ $.resizeConfig.name }'
            }
        },

        /**
         * Initializes Listing component.
         *
         * @returns {Listing} Chainable.
         */
        initialize: function () {
            _.bindAll(this, 'updateVisible');

            this._super()
                .initDnd()
                .initEditor()
                .initResize();

            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Listing} Chainable.
         */
        initObservable: function () {
            this._super()
                .track({
                    rows: [],
                    visibleColumns: []
                });

            return this;
        },

        /**
         * Creates drag&drop widget instance.
         *
         * @returns {Listing} Chainable.
         */
        initDnd: function () {
            if (this.dndConfig.enabled) {
                layout([this.dndConfig]);
            }

            return this;
        },

        /**
         * Inititalizes resize component.
         *
         * @returns {Listing} Chainable.
         */
        initResize: function () {
            if (this.resizeConfig.enabled) {
                layout([this.resizeConfig]);
            }

            return this;
        },

        /**
         * Creates inline editing component.
         *
         * @returns {Listing} Chainable.
         */
        initEditor: function () {
            if (this.editorConfig.enabled) {
                layout([this.editorConfig]);
            }

            return this;
        },

        /**
         * Called when another element was added to current component.
         *
         * @returns {Listing} Chainable.
         */
        initElement: function (element) {
            var currentCount = this.elems().length,
                totalCount = this.initChildCount;

            if (totalCount === currentCount) {
                this.initPositions();
            }

            element.on('visible', this.updateVisible);

            return this._super();
        },

        /**
         * Defines initial order of child elements.
         *
         * @returns {Listing} Chainable.
         */
        initPositions: function () {
            this.on('positions', this.applyPositions.bind(this));

            this.setStatefull('positions');

            return this;
        },

        /**
         * Updates current state of child positions.
         *
         * @returns {Listing} Chainable.
         */
        updatePositions: function () {
            var positions = {};

            this.elems.each(function (elem, index) {
                positions[elem.index] = index;
            });

            this.set('positions', positions);

            return this;
        },

        /**
         * Reseorts child elements array according to provided positions.
         *
         * @param {Object} positions - Object where key represents child
         *      index and value is its' position.
         * @returns {Listing} Chainable.
         */
        applyPositions: function (positions) {
            var sorting;

            sorting = this.elems.map(function (elem) {
                return {
                    elem: elem,
                    position: positions[elem.index]
                };
            });

            this.insertChild(sorting);

            return this;
        },

        /**
         * Returns reference to 'visibleColumns' array.
         *
         * @returns {Array}
         */
        getVisible: function () {
            var observable = ko.getObservable(this, 'visibleColumns');

            return observable || this.visibleColumns;
        },

        /**
         * Returns total number of displayed columns in grid.
         *
         * @returns {Number}
         */
        countVisible: function () {
            return this.visibleColumns.length;
        },

        /**
         * Updates array of visible columns.
         *
         * @returns {Listing} Chainable.
         */
        updateVisible: function () {
            this.visibleColumns = this.elems.filter('visible');

            return this;
        },

        /**
         * Checks if grid has data.
         *
         * @returns {Boolean}
         */
        hasData: function () {
            return !!this.rows.length;
        },

        /**
         * Hides loader.
         */
        hideLoader: function () {
            loader.get(this.name).hide();
        },

        /**
         * Shows loader.
         */
        showLoader: function () {
            loader.get(this.name).show();
        }
    });
});
