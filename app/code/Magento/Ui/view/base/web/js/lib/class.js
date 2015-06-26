/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils'
], function (_, utils) {
    'use strict';

    /**
     * Checks if string has a '_super' substring.
     */
    var superReg = /\b_super\b/;

    /**
     * Checks wether the incoming method contains calls of the '_super' property.
     *
     * @param {Function} method - Method to be checked.
     * @returns {Boolean}
     */
    function hasSuper(method) {
        return _.isFunction(method) && superReg.test(method);
    }

    /**
     * Wraps the incoming method to implement support of the '_super' method.
     *
     * @param {Object} parent - Reference to parents' prototype.
     * @param {String} name - Name of the method.
     * @param {Function} method - Method to be wrapped.
     * @returns {Function} Wrapped method.
     */
    function superWrapper(parent, name, method) {
        return function () {
            var superTmp = this._super,
                args = arguments,
                result;

            this._super = function () {
                var superArgs = arguments.length ? arguments : args;

                return parent[name].apply(this, superArgs);
            };

            result = method.apply(this, args);

            this._super = superTmp;

            return result;
        };
    }

    /**
     * Analogue of Backbone.extend function.
     *
     * @param  {Object} extender - Object, that describes the prototype of
     *      created constructor.
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

        defaults = utils.extend({}, parent.defaults, defaults);

        child.prototype = Object.create(parentProto);
        child.prototype.constructor = child;

        _.each(extender, function (method, name) {
            child.prototype[name] = hasSuper(method) ?
                superWrapper(parentProto, name, method) :
                method;
        });

        child.__super__ = parentProto;
        child.extend = extend;
        child.defaults = defaults;

        return child;
    }

    /**
     * Constructor.
     */
    function Class() {
        this.initialize.apply(this, arguments);
    }

    _.extend(Class, {
        extend: extend,
        defaults: {
            tmplsIgnore: {
                templates: true
            }
        }
    });

    _.extend(Class.prototype, {
        /**
         * Starts initialization of an instance.
         *
         * @param {Object} options
         * @returns {Class} Chainable.
         */
        initialize: function (options) {
            this.initConfig(options);

            return this;
        },

        /**
         * Recursively extends data specified in constructors' 'defaults'
         * property with a provided options object.
         *
         * @param {Object} options
         * @returns {Class} Chainable.
         */
        initConfig: function (options) {
            var defaults    = this.constructor.defaults,
                config      = utils.extend({}, defaults, options),
                ignored     = config.tmplsIgnore || {},
                cached      = utils.omit(config, ignored);

            config = utils.template(config, this);

            _.each(cached, function (value, key) {
                utils.nested(config, key, value);
            });

            _.extend(this, config);

            return this;
        }
    });

    return Class;
});
