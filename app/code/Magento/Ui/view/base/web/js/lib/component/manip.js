/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'uiRegistry'
], function (_, utils, registry) {
    'use strict';

    function getIndex(container, target) {
        var result;

        container.some(function (item, index) {
            result = index;

            return item && (item.name === target || item === target);
        });

        return result;
    }

    function compact(container) {
        return container.filter(utils.isObject);
    }

    function reserve(container, elem, position) {
        var offset = position,
            target;

        if (_.isObject(position)) {
            target = position.after || position.before;
            offset = getIndex(container, target);

            if (position.after) {
                offset++;
            }
        }

        offset = utils.formatOffset(container, offset);

        container[offset] ?
            container.splice(offset, 0, elem) :
            container[offset] = elem;

        return offset;
    }

    return {
        /**
         * Requests specified components to insert
         * them into 'elems' array starting from provided position.
         *
         * @param {String} elem - Name of the component to insert.
         * @param {Number} [position=-1] - Position at which to insert elements.
         * @returns {Component} Chainable.
         */
        insert: function (elem, position) {
            reserve(this._elems, elem, position);

            registry.get(elem, this._insert);

            return this;
        },

        /**
         * Removes specified element from the 'elems' array.
         *
         * @param {Object} elem - Element to be removed.
         * @returns {Component} Chainable.
         */
        remove: function (elem) {
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
            var layout = this.renderer.layout;

            this.source.remove('data.' + this.dataScope);
            this.source.remove('params.' + this.name);

            layout.clear(this.name);

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
                parent.remove(this);
            }, this);

            this.elems().forEach(function (child) {
                child.destroy();
            });

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
                grouped = _.groupBy(_elems, 'displayArea'),
                group;

            this.regions.forEach(function (region) {
                group = grouped[region];

                if (group) {
                    this[region](group);
                }
            }, this);

            this.elems(_elems);

            return this;
        }
    };
});
