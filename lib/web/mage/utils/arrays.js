/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    './strings'
], function (_, utils) {
    'use strict';

    /**
     * Defines index of an item in a specified container.
     *
     * @param {*} item - Item whose index should be defined.
     * @param {Array} container - Container upon which to perform search.
     * @returns {Number}
     */
    function getIndex(item, container) {
        var index = container.indexOf(item);

        if (~index) {
            return index;
        }

        return _.findIndex(container, function (value) {
            return value && value.name === item;
        });
    }

    return {
        /**
         * Facade method to remove/add value from/to array
         * without creating a new instance.
         *
         * @param {Array} arr - Array to be modified.
         * @param {*} value - Value to add/remove.
         * @param {Boolean} add - Flag that specfies operation.
         * @returns {Utils} Chainable.
         */
        toggle: function (arr, value, add) {
            return add ?
                this.add(arr, value) :
                this.remove(arr, value);
        },

        /**
         * Removes the incoming value from array in case
         * without creating a new instance of it.
         *
         * @param {Array} arr - Array to be modified.
         * @param {*} value - Value to be removed.
         * @returns {Utils} Chainable.
         */
        remove: function (arr, value) {
            var index = arr.indexOf(value);

            if (~index) {
                arr.splice(index, 1);
            }

            return this;
        },

        /**
         * Adds the incoming value to array if
         * it's not alredy present in there.
         *
         * @param {Array} arr - Array to be modifed.
         * params {...*} Values to be added.
         * @returns {Utils} Chainable.
         */
        add: function (arr) {
            var values = _.toArray(arguments).slice(1);

            values.forEach(function (value) {
                if (!~arr.indexOf(value)) {
                    arr.push(value);
                }
            });

            return this;
        },

        /**
         * Inserts specified item into container at a specified position.
         *
         * @param {*} item - Item to be inserted into container.
         * @param {Array} container - Container of items.
         * @param {*} [position=-1] - Position at which item should be inserted.
         *      Position can represent:
         *          - specific index in container
         *          - item which might already be present in container
         *          - structure with one of these properties: after, before
         * @returns {Boolean|*}
         *      - true if element has changed its' position
         *      - false if nothing has changed
         *      - inserted value if it wasn't present in container
         */
        insert: function (item, container, position) {
            var currentIndex = getIndex(item, container),
                newIndex,
                target;

            if (typeof position === 'undefined') {
                position = -1;
            } else if (typeof position === 'string') {
                position = isNaN(+position) ? position : +position;
            }

            newIndex = position;

            if (~currentIndex) {
                target = container.splice(currentIndex, 1)[0];

                if (typeof item === 'string') {
                    item = target;
                }
            }

            if (typeof position !== 'number') {
                target = position.after || position.before || position;

                newIndex = getIndex(target, container);

                if (~newIndex && (position.after || newIndex >= currentIndex)) {
                    newIndex++;
                }
            }

            if (newIndex < 0) {
                newIndex += container.length + 1;
            }

            container[newIndex] ?
                container.splice(newIndex, 0, item) :
                container[newIndex] = item;

            return !~currentIndex ? item : currentIndex !== newIndex;
        },

        /**
         * @param {Array} elems
         * @param {Number} offset
         * @return {Number|*}
         */
        formatOffset: function (elems, offset) {
            if (utils.isEmpty(offset)) {
                offset = -1;
            }

            offset = +offset;

            if (offset < 0) {
                offset += elems.length + 1;
            }

            return offset;
        }
    };
});
