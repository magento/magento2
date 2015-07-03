define([
    'underscore'
], function (_) {
    'use strict';

    /**
     * Checks if string has a '_super' substring.
     */
    var superReg = /\b_super\b/;

    return {
        /**
         * Wraps the incoming function to implement support of the '_super' method,
         * which allows to call the original function.
         *
         * @param {Function} fn - Method to be wrapped.
         * @param {(Object|Function)} parent - Reference to parents' object
         *      which contains original method or the method itself.
         * @param {String} [name] - Name of the method.
         * @returns {Function} Wrapped function.
         */
        create: function (fn, parent, name) {
            if (!this.has(fn)) {
                return fn;
            }

            return function () {
                var superTmp = this._super,
                    args     = arguments,
                    result;

                this._super = function () {
                    var superArgs = arguments.length ? arguments : args,
                        parentFn = typeof name === 'string' ? parent[name] : parent;

                    return parentFn.apply(this, superArgs);
                };

                result = fn.apply(this, args);

                this._super = superTmp;

                return result;
            };
        },

        /**
         * Checks wether the incoming method contains calls of the '_super' property.
         *
         * @param {Function} fn - Function to be checked.
         * @returns {Boolean}
         */
        has: function (fn) {
            return _.isFunction(fn) && superReg.test(fn);
        },

        /**
         * Copies properties from extenders to the 'target' object.
         * If property represents a function whith a '_super' call in it,
         * than it will be wrapped with a 'super.create' method.
         *
         * @param {Object} target - Object to be modified.
         * @param {...Object} extenders
         * @returns {Object} Extended 'target' object.
         */
        extend: function (target) {
            var extenders = _.toArray(arguments).slice(1),
                extend = this.extendSingle.bind(this, target);

            _.each(extenders, extend);

            return target;
        },

        /**
         * Same as the basic 'extend', except that
         * only one extender object can be passed.
         *
         * @param {Object} target - Object to be modified.
         * @param {Object} extender
         * @returns {Object} Extended 'target' object.
         */
        extendSingle: function (target, extender) {
            var key;

            for (key in extender) {
                if (extender.hasOwnProperty(key)) {
                    target[key] = this.create(extender[key], target[key]);
                }
            }

            return target;
        }
    };
});
