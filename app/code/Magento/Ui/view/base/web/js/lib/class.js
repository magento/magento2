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

    var Class;

    /**
     * Creates constructor function which allows
     * initialization without usage of a 'new' operator.
     *
     * @param {Object} protoProps - Prototypal propeties of a new consturctor.
     * @returns {Function} Created consturctor.
     */
    function createConstructor(protoProps) {
        /**
         * Constructor function.
         */
        var constr = function () {
            var obj = this;

            if (!obj || !Object.getPrototypeOf(obj) === constr.prototype) {
                obj = Object.create(constr.prototype);
            }

            obj.initialize.apply(obj, arguments);

            return obj;
        };

        constr.prototype = protoProps;
        constr.prototype.constructor = constr;

        return constr;
    }

    /**
     * Creates new constructor based on a current prototype properties,
     * extending them with properties specified in 'exender' object.
     *
     * @param {Object} [extender={}]
     * @returns {Function} New constructor.
     */
    function extend(extender) {
        var parent      = this,
            parentProto = parent.prototype,
            childProto  = Object.create(parentProto),
            child       = createConstructor(childProto),
            defaults    = extender.defaults || {};

        defaults = defaults || {};
        extender = extender || {};

        delete extender.defaults;

        _.each(extender, function (method, name) {
            childProto[name] = superWrapper.create(method, parentProto, name);
        });

        return _.extend(child, {
            __super__:  parentProto,
            extend:     parent.extend,
            defaults:   utils.extend({}, parent.defaults, defaults)
        });
    }

    Class = createConstructor({
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

    Class.defaults = {
        ignoreTmpls: {
            templates: true
        }
    };

    Class.extend = extend;

    return Class;
});
