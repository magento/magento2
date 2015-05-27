/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'mageUtils',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/lib/storage'
], function (ko, utils, _, registry) {
    'use strict';

    return {
        defaults: {
            template: 'ui/collection',
            parentName: '${ $.$data.getPart( $.name, -2) }',
            parentScope: '${ $.$data.getPart( $.dataScope, -2) }',
            containers: [],
            _elems: [],
            elems: [],
            storageConfig: {
                provider: 'localStorage',
                namespace: '${ $.name }',
                path: '${ $.storageConfig.provider }:${ $.storageConfig.namespace }'
            },
            additionalClasses: false
        },

        /**
         * Initializes component.
         *
         * @returns {Component} Chainable.
         */
        initialize: function () {
            _.bindAll(this, '_insert', 'trigger');

            this._super()
                .initProperties()
                .initObservable()
                .initStorage()
                .initModules()
                .initUnique()
                .initLinks()
                .setListners(this.listens);

            return this;
        },

        /**
         * Defines various properties.
         *
         * @returns {Component} Chainable.
         */
        initProperties: function () {
            _.extend(this, {
                source: registry.get(this.provider)
            });

            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Component} Chainable.
         */
        initObservable: function () {
            this.observe('elems');

            return this;
        },

        /**
         * Creates async wrapper on a specified storage component.
         *
         * @returns {Component} Chainable.
         */
        initStorage: function () {
            this.storage = registry.async(this.storageConfig.provider);

            return this;
        },

        /**
         * Initializes links between properties.
         *
         * @returns {Component} Chainbale.
         */
        initLinks: function () {
            this.setLinks(this.links, 'imports')
                .setLinks(this.links, 'exports');

            _.each({
                exports: this.exports,
                imports: this.imports
            }, this.setLinks, this);

            return this;
        },

        /**
         * Parses 'modules' object and creates
         * async wrappers on specified components.
         *
         * @returns {Component} Chainable.
         */
        initModules: function () {
            var modules = this.modules || {};

            _.each(modules, function (component, property) {
                this[property] = registry.async(component);
            }, this);

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
            offset = utils.formatOffset(parts, offset);

            parts.splice(offset, 1);

            return parts.join(delimiter) || '';
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
         * Provides classes of element as object used by knockout's css binding.
         */
        getStyles: function() {
            var styles = {
                required: this.required,
                _error: this.error,
                _disabled: this.disabled
            };
            if (typeof this.additionalClasses === 'string') {
                var item,
                    additionalClasses = this.additionalClasses.split(" ");
                for (item in additionalClasses) {
                    if (additionalClasses.hasOwnProperty(item)) {
                        styles[additionalClasses[item]] = true;
                    }
                }
            }
            return styles;
        }
    };
});
