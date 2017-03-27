define([
    './logger',
    './entry-factory',
    './console-output-handler',
    './formatter',
    './message-poll',
    'Magento_Ui/js/lib/core/storage/local',
    'underscore'
], function (Logger, entryFactory, ConsoleHandler, Formatter, messagePoll, storage, _) {
    'use strict';

    var STORAGE_NAMESPACE = 'CONSOLE_LOGGER';

    /**
     * Singleton Logger's sub-class instance of which is configured to display its
     * messages to the console. It also provides the support of predefined messages
     * and persists its display level.
     */
    function ConsoleLogger() {
        var formatter = new Formatter(),
            consoleHandler = new ConsoleHandler(formatter),
            savedLevel;

        Logger.call(this, consoleHandler, entryFactory);

        savedLevel = storage.get(STORAGE_NAMESPACE);

        if (savedLevel) {
            this.displayLevel_ = savedLevel;
        }
    }

    _.extend(ConsoleLogger, Logger);

    ConsoleLogger.prototype = Object.create(Logger.prototype);
    ConsoleLogger.prototype.constructor = ConsoleLogger;

    /**
     * Overrides parent method to save the provided display level.
     *
     * @override
     */
    ConsoleLogger.prototype.setDisplayLevel = function (level) {
        Logger.prototype.setDisplayLevel.call(this, level);

        storage.set(STORAGE_NAMESPACE, level);
    };

    /**
     * Adds the support of predefined messages.
     *
     * @protected
     * @override
     */
    ConsoleLogger.prototype.createEntry_ = function (message, level, data) {
        var code;

        if (messagePoll.hasMessage(message)) {
            code = message;
            data = data || {};
            message = messagePoll.getMessage(code);

            data.messageCode = code;
        }

        return Logger.prototype.createEntry_.call(this, message, level, data);
    };

    return new ConsoleLogger();
});
