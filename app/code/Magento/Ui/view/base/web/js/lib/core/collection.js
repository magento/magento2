/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'underscore',
    'mageUtils',
    'uiRegistry',
    'uiElement'
], function (_, utils, registry, Element) {
    'use strict';

    /**
     * Removes non plain object items from the specified array.
     *
     * @param {Array} container - Array whose value should be filtered.
     * @returns {Array}
     */
    function compact(container) {
        return _.values(container).filter(utils.isObject);
    }

    /**
     * Defines index of an item in a specified container.
     *
     * @param {*} item - Item whose index should be defined.
     * @param {Array} container - Container upon which to perform search.
     * @returns {Number}
     */
    function _findIndex(item, container) {
        var index = _.findKey(container, function (value) {
            return value === item;
        });

        if (typeof index === 'undefined') {
            index = _.findKey(container, function (value) {
                return value && value.name === item;
            });
        }

        return typeof index === 'undefined' ? -1 : index;
    }

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
    function _insertAt(item, container, position) {
        var currentIndex = _findIndex(item, container),
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

            newIndex = _findIndex(target, container);

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
    }

    return Element.extend({
        defaults: {
            template: 'ui/collection',
            _elems: [],
            ignoreTmpls: {
                childDefaults: true
            }
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Model} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe({
                    elems: []
                });

            return this;
        },

        /**
         * Called when another element was added to current component.
         *
         * @param {Object} elem - Instance of an element that was added.
         * @returns {Collection} Chainable.
         */
        initElement: function (elem) {
            elem.initContainer(this);

            return this;
        },

        /**
         * Returns instance of a child found by provided index.
         *
         * @param {String} index - Index of a child.
         * @returns {Object}
         */
        getChild: function (index) {
            return _.findWhere(this.elems(), {
                index: index
            });
        },

        /**
         * Requests specified components to insert
         * them into 'elems' array starting from provided position.
         *
         * @param {(String|Array)} elems - Name of the component to insert.
         * @param {Number} [position=-1] - Position at which to insert elements.
         * @returns {Collection} Chainable.
         */
        insertChild: function (elems, position) {
            var container   = this._elems,
                insert      = this._insert.bind(this),
                update;

            if (!Array.isArray(elems)) {
                elems = [elems];
            }

            elems.map(function (item) {
                return item.elem ?
                    _insertAt(item.elem, container, item.position) :
                    _insertAt(item, container, position);
            }).forEach(function (item) {
                if (item === true) {
                    update = true;
                } else if (_.isString(item)) {
                    registry.get(item, insert);
                } else if (utils.isObject(item)) {
                    insert(item);
                }
            });

            if (update) {
                this._updateCollection();
            }

            return this;
        },

        /**
         * Removes specified child from collection.
         *
         * @param {(Object|String)} elem - Child or index of a child to be removed.
         * @param {Boolean} skipUpdate - skip collection update when element to be destroyed.
         *
         * @returns {Collection} Chainable.
         */
        removeChild: function (elem, skipUpdate) {
            if (_.isString(elem)) {
                elem = this.getChild(elem);
            }

            if (elem) {
                utils.remove(this._elems, elem);

                if (!skipUpdate) {
                    this._updateCollection();
                }
            }

            return this;
        },

        /**
         * Destroys collection children with its' elements.
         */
        destroyChildren: function () {
            this.elems.each(function (elem) {
                elem.destroy(true);
            });

            this._updateCollection();
        },

        /**
         * Clear data. Call method "clear"
         * in child components
         *
         * @returns {Object} Chainable.
         */
        clear: function () {
            var elems = this.elems();

            _.each(elems, function (elem) {
                if (_.isFunction(elem.clear)) {
                    elem.clear();
                }
            }, this);

            return this;
        },

        /**
         * Checks if specified child exists in collection.
         *
         * @param {String} index - Index of a child.
         * @returns {Boolean}
         */
        hasChild: function (index) {
            return !!this.getChild(index);
        },

        /**
         * Creates 'async' wrapper for the specified child
         * using uiRegistry 'async' method and caches it
         * in a '_requested' components  object.
         *
         * @param {String} index - Index of a child.
         * @returns {Function} Async module wrapper.
         */
        requestChild: function (index) {
            var name = this.formChildName(index);

            return this.requestModule(name);
        },

        /**
         * Creates complete child name based on a provided index.
         *
         * @param {String} index - Index of a child.
         * @returns {String}
         */
        formChildName: function (index) {
            return this.name + '.' + index;
        },

        /**
         * Retrieves requested region.
         * Creates region if it was not created yet
         *
         * @returns {ObservableArray}
         */
        getRegion: function (name) {
            var regions = this.regions = this.regions || {};

            if (!regions[name]) {
                regions[name] = [];

                this.observe.call(regions, name);
            }

            return regions[name];
        },

        /**
         * Checks if the specified region has any elements
         * associated with it.
         *
         * @param {String} name
         * @returns {Boolean}
         */
        regionHasElements: function (name) {
            var region = this.getRegion(name);

            return region().length > 0;
        },

        /**
         * Replaces specified regions' data with a provided one.
         * Creates region if it was not created yet.
         *
         * @param {Array} items - New regions' data.
         * @param {String} name - Name of the region.
         * @returns {Collection} Chainable.
         */
        updateRegion: function (items, name) {
            this.getRegion(name)(items);

            return this;
        },

        /**
         * Destroys collection along with its' elements.
         */
        destroy: function () {
            this._super();

            this.elems.each('destroy');
        },

        /**
         * Inserts provided component into 'elems' array at a specified position.
         * @private
         *
         * @param {Object} elem - Element to insert.
         */
        _insert: function (elem) {
            var index = _.findKey(this._elems, function (value) {
                return value === elem.name;
            });

            if (typeof index !== 'undefined') {
                this._elems[index] = elem;
            }

            this._updateCollection()
                .initElement(elem);
        },

        /**
         * Synchronizes multiple elements arrays with a core '_elems' container.
         * Performs elemets grouping by theirs 'displayArea' property.
         * @private
         *
         * @returns {Collection} Chainable.
         */
        _updateCollection: function () {
            var _elems = compact(this._elems),
                grouped;

            grouped = _elems.filter(function (elem) {
                return elem.displayArea && _.isString(elem.displayArea);
            });
            grouped = _.groupBy(grouped, 'displayArea');

            _.each(grouped, this.updateRegion, this);

            _.each(this.regions, function (items) {
                var hasObsoleteComponents = items().length && !_.intersection(_elems, items()).length;

                if (hasObsoleteComponents) {
                    items.removeAll();
                }
            });

            this.elems(_elems);

            return this;
        },

        /**
         * Tries to call specified method of a current component,
         * otherwise delegates attempt to its' children.
         *
         * @param {String} target - Name of the method.
         * @param {...*} parameters - Arguments that will be passed to method.
         * @returns {*} Result of the method calls.
         */
        delegate: function (target) {
            var args = _.toArray(arguments);

            target = this[target];

            if (_.isFunction(target)) {
                return target.apply(this, args.slice(1));
            }

            return this._delegate(args);
        },

        /**
         * Calls 'delegate' method of all of it's children components.
         * @private
         *
         * @param {Array} args - An array of arguments to pass to the next delegation call.
         * @returns {Array} An array of delegation results.
         */
        _delegate: function (args) {
            var result;

            result = this.elems.map(function (elem) {
                var target;

                if (!_.isFunction(elem.delegate)) {
                    target = elem[args[0]];

                    if (_.isFunction(target)) {
                        return target.apply(elem, args.slice(1));
                    }
                } else {
                    return elem.delegate.apply(elem, args);
                }
            });

            return _.flatten(result);
        }
    });
});
