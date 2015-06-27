/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'mageUtils'
], function (Component, utils) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'ui/grid/controls/bookmarks/view',
            value: '${ $.isNew ? "" : $.label }',
            active: false,
            editable: true,
            editing: false,
            isNew: false,
            statesProvider: '${ $.parentName }',
            exports: {
                active: 'onActivate'
            },
            listens: {
                'editing value': 'syncLabel',
                '${ $.statesProvider }:activeIndex': 'onActiveChange'
            },
            modules: {
                states: '${ $.statesProvider }'
            }
        },

        /**
         * Initializes view component.
         *
         * @returns {View} Chainable.
         */
        initialize: function () {
            this._super();

            this.data = {
                label: this.label(),
                items: this.data || {}
            };

            return this;
        },

        /**
         * Creates observable properties.
         *
         * @returns {View} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('active label value editing');

            return this;
        },

        /**
         * Retrieves copied views' data.
         *
         * @param {String} [path] - Path to the specific property.
         * @returns {*}
         */
        getData: function (path) {
            var data = this.data.items;

            if (path) {
                data = utils.nested(data, path);
            }

            return utils.copy(data);
        },

        /**
         * Replaces current data with a provided one.
         *
         * @param {Object} data - New data object.
         * @returns {View} Chainable.
         */
        setData: function (data) {
            if (this.editable) {
                this.set('data.items', utils.copy(data));
            }

            return this;
        },

        /**
         * Sets new label.
         *
         * @returns {View} Chainable.
         */
        syncLabel: function () {
            var label = this.value();

            label = label.trim() || this.data.label;

            this.label(label);
            this.value(label);

            this.data.label = label;

            return this;
        },

        /**
         * Sets 'editing' flag to true.
         *
         * @returns {View} Chainable.
         */
        startEdit: function () {
            this.editing(true);

            return this;
        },

        /**
         * Sets 'editing' flag to false.
         *
         * @returns {View} Chainable.
         */
        endEdit: function () {
            this.editing(false);

            return this;
        },

        /**
         * Returns views' data including 'label' and 'index' properties.
         *
         * @returns {Object}
         */
        exportView: function () {
            return {
                index: this.index,
                label: this.label(),
                data: this.data.items
            };
        },

        /**
         * Listener of the 'active' property.
         */
        onActivate: function (active) {
            if (active) {
                this.states('set', 'activeIndex', this.index);
            }
        },

        /**
         * Listener of the collections' active index value.
         */
        onActiveChange: function (index) {
            if (index !== this.index) {
                this.active(false);
            }
        }
    });
});
