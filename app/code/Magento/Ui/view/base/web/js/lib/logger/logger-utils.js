/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([], function () {
    'use strict';

    /**
     * Utils methods for logger
     * @param {Logger} logger
     */
    function LogUtils(logger) {
        this.logger = logger;

    }

    /**
     * Method for logging asynchronous operations
     * @param {Promise} promise
     * @param {Object} config
     */
    LogUtils.prototype.asyncLog = function (promise, config) {
        var levels,
            messages,
            wait;

        config = config || {};
        levels = config.levels || this.createLevels();
        messages = config.messages || this.createMessages();
        wait = config.wait || 5000;

        this.logger[levels.requested](messages.requested, config.data);
        setTimeout(function () {
            promise.state() === 'pending' ?
                this.logger[levels.failed](messages.failed, config.data) :
                this.logger[levels.loaded](messages.loaded, config.data);
        }.bind(this), wait);
    };

    /**
     * Method that creates object of messages
     * @param {String} requested - log message that showing that request for class is started
     * @param {String} loaded - log message that show when requested class is loaded
     * @param {String} failed - log message that show when requested class is failed
     * @returns {Object}
     */
    LogUtils.prototype.createMessages = function (requested, loaded, failed) {
        return {
            requested: requested || '',
            loaded: loaded || '',
            failed: failed || ''
        };
    };

    /**
     * Method that creates object of log levels
     * @param {String} requested - log message that showing that request for class is started
     * @param {String} loaded - log message that show when requested class is loaded
     * @param {String} failed - log message that show when requested class is failed
     * @returns {Object}
     */
    LogUtils.prototype.createLevels = function (requested, loaded, failed) {
        return {
            requested: requested || 'info',
            loaded: loaded || 'info',
            failed: failed || 'warn'
        };
    };

    return LogUtils;
});
