/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'domReady!'
], function (_) {
    'use strict';

    var context     = require.s.contexts._,
        execCb      = context.execCb,
        registry    = context.registry,
        callbacks   = [],
        retries     = 10,
        updateDelay = 1,
        ready,
        update;

    /**
     * Checks if provided callback already exists in the callbacks list.
     *
     * @param {Object} callback - Callback object to be checked.
     * @returns {Boolean}
     */
    function isSubscribed(callback) {
        return !!_.findWhere(callbacks, callback);
    }

    /**
     * Checks if provided module has unresolved dependencies.
     *
     * @param {Object} module - Module to be checked.
     * @returns {Boolean}
     */
    function isPending(module) {
        return !!module.depCount;
    }

    /**
     * Checks if requirejs's registry object contains pending modules.
     *
     * @returns {Boolean}
     */
    function hasPending() {
        return _.some(registry, isPending);
    }

    /**
     * Checks if 'resolver' module is in ready
     * state and that there are no pending modules.
     *
     * @returns {Boolean}
     */
    function isReady() {
        return ready && !hasPending();
    }

    /**
     * Invokes provided callback handler.
     *
     * @param {Object} callback
     */
    function invoke(callback) {
        callback.handler.call(callback.ctx);
    }

    /**
     * Sets 'resolver' module to a ready state
     * and invokes pending callbacks.
     */
    function resolve() {
        ready = true;

        callbacks.splice(0).forEach(invoke);
    }

    /**
     * Drops 'ready' flag and runs the update process.
     */
    function tick() {
        ready = false;

        update(retries);
    }

    /**
     * Adds callback which will be invoked
     * when all of the pending modules are initiated.
     *
     * @param {Function} handler - 'Ready' event handler function.
     * @param {Object} [ctx] - Optional context with which handler
     *      will be invoked.
     */
    function subscribe(handler, ctx) {
        var callback = {
            handler: handler,
            ctx: ctx
        };

        if (!isSubscribed(callback)) {
            callbacks.push(callback);

            if (isReady()) {
                _.defer(tick);
            }
        }
    }

    /**
     * Checks for all modules to be initiated
     * and invokes pending callbacks if it's so.
     *
     * @param {Number} [retry] - Number of retries
     *      that will be used to repeat the 'update' function
     *      invokation in case if there are no pending requests.
     */
    update = _.debounce(function (retry) {
        if (!hasPending()) {
            retry ? update(--retry) : resolve();
        }
    }, updateDelay);

    /**
     * Overrides requirejs's original 'execCb' method
     * in order to track pending modules.
     *
     * @returns {*} Result of original method call.
     */
    context.execCb = function () {
        var exported = execCb.apply(context, arguments);

        tick();

        return exported;
    };

    return subscribe;
});
