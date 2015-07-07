/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'underscore',
    'mageUtils',
    'uiRegistry'
], function (ko, _, utils, registry) {
    'use strict';

    /**
     * Removes non plain object items from the specfied array.
     *
     * @param {Array} container - Array whose value should be filtered.
     * @returns {Array}
     */
    function compact(container) {
        return container.filter(utils.isObject);
    }

    return {
        /**
         * Retrieves requested region.
         * Creates region if it was not created yet
         *
         * @returns {ObservableArray}.
         */
        getRegion: function (name) {
            var regions = this.regions = this.regions || {};

            if (!regions[name]) {
                regions[name] = ko.observable([]);
            }

            return regions[name];
        },

        /**
         * Replaces specified regions' data with a provided one.
         * Creates region if it was not created yet.
         *
         * @param {Array} items - New regions' data.
         * @param {String} name - Name of the region.
         * @returns {Component} Chainable.
         */
        updateRegion: function (items, name) {
            var region = this.getRegion(name);

            region(items);

            return this;
        },

        /**
         * Requests specified components to insert
         * them into 'elems' array starting from provided position.
         *
         * @param {(String|Array)} elems - Name of the component to insert.
         * @param {Number} [position=-1] - Position at which to insert elements.
         * @returns {Component} Chainable.
         */
        insertChild: function (elems, position) {
            var container = this._elems,
                update;

            if (!Array.isArray(elems)) {
                elems = [elems];
            }

            elems.map(function (item) {
                return item.elem ?
                    utils.insert(item.elem, container, item.position) :
                    utils.insert(item, container, position);
            }).forEach(function (item) {
                if (item === true) {
                    update = true;
                } else if (_.isString(item)) {
                    registry.get(item, this._insert);
                } else if (utils.isObject(item)) {
                    this._insert(item);
                }
            }, this);

            if (update) {
                this._update();
            }

            return this;
        },

        /**
         * Removes specified element from the 'elems' array.
         *
         * @param {Object} elem - Element to be removed.
         * @returns {Component} Chainable.
         */
        removeChild: function (elem) {
            utils.remove(this._elems, elem);
            this._update();

            return this;
        },

        /**
         * Destroys current instance along with all of its' children.
         */
        destroy: function () {
            this._dropHandlers()
                ._clearData()
                ._clearRefs();
        },

        /**
         * Removes events listeners.
         * @private
         *
         * @returns {Component} Chainable.
         */
        _dropHandlers: function () {
            this.off();

            this.source.off(this.name);

            return this;
        },

        /**
         * Clears all data associated with component.
         * @private
         *
         * @returns {Component} Chainable.
         */
        _clearData: function () {
            this.source.remove(this.dataScope);
            this.source.remove('params.' + this.name);

            return this;
        },

        /**
         * Removes all references to current instance and
         * calls 'destroy' method on all of its' children.
         * @private
         *
         * @returns {Component} Chainable.
         */
        _clearRefs: function () {
            registry.remove(this.name);

            this.containers.forEach(function (parent) {
                parent.removeChild(this);
            }, this);

            this.elems.each('destroy');

            return this;
        },

        /**
         * Inserts provided component into 'elems' array at a specified position.
         * @private
         *
         * @param {Object} elem - Element to insert.
         */
        _insert: function (elem) {
            var index = this._elems.indexOf(elem.name);

            if (!~index) {
                return;
            }

            this._elems[index] = elem;

            this._update()
                .initElement(elem);
        },

        /**
         * Synchronizes multiple elements arrays with a core '_elems' container.
         * Performs elemets grouping by theirs 'displayArea' property.
         * @private
         *
         * @returns {Component} Chainable.
         */
        _update: function () {
            var _elems = compact(this._elems),
                grouped = _.groupBy(_elems, 'displayArea');

            _.each(grouped, this.updateRegion, this);

            this.elems(_elems);

            return this;
        }
    };
});
