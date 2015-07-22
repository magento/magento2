/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
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
         * Wraps target function with a specified wrapper, which will recieve
         * reference to the original function as a first parameter.
         *
         * @param {Function} target - Function to be wrapped.
         * @param {Function} wrapper - Wrapper function.
         * @returns {Function} Wrapper function.
         *
         * @example Wrapper which shows arguments delegation.
         *      var multiply = function (a, b) {
         *          return a * b;
         *      };
         *
         *      multiply = _super.wrap(multiply, function (orig) {
         *          return 'Result is: ' + orig();
         *      });
         *
         *      multiply(2, 2);
         *      => 'Result is: 4'
         */
        wrap: function (target, wrapper) {
            return function () {
                var args    = _.toArray(arguments),
                    ctx     = this,
                    _super;

                /**
                 * Function that will be passed to the wrapper.
                 * If no arguments will be passed to it, then the original
                 * function will be called with an arguments of a wrapper function.
                 */
                _super = function () {
                    var superArgs = arguments.length ? arguments : args.slice(1);

                    return target.apply(ctx, superArgs);
                };

                args.unshift(_super);

                return wrapper.apply(ctx, args);
            };
        },

        /**
         * Wraps the incoming function to implement support of the '_super' method.
         *
         * @param {Function} target - Function to be wrapped.
         * @param {Function} wrapper - Wrapper function.
         * @returns {Function} Wrapped function.
         *
         * @example Sample usage.
         *      var multiply = function (a, b) {
         *         return a * b;
         *      };
         *      var obj = {
         *          multiply: _super.wrapSuper(multiply, function () {
         *              return 'Result is: ' + this._super();
         *          });
         *      };
         *
         *      obj.multiply(2, 2);
         *      => 'Result is: 4'
         */
        wrapSuper: function (target, wrapper) {
            if (!this.hasSuper(wrapper) || !_.isFunction(target)) {
                return wrapper;
            }

            return function () {
                var _super  = this._super,
                    args    = arguments,
                    result;

                /**
                 * Temporary define '_super' method which
                 * contains call to the original function.
                 */
                this._super = function () {
                    var superArgs = arguments.length ? arguments : args;

                    return target.apply(this, superArgs);
                };

                result = wrapper.apply(this, args);

                this._super = _super;

                return result;
            };
        },

        /**
         * Checks wether the incoming method contains calls of the '_super' property.
         *
         * @param {Function} fn - Function to be checked.
         * @returns {Boolean}
         */
        hasSuper: function (fn) {
            return _.isFunction(fn) && superReg.test(fn);
        }
    };
});
