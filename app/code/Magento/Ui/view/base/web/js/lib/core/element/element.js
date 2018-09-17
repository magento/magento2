/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'underscore',
    'mageUtils',
    'uiRegistry',
    'uiEvents',
    'uiClass',
    './links',
    '../storage/local'
], function (ko, _, utils, registry, Events, Class, links) {
    'use strict';

    var Element;

    /**
     * Creates observable property using knockouts'
     * 'observableArray' or 'observable' methods,
     * depending on a type of 'value' parameter.
     *
     * @param {Object} obj - Object to whom property belongs.
     * @param {String} key - Key of the property.
     * @param {*} value - Initial value.
     */
    function observable(obj, key, value) {
        var method = Array.isArray(value) ? 'observableArray' : 'observable';

        if (_.isFunction(obj[key]) && !ko.isObservable(obj[key])) {
            return;
        }

        if (ko.isObservable(value)) {
            value = value();
        }

        ko.isObservable(obj[key]) ?
            obj[key](value) :
            obj[key] = ko[method](value);
    }

    /**
     * Creates observable property using 'track' method.
     *
     * @param {Object} obj - Object to whom property belongs.
     * @param {String} key - Key of the property.
     * @param {*} value - Initial value.
     */
    function accessor(obj, key, value) {
        if (_.isFunction(obj[key]) || ko.isObservable(obj[key])) {
            return;
        }

        obj[key] = value;

        if (!ko.es5.isTracked(obj, key)) {
            ko.track(obj, [key]);
        }
    }

    Element = _.extend({
        defaults: {
            _requested: {},
            containers: [],
            exports: {},
            imports: {},
            links: {},
            listens: {},
            name: '',
            ns: '${ $.name.split(".")[0] }',
            provider: '',
            registerNodes: true,
            source: null,
            statefull: {},
            template: '',
            tracks: {},
            storageConfig: {
                provider: 'localStorage',
                namespace: '${ $.name }',
                path: '${ $.storageConfig.provider }:${ $.storageConfig.namespace }'
            },
            maps: {
                imports: {},
                exports: {}
            },
            modules: {
                storage: '${ $.storageConfig.provider }'
            }
        },

        /**
         * Initializes model instance.
         *
         * @returns {Element} Chainable.
         */
        initialize: function () {
            this._super()
                .initObservable()
                .initModules()
                .initStatefull()
                .initLinks()
                .initUnique();

            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Element} Chainable.
         */
        initObservable: function () {
            _.each(this.tracks, function (enabled, key) {
                if (enabled) {
                    this.track(key);
                }
            }, this);

            return this;
        },

        /**
         * Parses 'modules' object and creates
         * async wrappers for specified components.
         *
         * @returns {Element} Chainable.
         */
        initModules: function () {
            _.each(this.modules, function (name, property) {
                if (name) {
                    this[property] = this.requestModule(name);
                }
            }, this);

            if (!_.isFunction(this.source)) {
                this.source = registry.get(this.provider);
            }

            return this;
        },

        /**
         * Called when current element was injected to another component.
         *
         * @param {Object} parent - Instance of a 'parent' component.
         * @returns {Collection} Chainable.
         */
        initContainer: function (parent) {
            this.containers.push(parent);

            return this;
        },

        /**
         * Initializes statefull properties
         * based on the keys of 'statefull' object.
         *
         * @returns {Element} Chainable.
         */
        initStatefull: function () {
            _.each(this.statefull, function (path, key) {
                if (path) {
                    this.setStatefull(key, path);
                }
            }, this);

            return this;
        },

        /**
         * Initializes links between properties.
         *
         * @returns {Element} Chainbale.
         */
        initLinks: function () {
            return this.setListeners(this.listens)
                       .setLinks(this.links, 'imports')
                       .setLinks(this.links, 'exports')
                       .setLinks(this.exports, 'exports')
                       .setLinks(this.imports, 'imports');
        },

        /**
         * Initializes listeners of the unique property.
         *
         * @returns {Element} Chainable.
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
         * Makes specified property to be stored automatically.
         *
         * @param {String} key - Name of the property
         *      that will be stored.
         * @param {String} [path=key] - Path to the property in storage.
         * @returns {Element} Chainable.
         */
        setStatefull: function (key, path) {
            var link = {};

            path        = !_.isString(path) || !path ? key : path;
            link[key]   = this.storageConfig.path + '.' + path;

            this.setLinks(link, 'imports')
                .setLinks(link, 'exports');

            return this;
        },

        /**
         * Updates property specified in uniqueNs
         * if elements' unique property is set to 'true'.
         *
         * @returns {Element} Chainable.
         */
        setUnique: function () {
            var property = this.uniqueProp;

            if (this[property]()) {
                this.source.set(this.uniqueNs, this.name);
            }

            return this;
        },

        /**
         * Creates 'async' wrapper for the specified component
         * using uiRegistry 'async' method and caches it
         * in a '_requested' components  object.
         *
         * @param {String} name - Name of requested component.
         * @returns {Function} Async module wrapper.
         */
        requestModule: function (name) {
            var requested = this._requested;

            if (!requested[name]) {
                requested[name] = registry.async(name);
            }

            return requested[name];
        },

        /**
         * Returns path to elements' template.
         *
         * @returns {String}
         */
        getTemplate: function () {
            return this.template;
        },

        /**
         * Checks if template was specified for an element.
         *
         * @returns {Boolean}
         */
        hasTemplate: function () {
            return !!this.template;
        },

        /**
         * Returns value of the nested property.
         *
         * @param {String} path - Path to the property.
         * @returns {*} Value of the property.
         */
        get: function (path) {
            return utils.nested(this, path);
        },

        /**
         * Sets provided value as a value of the specified nested property.
         * Triggers changes notifications, if value has mutated.
         *
         * @param {String} path - Path to property.
         * @param {*} value - New value of the property.
         * @returns {Element} Chainable.
         */
        set: function (path, value) {
            var data = this.get(path),
                diffs;

            diffs = !_.isFunction(data) && !this.isTracked(path) ?
                utils.compare(data, value, path) :
                false;

            utils.nested(this, path, value);

            if (diffs) {
                this._notifyChanges(diffs);
            }

            return this;
        },

        /**
         * Removes nested property from the object.
         *
         * @param {String} path - Path to the property.
         * @returns {Element} Chainable.
         */
        remove: function (path) {
            var data = utils.nested(this, path),
                diffs;

            if (_.isUndefined(data) || _.isFunction(data)) {
                return this;
            }

            diffs = utils.compare(data, undefined, path);

            utils.nestedRemove(this, path);

            this._notifyChanges(diffs);

            return this;
        },

        /**
         * Creates observable properties for the current object.
         *
         * If 'useTrack' flag is set to 'true' then each property will be
         * created with a ES5 get/set accessor descriptors, instead of
         * making them an observable functions.
         * See 'knockout-es5' library for more information.
         *
         * @param {Boolean} [useAccessors=false] - Whether to create an
         *      observable function or to use property accesessors.
         * @param {(Object|String|Array)} properties - List of observable properties.
         * @returns {Element} Chainable.
         *
         * @example Sample declaration and equivalent knockout methods.
         *      this.key = 'value';
         *      this.array = ['value'];
         *
         *      this.observe(['key', 'array']);
         *      =>
         *          this.key = ko.observable('value');
         *          this.array = ko.observableArray(['value']);
         *
         * @example Another syntaxes of the previous example.
         *      this.observe({
         *          key: 'value',
         *          array: ['value']
         *      });
         */
        observe: function (useAccessors, properties) {
            var model = this,
                trackMethod;

            if (typeof useAccessors !== 'boolean') {
                properties   = useAccessors;
                useAccessors = false;
            }

            trackMethod = useAccessors ? accessor : observable;

            if (_.isString(properties)) {
                properties = properties.split(' ');
            }

            if (Array.isArray(properties)) {
                properties.forEach(function (key) {
                    trackMethod(model, key, model[key]);
                });
            } else if (typeof properties === 'object') {
                _.each(properties, function (value, key) {
                    trackMethod(model, key, value);
                });
            }

            return this;
        },

        /**
         * Delegates call to 'observe' method but
         * with a predefined 'useAccessors' flag.
         *
         * @param {(String|Array|Object)} properties - List of observable properties.
         * @returns {Element} Chainable.
         */
        track: function (properties) {
            this.observe(true, properties);

            return this;
        },

        /**
         * Checks if specified property is tracked.
         *
         * @param {String} property - Property to be checked.
         * @returns {Boolean}
         */
        isTracked: function (property) {
            return ko.es5.isTracked(this, property);
        },

        /**
         * Invokes subscribers for the provided changes.
         *
         * @param {Object} diffs - Object with changes descriptions.
         * @returns {Element} Chainable.
         */
        _notifyChanges: function (diffs) {
            diffs.changes.forEach(function (change) {
                this.trigger(change.path, change.value, change);
            }, this);

            _.each(diffs.containers, function (changes, name) {
                var value = utils.nested(this, name);

                this.trigger(name, value, changes);
            }, this);

            return this;
        },

        /**
         * Extracts all stored data and sets it to element.
         *
         * @returns {Element} Chainable.
         */
        restore: function () {
            var ns = this.storageConfig.namespace,
                storage = this.storage();

            if (storage) {
                utils.extend(this, storage.get(ns));
            }

            return this;
        },

        /**
         * Stores value of the specified property in components' storage module.
         *
         * @param {String} property
         * @param {*} [data=this[property]]
         * @returns {Element} Chainable.
         */
        store: function (property, data) {
            var ns = this.storageConfig.namespace,
                path = utils.fullPath(ns, property);

            if (arguments.length < 2) {
                data = this.get(property);
            }

            this.storage('set', path, data);

            return this;
        },

        /**
         * Extracts specified property from storage.
         *
         * @param {String} [property] - Name of the property
         *      to be extracted. If not specified then all of the
         *      stored will be returned.
         * @returns {*}
         */
        getStored: function (property) {
            var ns = this.storageConfig.namespace,
                path = utils.fullPath(ns, property),
                storage = this.storage(),
                data;

            if (storage) {
                data = storage.get(path);
            }

            return data;
        },

        /**
         * Removes stored property.
         *
         * @param {String} property - Property to be removed from storage.
         * @returns {Element} Chainable.
         */
        removeStored: function (property) {
            var ns = this.storageConfig.namespace,
                path = utils.fullPath(ns, property);

            this.storage('remove', path);

            return this;
        },

        /**
         * Destroys current instance along with all of its' children.
         * @param {Boolean} skipUpdate - skip collection update when element to be destroyed.
         */
        destroy: function (skipUpdate) {
            this._dropHandlers()
                ._clearRefs(skipUpdate);
        },

        /**
         * Removes events listeners.
         * @private
         *
         * @returns {Element} Chainable.
         */
        _dropHandlers: function () {
            this.off();

            if (_.isFunction(this.source)) {
                this.source().off(this.name);
            } else if (this.source) {
                this.source.off(this.name);
            }

            return this;
        },

        /**
         * Removes all references to current instance and
         * calls 'destroy' method on all of its' children.
         * @private
         * @param {Boolean} skipUpdate - skip collection update when element to be destroyed.
         *
         * @returns {Element} Chainable.
         */
        _clearRefs: function (skipUpdate) {
            registry.remove(this.name);

            this.containers.forEach(function (parent) {
                parent.removeChild(this, skipUpdate);
            }, this);

            return this;
        },

        /**
         * Overrides 'EventsBus.trigger' method to implement events bubbling.
         *
         * @param {...*} arguments - Any number of arguments that should be passed to the events' handler.
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
         * Clean data form data source.
         *
         * @returns {Element}
         */
        cleanData: function () {
            if (this.source && this.source.componentType === 'dataSource') {
                if (this.elems) {
                    _.each(this.elems(), function (val) {
                        val.cleanData();
                    });
                } else {
                    this.source.remove(this.dataScope);
                }
            }

            return this;
        },

        /**
         * Fallback data.
         */
        cacheData: function () {
            this.cachedComponent = utils.copy(this);
        },

        /**
         * Update configuration in component.
         *
         * @param {*} oldValue
         * @param {*} newValue
         * @param {String} path - path to value.
         * @returns {Element}
         */
        updateConfig: function (oldValue, newValue, path) {
            var names = path.split('.'),
                index = _.lastIndexOf(names, 'config') + 1;

            names = names.splice(index, names.length - index).join('.');
            this.set(names, newValue);

            return this;
        }
    }, Events, links);

    return Class.extend(Element);
});
