/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/lib/storage'
], function (_, registry) {
    'use strict';

    return {
        defaults: {
            template: 'ui/collection',
            ignoreTmpls: {
                childDefaults: true
            },
            storageConfig: {
                provider: 'localStorage',
                namespace: '${ $.name }',
                path: '${ $.storageConfig.provider }:${ $.storageConfig.namespace }'
            },
            modules: {
                storage: '${ $.storageConfig.provider }'
            }
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
                .initModules()
                .initUnique()
                .setListners(this.listens)
                .initLinks();

            return this;
        },

        /**
         * Defines various properties.
         *
         * @returns {Component} Chainable.
         */
        initProperties: function () {
            _.extend(this, {
                source: registry.get(this.provider),
                containers: [],
                _elems: [],
                elems: []
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
        }
    };
});
