define([
    './logger',
    './entry-factory',
    './console-output-handler',
    './formatter',
    './message-poll',
    './levels-poll',
    'Magento_Ui/js/lib/core/storage/local',
    'underscore'
], function (Logger, entryFactory, ConsoleHandler, Formatter, messagePoll, levelsPoll, storage, _) {
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
            savedLevel = storage.get(STORAGE_NAMESPACE);

        Logger.call(this, consoleHandler, entryFactory);

        if (savedLevel) {
            this.displayLevel_ = savedLevel;
        }

        this.messages = messagePoll;
        this.levels = levelsPoll.getLevels();
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
            data = data || {};
            code = message;
            message = messagePoll.getMessage(code);

            data.messageCode = code;
        }

        return Logger.prototype.createEntry_.call(this, message, level, data);
    };

    return new ConsoleLogger();
});
