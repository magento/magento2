/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'uiRegistry',
    'uiElement'
], function (_, utils, registry, Element) {
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

    return Element.extend({
        defaults: {
            template: 'ui/collection',
            componentType: 'container',
            _elems: [],
            ignoreTmpls: {
                childDefaults: true
            }
        },

        /**
         * Initializes component.
         *
         * @returns {Collection} Chainable.
         */
        initialize: function () {
            this._super()
                .initUnique();

            return this;
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
         * Initializes listeners of the unique property.
         *
         * @returns {Collection} Chainable.
         */
        initUnique: function () {
            var update = this.onUniqueUpdate.bind(this),
                uniqueNs = this.uniqueNs;

            this.hasUnique = this.uniqueProp && uniqueNs;

            if (this.hasUnique) {
                this.source.on(uniqueNs, update, this.name);
            }

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
         * Updates property specified in uniqueNs
         * if components' unique property is set to 'true'.
         *
         * @returns {Collection} Chainable.
         */
        setUnique: function () {
            var property = this.uniqueProp;

            if (this[property]()) {
                this.source.set(this.uniqueNs, this.name);
            }

            return this;
        },

        /**
         * Retrieves requested region.
         * Creates region if it was not created yet
         *
         * @returns {ObservableArray}.
         */
        getRegion: function (name) {
            var regions = this.regions = this.regions || {},
                region;

            if (name) {
                if (!regions[name]) {
                    regions[name] = [];

                    this.observe.call(regions, name);
                }

                region = regions[name];
            }

            return region;
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
            var region = this.getRegion(name);

            if (region) {
                region(items);
            }

            return this;
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
                    utils.insert(item.elem, container, item.position) :
                    utils.insert(item, container, position);
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
                this._update();
            }

            return this;
        },

        /**
         * Removes specified element from the 'elems' array.
         *
         * @param {Object} elem - Element to be removed.
         * @returns {Collection} Chainable.
         */
        removeChild: function (elem) {
            utils.remove(this._elems, elem);
            this._update();

            return this;
        },

        /**
         * Removes all references to current instance and
         * calls 'destroy' method on all of its' children.
         * @private
         *
         * @returns {Collection} Chainable.
         */
        _clearRefs: function () {
            this._super();

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

            if (~index) {
                this._elems[index] = elem;
            }

            this._update()
                .initElement(elem);
        },

        /**
         * Synchronizes multiple elements arrays with a core '_elems' container.
         * Performs elemets grouping by theirs 'displayArea' property.
         * @private
         *
         * @returns {Collection} Chainable.
         */
        _update: function () {
            var _elems = compact(this._elems),
                grouped = _.groupBy(_elems, 'displayArea');

            _.each(grouped, this.updateRegion, this);

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
         * @returns {Array} An array of delegation resutls.
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
        },

        /**
         * Callback which fires when property under uniqueNs has changed.
         */
        onUniqueUpdate: function (name) {
            var active = name === this.name,
                property = this.uniqueProp;

            this[property](active);
        }
    });
});
