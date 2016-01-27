/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Utility methods used to wrap and extend functions.
 *
 * @example Usage of a 'wrap' method with arguments delegation.
 *      var multiply = function (a, b) {
 *          return a * b;
 *      };
 *
 *      multiply = module.wrap(multiply, function (orig) {
 *          return 'Result is: ' + orig();
 *      });
 *
 *      multiply(2, 2);
 *      => 'Result is: 4'
 *
 * @example Usage of 'wrapSuper' method.
 *      var multiply = function (a, b) {
 *         return a * b;
 *      };
 *
 *      var obj = {
 *          multiply: module.wrapSuper(multiply, function () {
 *              return 'Result is: ' + this._super();
 *          });
 *      };
 *
 *      obj.multiply(2, 2);
 *      => 'Result is: 4'
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
         * reference to the original function as a first argument.
         *
         * @param {Function} target - Function to be wrapped.
         * @param {Function} wrapper - Wrapper function.
         * @returns {Function} Wrapper function.
         */
        wrap: function (target, wrapper) {
            if (!_.isFunction(target) || !_.isFunction(wrapper)) {
                return wrapper;
            }

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
         * Checks wether the incoming method contains calls of the '_super' method.
         *
         * @param {Function} fn - Function to be checked.
         * @returns {Boolean}
         */
        hasSuper: function (fn) {
            return _.isFunction(fn) && superReg.test(fn);
        },

        /**
         * Extends target object with provided extenders.
         * If property in target and extender objects is a function,
         * then it will be wrapped using 'wrap' method.
         *
         * @param {Object} target - Object to be extended.
         * @param {...Object} extenders - Multiple extenders objects.
         * @returns {Object} Modified target object.
         */
        extend: function (target) {
            var extenders = _.toArray(arguments).slice(1),
                iterator = this._extend.bind(this, target);

            extenders.forEach(iterator);

            return target;
        },

        /**
         * Same as the 'extend' method, but operates only on one extender object.
         *
         * @private
         * @param {Object} target
         * @param {Object} extender
         */
        _extend: function (target, extender) {
            _.each(extender, function (value, key) {
                target[key] = this.wrap(target[key], extender[key]);
            }, this);
        }
    };
});
