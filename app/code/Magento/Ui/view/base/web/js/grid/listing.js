/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'uiComponent',
    'Magento_Ui/js/lib/spinner',
    'Magento_Ui/js/core/renderer/layout'
], function (_, Component, loader, layout) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'ui/grid/listing',
            positions: false,
            storageConfig: {
                positions: '${ $.storageConfig.path }.positions'
            },
            dndConfig: {
                name: '${ $.name }_dnd',
                component: 'Magento_Ui/js/grid/dnd',
                containerTmpl: 'ui/grid/dnd/listing',
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
                dnd: '${ $.dndConfig.name }'
            }
        },

        /**
         * Initializes Listing component.
         *
         * @returns {Listing} Chainable.
         */
        initialize: function () {
            this._super();

            if (this.dndConfig.enabled) {
                this.initDnd();
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
                .observe('rows');

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
