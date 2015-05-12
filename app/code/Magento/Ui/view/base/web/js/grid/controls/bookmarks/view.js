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
            active: false,
            editable: true,
            editing: false,
            changed: false,
            isNew: false,
            saved: {},
            exports: {
                active: 'onActivate'
            },
            listens: {
                label: 'setLabel',
                '${ $.parentName }:activeIndex': 'onActiveChange'
            },
            modules: {
                states: '${ $.parentName }'
            }
        },

        initialize: function () {
            this._super();

            this.data = {
                label: this.label(),
                items: this.data || {}
            };

            !this.isNew ?
                this.save() :
                this.changed(true);
        },

        /**
         * Creates observable properties.
         *
         * @returns {View} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('active label editing changed');

            return this;
        },

        /**
         * Retrieves saved data.
         *
         * @returns {Object}
         */
        getSaved: function () {
            return utils.copy(this.saved.items);
        },

        /**
         * Retrieves current data.
         *
         * @returns {Object}
         */
        getData: function () {
            return utils.copy(this.data.items);
        },

        /**
         * Replaces current data with a provided one.
         * Performs dirty checking.
         *
         * @param {Object} data - New data object.
         * @returns {View} Chainable.
         */
        setData: function (data) {
            if (this.editable) {
                this.data.items = utils.copy(data);

                this.checkChanges();
            }

            return this;
        },

        /**
         * Sets new label.
         * Performs dirty checking.
         *
         * @param {String} label - New label value.
         * @returns {View} Chainable.
         */
        setLabel: function (label) {
            label = label.trim() || this.data.label;

            this.data.label = label;
            this.label(label);

            this.checkChanges();

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
         * Sets current data to a saved state.
         *
         * @returns {Object} Current data.
         */
        save: function () {
            this.saved = utils.copy(this.data);
            this.isNew = false;

            this.changed(false);

            return this.saved.items;
        },

        /**
         * Checks if current data is different from its saved state.
         *
         * @returns {Boolean} Whether data has been changed.
         */
        checkChanges: function () {
            var diff = utils.compare(this.saved, this.data),
                changed = !diff.equal;

            this.changed(changed);

            return changed;
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
