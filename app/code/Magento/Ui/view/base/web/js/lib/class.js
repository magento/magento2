/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore'
], function(_) {
    'use strict';

    var superReg = /\b_super\b/;

    /**
     * Checks wether the incoming method contains calls of the '_super' property.
     *
     * @param {Function} method - Method to be checked.
     * @returns {Boolean}
     */
    function hasSuper(method){
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
    function superWrapper(parent, name, method){
        return function(){
            var superTmp    = this._super,
                args        = arguments,
                result;

            this._super = function(){
                var superArgs = arguments.length ? arguments : args;

                return parent[name].apply(this, superArgs);
            };

            result = method.apply(this, args);

            this._super = superTmp;

            return result;
        }
    }

    /**
     * Analogue of Backbone.extend function.
     *
     * @param  {Object} extender - 
     *      Object, that describes the prototype of
     *      created constructor.
     * @param {...Object} Multiple amount of mixins.
     * @returns {Function} New constructor.
     */
    function extend(extender){
        var parent      = this,
            parentProto = parent.prototype,
            defaults    = extender.defaults || {},
            child,
            childProto,
            mixins;

        child = function(){
            _.defaults(this, defaults);

            parent.apply(this, arguments);
        };

        delete extender.defaults;

        childProto = child.prototype = Object.create(parentProto);

        childProto.constructor = child;

        _.each(extender, function(method, name){
            childProto[name] = hasSuper(method) ?
                superWrapper(parentProto, name, method) :
                method;
        });

        mixins = _.toArray(arguments).slice(1);

        mixins.forEach(function(mixin){
            _.extend(childProto, mixin);
        });

        child.__super__ = parentProto;
        child.extend    = extend;

        return child;
    }

    /**
     * Constructor, which calls initialize with passed arguments.
     */
    function Class() {
        this.initialize.apply(this, arguments);
    }

    Class.prototype.initialize = function(){};

    Class.extend = extend;

    return Class;
});