/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'mageUtils',
    'underscore',
    'Magento_Ui/js/lib/registry/registry'
], function (ko, utils, _, registry) {
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

    /**
     * Wrapper for ko.observable and ko.observableArray.
     * Assignes one or another ko property to obj[key]
     * @param  {Object} obj   - object to store property to
     * @param  {String} key   - key
     * @param  {*} value      - initial value of observable
     */
    function observe(obj, key, value) {
        var method = Array.isArray(value) ? 'observableArray' : 'observable';

        if (!ko.isObservable(obj[key])) {
            obj[key] = ko[method](value);
        } else {
            obj[key](value);
        }
    }

    return {
        initialize: function (options, additional) {
            _.bindAll(this, '_insert');

            this.initConfig(options, additional)
                .initProperties()
                .initObservable()
                .initUnique()
                .initLinks()
                .setListners(this.listens);

            return this;
        },

        initConfig: function (options, additional) {
            var defaults = this.constructor.defaults,
                config = _.extend({}, defaults, options, additional);

            config = utils.template(config, this);

            _.extend(this, config);

            return this;
        },

        /**
         * Defines various properties.
         *
         * @returns {Component} Chainable.
         */
        initProperties: function () {
            _.extend(this, {
                'parentName': this.getPart(this.name, -2),
                'parentScope': this.getPart(this.dataScope, -2),
                'source': registry.get(this.provider),
                'renderer': registry.get('globalStorage').renderer,
                'containers': [],
                'regions': [],
                '_elems': []
            });

            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Component} Chainable.
         */
        initObservable: function () {
            this.observe({
                'elems': []
            });

            this.regions.forEach(function (region) {
                this.observe(region, []);
            }, this);

            return this;
        },

        initLinks: function () {
            _.each({
                both: this.links,
                exports: this.exports,
                imports: this.imports
            }, this.setLinks, this);

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
                this.source.on('update:params.' + uniqueNs, update, this.name);
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
         * Splits incoming string and returns its' part specified by offset.
         *
         * @param {String} parts
         * @param {Number} [offset]
         * @param {String} [delimiter=.]
         * @returns {String}
         */
        getPart: function (parts, offset, delimiter) {
            delimiter = delimiter || '.';
            parts = parts.split(delimiter);
            offset = getOffsetFor(parts, offset);

            parts.splice(offset, 1);

            return parts.join(delimiter) || '';
        },

        /**
         * Returns path to components' template.
         * @returns {String}
         */
        getTemplate: function () {
            return this.template || 'ui/collection';
        },

        /**
         * Updates property specified in uniqueNs
         * if components' unique property is set to 'true'.
         *
         * @returns {Component} Chainable.
         */
        setUnique: function () {
            var params = this.provider.params,
                property = this.uniqueProp;

            if (this[property]()) {
                params.set(this.uniqueNs, this.name);
            }

            return this;
        },

        /**
         * If 2 params passed, path is considered as key.
         * Else, path is considered as object.
         * Assignes props to this based on incoming params
         * @param  {Object|String} path
         */
        observe: function (path) {
            var type = typeof path;

            if (type === 'string') {
                path = path.split(' ');
            }

            if (Array.isArray(path)) {
                path.forEach(function (key) {
                    observe(this, key, this[key]);
                }, this);
            } else if (type === 'object') {
                _.each(path, function (value, key) {
                    observe(this, key, value);
                }, this);
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
        }
    };
});