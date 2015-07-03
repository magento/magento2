/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'mage/utils/super'
], function (_, utils, superWrapper) {
    'use strict';

    /**
     * Creates new constructor based on a current prototype properties,
     * extending them with properties specified in 'exender' object.
     *
     * @param {Object} [extender={}]
     * @returns {Function} New constructor.
     */
    function extend(extender) {
        var parent = this,
            defaults = extender.defaults || {},
            parentProto = parent.prototype,
            child;

        defaults = defaults || {};
        extender = extender || {};

        delete extender.defaults;

        if (extender.hasOwnProperty('constructor')) {
            child = extender.constructor;
        } else {
            child = function () {
                parent.apply(this, arguments);
            };
        }

        child.prototype = Object.create(parentProto);
        child.prototype.constructor = child;

        _.each(extender, function (method, name) {
            child.prototype[name] = superWrapper.create(method, parentProto, name);
        });

        return _.extend(child, {
            __super__:  parentProto,
            extend:     parent.extend,
            defaults:   utils.extend({}, parent.defaults, defaults)
        });
    }

    /**
     * Constructor.
     */
    function Class() {
        this.initialize.apply(this, arguments);
    }

    _.extend(Class, {
        defaults: {
            ignoreTmpls: {
                templates: true
            }
        },
        extend: extend
    });

    _.extend(Class.prototype, {
        /**
         * Entry point to the initialization of consturctors' instance.
         *
         * @param {Object} [options={}]
         * @returns {Class} Chainable.
         */
        initialize: function (options) {
            this.initConfig(options);

            return this;
        },

        /**
         * Recursively extends data specified in constructors' 'defaults'
         * property with provided options object. Evaluates resulting
         * object using string templates (see: mage/utils/template.js).
         *
         * @param {Object} [options={}]
         * @returns {Class} Chainable.
         */
        initConfig: function (options) {
            var defaults    = this.constructor.defaults,
                config      = utils.extend({}, defaults, options || {}),
                ignored     = config.ignoreTmpls || {},
                cached      = utils.omit(config, ignored);

            config = utils.template(config, this);

            _.each(cached, function (value, key) {
                utils.nested(config, key, value);
            });

            return _.extend(this, config);
        }
    });

    return Class;
});
