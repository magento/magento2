/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'Magento_Ui/js/lib/registry/registry'
], function (_, utils, registry) {
    'use strict';

    function getOffsetFor(elems, offset) {
        if (typeof offset === 'undefined') {
            offset = -1;
        }

        if (offset < 0) {
            offset += elems.length + 1;
        }

        return offset;
    }

    return {
        /**
         * Requests specified components to insert
         * them into 'elems' array starting from provided position.
         *
         * @param {String} elem - Name of the component to insert.
         * @param {Number} [offset=-1] - Position at which to insert elements.
         * @returns {Component} Chainable.
         */
        insert: function (elem, offset) {
            var _elems = this._elems,
                insert = this._insert;

            offset = getOffsetFor(_elems, offset);

            _elems.splice(offset, 0, false);

            registry.get(elem, function (elem) {
                insert(elem, offset);
            });

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
         * @param {Number} index - Position of the element.
         */
        _insert: function (elem, index) {
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
            var _elems = _.compact(this._elems),
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
