/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* eslint-disable max-nested-callbacks */
/*jscs:disable jsDoc*/

define([
    'Magento_Ui/js/lib/logger/logger',
    'Magento_Ui/js/lib/logger/levels-pool'
], function (Logger, levelsPoll) {
    'use strict';

    var levels = levelsPoll.getLevels(),
        entryHandler,
        entryFactory;

    function LogEntry(message, level, data) {
        this.message = message;
        this.level = level;
        this.data = data;
    }

    function createLogger() {
        entryHandler = {
            show: function () {},
            dump: function () {}
        };

        entryFactory = {
            createEntry: function (message, level, data) {
                return new LogEntry(message, level, data);
            }
        };

        return new Logger(entryHandler, entryFactory);
    }

    describe('Magento_Ui/js/lib/logger/logger', function () {
        afterEach(function () {
            entryHandler = null;
            entryFactory = null;
        });

        describe('setDisplayLevel', function () {
            it('prevents logging for levels lower than the provided one', function () {
                var logger = createLogger();

                spyOn(entryHandler, 'show');

                logger.setDisplayLevel(levels.WARN);

                logger.info('info');
                logger.warn('warn');

                expect(entryHandler.show).toHaveBeenCalledTimes(1);
                expect(entryHandler.show).toHaveBeenCalledWith(jasmine.objectContaining({
                    message: 'warn'
                }));

                logger.setDisplayLevel(levels.DEBUG);
                logger.info('info');

                expect(entryHandler.show).toHaveBeenCalledTimes(2);
                expect(entryHandler.show).toHaveBeenCalledWith(jasmine.objectContaining({
                    message: 'info'
                }));
            });

            it('allows to prevent all messages from being logged', function () {
                var logger = createLogger();

                spyOn(entryHandler, 'show');

                logger.setDisplayLevel(levels.NONE);
                logger.error('error');

                expect(entryHandler.show).not.toHaveBeenCalled();
            });

            it('allows to remove level-related constraints', function () {
                var logger = createLogger();

                spyOn(entryHandler, 'show');

                logger.setDisplayLevel(levels.ERROR);
                logger.info('info');

                expect(entryHandler.show).not.toHaveBeenCalled();

                logger.setDisplayLevel(levels.ALL);
                logger.info('info');

                expect(entryHandler.show).toHaveBeenCalledWith(jasmine.objectContaining({
                    message: 'info'
                }));
            });

            it('throws an error if provided value is not defined in the levels list', function () {
                var logger = createLogger();

                expect(function () {
                    logger.setDisplayLevel(22);
                }).toThrow();
            });
        });

        describe('addDisplayCriteria', function () {
            it('applies the criteria by which log messages may be filtered out', function () {
                var logger = createLogger(),
                    spy = jasmine.createSpy();

                spyOn(entryHandler, 'show');

                logger.setDisplayLevel(levels.ALL);
                logger.addDisplayCriteria(function (entry) {
                    spy();

                    expect(this).toBe(logger);
                    expect(entry instanceof LogEntry).toBe(true);

                    return entry.message === 'passed message';
                });

                logger.info('passed message');
                logger.info('blocked message');

                expect(spy).toHaveBeenCalledTimes(2);

                expect(entryHandler.show).toHaveBeenCalledTimes(1);
                expect(entryHandler.show).toHaveBeenCalledWith(jasmine.objectContaining({
                    message: 'passed message'
                }));
            });
        });

        describe('removeDisplayCriteria', function () {
            it('removes previously applied filter criteria', function () {
                var logger = createLogger(),
                    criteria = function () {
                        return false;
                    };

                spyOn(entryHandler, 'show');

                logger.setDisplayLevel(levels.ALL);
                logger.addDisplayCriteria(criteria);
                logger.info('blocked message');

                expect(entryHandler.show).not.toHaveBeenCalled();

                logger.removeDisplayCriteria(criteria);
                logger.info('passed message');

                expect(entryHandler.show).toHaveBeenCalledTimes(1);
                expect(entryHandler.show).toHaveBeenCalledWith(jasmine.objectContaining({
                    message: 'passed message'
                }));
            });
        });

        describe('error', function () {
            it('creates an instance of the "error" entry', function () {
                var logger = createLogger(),
                    logData = {},
                    entry;

                spyOn(entryHandler, 'show');
                spyOn(entryFactory, 'createEntry').and.callThrough();

                entry = logger.error('error message', logData);

                expect(entry instanceof LogEntry).toBe(true);
                expect(entryHandler.show).toHaveBeenCalledWith(entry);
                expect(entryFactory.createEntry).toHaveBeenCalledWith(
                    'error message',
                    levels.ERROR,
                    logData
                );
            });
        });

        describe('warn', function () {
            it('creates an instance of the "warn" entry', function () {
                var logger = createLogger(),
                    logData = {},
                    entry;

                spyOn(entryHandler, 'show');
                spyOn(entryFactory, 'createEntry').and.callThrough();

                logger.setDisplayLevel(levels.WARN);
                entry = logger.warn('warn message', logData);

                expect(entry instanceof LogEntry).toBe(true);
                expect(entryHandler.show).toHaveBeenCalledWith(entry);
                expect(entryFactory.createEntry).toHaveBeenCalledWith(
                    'warn message',
                    levels.WARN,
                    logData
                );
            });
        });

        describe('info', function () {
            it('creates an instance of the "info" entry', function () {
                var logger = createLogger(),
                    logData = {},
                    entry;

                logger.setDisplayLevel(levels.ALL);
                spyOn(entryHandler, 'show');
                spyOn(entryFactory, 'createEntry').and.callThrough();

                entry = logger.info('info message', logData);

                expect(entry instanceof LogEntry).toBe(true);
                expect(entryHandler.show).toHaveBeenCalledWith(entry);
                expect(entryFactory.createEntry).toHaveBeenCalledWith(
                    'info message',
                    levels.INFO,
                    logData
                );
            });
        });

        describe('debug', function () {
            it('creates an instance of the "debug" entry', function () {
                var logger = createLogger(),
                    logData = {},
                    entry;

                logger.setDisplayLevel(levels.ALL);
                spyOn(entryHandler, 'show');
                spyOn(entryFactory, 'createEntry').and.callThrough();

                entry = logger.debug('debug message', logData);

                expect(entry instanceof LogEntry).toBe(true);
                expect(entryHandler.show).toHaveBeenCalledWith(entry);
                expect(entryFactory.createEntry).toHaveBeenCalledWith(
                    'debug message',
                    levels.DEBUG,
                    logData
                );
            });
        });

        describe('dump', function () {
            it('displays previously collected entries', function () {
                var logger = createLogger(),
                    entries,
                    infoLog,
                    errorLog;

                spyOn(entryHandler, 'dump');

                logger.setDisplayLevel(levels.ALL);

                infoLog = logger.info('info message');
                errorLog = logger.error('error message');

                logger.dump();

                entries = entryHandler.dump.calls.mostRecent().args[0];

                expect(entries.length).toBe(2);
                expect(entries[0]).toBe(infoLog);
                expect(entries[1]).toBe(errorLog);
            });

            it('applies existing display criteria', function () {
                var logger = createLogger(),
                    entries,
                    infoLog;

                spyOn(entryHandler, 'dump');

                logger.setDisplayLevel(levels.ALL);
                logger.addDisplayCriteria(function (entry) {
                    return entry.message === 'info message';
                });

                infoLog = logger.info('info message');

                logger.error('error message');
                logger.dump();

                entries = entryHandler.dump.calls.mostRecent().args[0];

                expect(entries.length).toBe(1);
                expect(entries[0]).toBe(infoLog);
            });

            it('accepts custom display criteria', function () {
                var logger = createLogger(),
                    entries,
                    infoLog;

                spyOn(entryHandler, 'dump');

                logger.setDisplayLevel(levels.ALL);

                infoLog = logger.info('info message');

                logger.error('error message');
                logger.dump(function (entry) {
                    return entry.message === 'info message';
                });

                entries = entryHandler.dump.calls.mostRecent().args[0];

                expect(entries.length).toBe(1);
                expect(entries[0]).toBe(infoLog);
            });
        });

        describe('getEntries', function () {
            it('returns all log entries', function () {
                var logger = createLogger(),
                    entries;

                logger.setDisplayLevel(levels.ERROR);
                logger.info('info message');
                logger.error('error message');

                entries = logger.getEntries();

                expect(entries.length).toBe(2);
                expect(entries[0].message).toBe('info message');
                expect(entries[1].message).toBe('error message');
            });

            it('filters log entries by provided criteria', function () {
                var logger = createLogger(),
                    entries;

                logger.setDisplayLevel(levels.ERROR);
                logger.info('info message');
                logger.error('error message');

                entries = logger.getEntries(function (entry) {
                    return entry.message === 'info message';
                });

                expect(entries.length).toBe(1);
                expect(entries[0].message).toBe('info message');
            });
        });
    });
});
