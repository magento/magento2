/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/lib/spinner',
    'uiLayout',
    'uiComponent'
], function (_, loader, layout, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'ui/grid/listing',
            stickyTmpl: 'ui/grid/sticky/listing',
            positions: false,
            storageConfig: {
                positions: '${ $.storageConfig.path }.positions'
            },
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
                provider: '${ $.provider }',
                classResize: 'shadow-div',
                divsAttrParams: {
                    'data-cl-elem': 'shadow-div'
                },
                enabled: true
            },
            imports: {
                rows: '${ $.provider }:data.items'
            },
            listens: {
                elems: 'setPositions',
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
            this._super();

            if (this.resizeConfig.enabled) {
                this.initResize();
            }

            if (this.dndConfig.enabled) {
                this.initDnd();
            }

            if (this.editorConfig.enabled) {
                this.initEditor();
            }

            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Listing} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe({
                    rows: []
                });

            return this;
        },

        /**
         * Creates drag&drop widget instance.
         *
         * @returns {Listing} Chainable.
         */
        initDnd: function () {
            layout([this.dndConfig]);

            return this;
        },

        /**
         * Creates resize widget instance.
         *
         * @returns {Listing} Chainable.
         */
        initResize: function () {
            layout([this.resizeConfig]);

            return this;
        },

        /**
         * Creates inline editing component.
         *
         * @returns {Listing} Chainable.
         */
        initEditor: function () {
            layout([this.editorConfig]);

            return this;
        },

        /**
         * Called when another element was added to current component.
         *
         * @returns {Listing} Chainable.
         */
        initElement: function () {
            var currentCount = this.elems().length,
                totalCount = this.initChildCount;

            if (totalCount === currentCount) {
                this.initPositions();
            }

            return this._super();
        },

        /**
         * Defines initial order of child elements.
         *
         * @returns {Listing} Chainable.
         */
        initPositions: function () {
            var link = {
                positions: this.storageConfig.positions
            };

            this.on('positions', this.applyPositions.bind(this));

            this.setLinks(link, 'imports')
                .setLinks(link, 'exports');

            return this;
        },

        /**
         * Updates current state of child positions.
         *
         * @returns {Listing} Chainable.
         */
        setPositions: function () {
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
         * Returns instance of a column found by provided identifier.
         *
         * @param {String} index - Columns' identifier.
         * @returns {Column}
         */
        getColumn: function (index) {
            return this.elems.findWhere({
                index: index
            });
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
        },

        /**
         * Returns total number of displayed columns in grid.
         *
         * @returns {Number}
         */
        countVisible: function () {
            return this.elems.filter('visible').length;
        },

        /**
         * Checks if grid has data.
         *
         * @returns {Boolean}
         */
        hasData: function () {
            return !!this.rows().length;
        }
    });
});
