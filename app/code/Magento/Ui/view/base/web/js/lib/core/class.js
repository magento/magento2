/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mageUtils',
    'mage/utils/wrapper'
], function (_, utils, wrapper) {
    'use strict';

    var Class;

    /**
     * Returns property of an object if
     * it's his own property.
     *
     * @param {Object} obj - Object whose property should be retrieved.
     * @param {String} prop - Name of the property.
     * @returns {*} Value of the property or false.
     */
    function getOwn(obj, prop) {
        return _.isObject(obj) && obj.hasOwnProperty(prop) && obj[prop];
    }

    /**
     * Creates constructor function which allows
     * initialization without usage of a 'new' operator.
     *
     * @param {Object} protoProps - Prototypal propeties of a new constructor.
     * @param {Function} constructor
     * @returns {Function} Created constructor.
     */
    function createConstructor(protoProps, constructor) {
        var UiClass = constructor;

        if (!UiClass) {

            /**
             * Default constructor function.
             */
            UiClass = function () {
                var obj = this;

                if (!_.isObject(obj) || Object.getPrototypeOf(obj) !== UiClass.prototype) {
                    obj = Object.create(UiClass.prototype);
                }

                obj.initialize.apply(obj, arguments);

                return obj;
            };
        }

        UiClass.prototype = protoProps;
        UiClass.prototype.constructor = UiClass;

        return UiClass;
    }

    Class = createConstructor({

        /**
         * Entry point to the initialization of constructor's instance.
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

            config = utils.template(config, this, false, true);

            _.each(cached, function (value, key) {
                utils.nested(config, key, value);
            });

            return _.extend(this, config);
        }
    });

    _.extend(Class, {
        defaults: {
            ignoreTmpls: {
                templates: true
            }
        },

        /**
         * Creates new constructor based on a current prototype properties,
         * extending them with properties specified in 'exender' object.
         *
         * @param {Object} [extender={}]
         * @returns {Function} New constructor.
         */
        extend: function (extender) {
            var parent      = this,
                parentProto = parent.prototype,
                childProto  = Object.create(parentProto),
                child       = createConstructor(childProto, getOwn(extender, 'constructor')),
                defaults;

            extender = extender || {};
            defaults = extender.defaults;

            delete extender.defaults;

            _.each(extender, function (method, name) {
                childProto[name] = wrapper.wrapSuper(parentProto[name], method);
            });

            child.defaults = utils.extend({}, parent.defaults || {});

            if (defaults) {
                utils.extend(child.defaults, defaults);
                extender.defaults = defaults;
            }

            return _.extend(child, {
                __super__:  parentProto,
                extend:     parent.extend
            });
        }
    });

    return Class;
});
