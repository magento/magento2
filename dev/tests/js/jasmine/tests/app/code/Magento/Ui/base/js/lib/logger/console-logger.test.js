/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* eslint-disable max-nested-callbacks */
/*jscs:disable jsDoc*/

define([
    'Magento_Ui/js/lib/logger/console-logger',
    'Magento_Ui/js/lib/logger/logger',
    'Magento_Ui/js/lib/logger/entry',
    'Magento_Ui/js/lib/logger/levels-pool',
    'Magento_Ui/js/lib/logger/message-pool'
], function (logger, Logger, LogEntry, levelsPoll, messagePoll) {
    'use strict';

    var levels = levelsPoll.getLevels();

    describe('Magento_Ui/js/lib/logger/console-logger', function () {
        describe('constructor', function () {
            it('is an instance of the Logger class', function () {
                expect(logger instanceof Logger).toBe(true);
            });

            it('provides reference to the available log levels', function () {
                expect(logger.levels).toBe(levels);
            });

            it('provides reference to the poll of available messages', function () {
                expect(logger.messages).toBe(messagePoll);
            });
        });

        describe('error', function () {
            it('allows to provide code from the messages poll', function () {
                var entry;

                spyOn(Logger.prototype, 'error').and.callThrough();

                logger.setDisplayLevel(logger.levels.NONE);
                logger.messages.addMessage('ERROR_MESSAGE', 'error message');

                entry = logger.error('ERROR_MESSAGE');

                expect(entry instanceof LogEntry).toBe(true);
                expect(Logger.prototype.error).toHaveBeenCalled();
                expect(entry).toEqual(jasmine.objectContaining({
                    message: 'error message',
                    data: {
                        messageCode: 'ERROR_MESSAGE'
                    }
                }));
            });
        });

        describe('warn', function () {
            it('allows to provide code from the messages poll', function () {
                var entry;

                spyOn(Logger.prototype, 'warn').and.callThrough();

                logger.setDisplayLevel(logger.levels.NONE);
                logger.messages.addMessage('WARN_MESSAGE', 'warn message');

                entry = logger.warn('WARN_MESSAGE');

                expect(entry instanceof LogEntry).toBe(true);
                expect(Logger.prototype.warn).toHaveBeenCalled();
                expect(entry).toEqual(jasmine.objectContaining({
                    message: 'warn message',
                    data: {
                        messageCode: 'WARN_MESSAGE'
                    }
                }));
            });
        });

        describe('info', function () {
            it('allows to provide code from the messages poll', function () {
                var entry;

                spyOn(Logger.prototype, 'info').and.callThrough();

                logger.setDisplayLevel(logger.levels.NONE);
                logger.messages.addMessage('INFO_MESSAGE', 'info message');

                entry = logger.info('INFO_MESSAGE');

                expect(entry instanceof LogEntry).toBe(true);
                expect(Logger.prototype.info).toHaveBeenCalled();
                expect(entry).toEqual(jasmine.objectContaining({
                    message: 'info message',
                    data: {
                        messageCode: 'INFO_MESSAGE'
                    }
                }));
            });
        });

        describe('debug', function () {
            it('allows to provide code from the messages poll', function () {
                var entry;

                spyOn(Logger.prototype, 'debug').and.callThrough();

                logger.setDisplayLevel(logger.levels.NONE);
                logger.messages.addMessage('DEBUG_MESSAGE', 'debug message');

                entry = logger.debug('DEBUG_MESSAGE');

                expect(entry instanceof LogEntry).toBe(true);
                expect(Logger.prototype.debug).toHaveBeenCalled();
                expect(entry).toEqual(jasmine.objectContaining({
                    message: 'debug message',
                    data: {
                        messageCode: 'DEBUG_MESSAGE'
                    }
                }));
            });
        });
    });
});
