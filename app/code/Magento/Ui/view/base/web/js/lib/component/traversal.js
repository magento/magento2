/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'Magento_Ui/js/lib/events'
], function (_, EventsBus) {
    'use strict';

    return _.extend({}, EventsBus, {
        /**
         * Tries to call specified method of a current component,
         * otherwise delegates attempt to its' children.
         *
         * @param {String} target - Name of the method.
         * @param [...] Arguments that will be passed to method.
         * @returns {*} Result of the method calls.
         */
        delegate: function (target) {
            var args = _.toArray(arguments);

            target = this[target];

            if (_.isFunction(target)) {
                return target.apply(this, args.slice(1));
            }

            return this._delegate(args);
        },

        /**
         * Calls 'delegate' method of all of it's children components.
         * @private
         *
         * @param {Array} args - An array of arguments to pass to the next delegation call.
         * @returns {Array} An array of delegation resutls.
         */
        _delegate: function (args) {
            var result;

            result = this.elems.map(function (elem) {
                return elem.delegate.apply(elem, args);
            });

            return _.flatten(result);
        },

        /**
         * Overrides 'EventsBus.trigger' method to implement events bubbling.
         *
         * @param {String} name - Name of the event.
         * @param [...] Any number of arguments that should be to the events' handler.
         * @returns {Boolean} False if event bubbling was canceled.
         */
        bubble: function () {
            var args = _.toArray(arguments),
                bubble = this.trigger.apply(this, args),
                result;

            if (!bubble) {
                return false;
            }

            this.containers.forEach(function (parent) {
                result = parent.bubble.apply(parent, args);

                if (result === false) {
                    bubble = false;
                }
            });

            return !!bubble;
        }
    });
});
