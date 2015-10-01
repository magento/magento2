/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'uiRegistry',
    'uiModel',
    'Magento_Ui/js/lib/ko/initialize'
], function (_, utils, registry, Model) {
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

    return Model.extend({
        defaults: {
            template: 'ui/collection',
            componentType: 'container',
            registerNodes: true,
            ignoreTmpls: {
                childDefaults: true
            }
        },

        /**
         * Initializes component.
         *
         * @returns {Component} Chainable.
         */
        initialize: function () {
            this._super()
                .initProperties()
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
         * Defines various properties.
         *
         * @returns {Component} Chainable.
         */
        initProperties: function () {
            if (!this.source) {
                this.source = registry.get(this.provider);
            }

            _.extend(this, {
                containers: [],
                _elems: []
            });

            return this;
        },

        /**
         * Initializes listeners of the unique property.
         *
         * @returns {Component} Chainable.
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
         * Called when current element was injected to another component.
         *
         * @param {Object} parent - Instance of a 'parent' component.
         * @returns {Component} Chainable.
         */
        initContainer: function (parent) {
            this.containers.push(parent);

            return this;
        },

        /**
         * Called when another element was added to current component.
         *
         * @param {Object} elem - Instance of an element that was added.
         * @returns {Component} Chainable.
         */
        initElement: function (elem) {
            elem.initContainer(this);

            return this;
        },

        /**
         * Returns path to components' template.
         * @returns {String}
         */
        getTemplate: function () {
            return this.template;
        },

        /**
         * Updates property specified in uniqueNs
         * if components' unique property is set to 'true'.
         *
         * @returns {Component} Chainable.
         */
        setUnique: function () {
            var property = this.uniqueProp;

            if (this[property]()) {
                this.source.set(this.uniqueNs, this.name);
            }

            return this;
        },

        /**
         * Callback which fires when property under uniqueNs has changed.
         */
        onUniqueUpdate: function (name) {
            var active = name === this.name,
                property = this.uniqueProp;

            this[property](active);
        },

        /**
         * Retrieves requested region.
         * Creates region if it was not created yet
         *
         * @returns {ObservableArray}.
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
         * Replaces specified regions' data with a provided one.
         * Creates region if it was not created yet.
         *
         * @param {Array} items - New regions' data.
         * @param {String} name - Name of the region.
         * @returns {Component} Chainable.
         */
        updateRegion: function (items, name) {
            if (name) {
                this.getRegion(name)(items);
            }

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
         * @returns {Component} Chainable.
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
                return elem.delegate.apply(elem, args);
            });

            return _.flatten(result);
        },

        /**
         * Overrides 'EventsBus.trigger' method to implement events bubbling.
         *
         * @param {...*} parameters - Any number of arguments that should be passed to the events' handler.
         * @returns {Boolean} False if event bubbling was canceled.
         */
        bubble: function () {
            var args = _.toArray(arguments),
                bubble = this.trigger.apply(this, args),
                result;

            if (!bubble) {
                return false;
            }

            this.containers.forEach(function (parent) {
                result = parent.bubble.apply(parent, args);

                if (result === false) {
                    bubble = false;
                }
            });

            return !!bubble;
        }
    });
});
