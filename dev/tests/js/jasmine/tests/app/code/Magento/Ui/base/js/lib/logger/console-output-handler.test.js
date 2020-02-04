/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* eslint-disable max-nested-callbacks */
/*jscs:disable jsDoc*/

define([
    'Magento_Ui/js/lib/logger/console-output-handler',
    'Magento_Ui/js/lib/logger/levels-pool'
], function (ConsoleHandler, levelsPoll) {
    'use strict';

    var levels = levelsPoll.getLevels(),
        formatter = {
            process: function (entry) {
                return 'Formatted message: ' + entry.message;
            }
        };

    describe('Magento_Ui/js/lib/logger/console-output-handler', function () {
        describe('show', function () {
            it('displays an entry of the "error" level', function () {
                var handler = new ConsoleHandler(formatter),
                    entry = {
                        level: levels.ERROR,
                        message: 'error'
                    };

                spyOn(console, 'error');
                spyOn(formatter, 'process').and.callThrough();

                handler.show(entry);

                expect(formatter.process).toHaveBeenCalledWith(entry);
                expect(console.error).toHaveBeenCalledWith('Formatted message: error');
            });

            it('displays an entry of the "warn" level', function () {
                var handler = new ConsoleHandler(formatter),
                    entry = {
                        level: levels.WARN,
                        message: 'warn message'
                    };

                spyOn(console, 'warn');
                spyOn(formatter, 'process').and.callThrough();

                handler.show(entry);

                expect(formatter.process).toHaveBeenCalledWith(entry);
                expect(console.warn).toHaveBeenCalledWith('Formatted message: warn message');
            });

            it('displays an entry of the "info" level', function () {
                var handler = new ConsoleHandler(formatter),
                    entry = {
                        level: levels.INFO,
                        message: 'info message'
                    };

                spyOn(console, 'info');
                spyOn(formatter, 'process').and.callThrough();

                handler.show(entry);

                expect(formatter.process).toHaveBeenCalledWith(entry);
                expect(console.info).toHaveBeenCalledWith('Formatted message: info message');
            });

            it('displays an entry of the "debug" levels', function () {
                var handler = new ConsoleHandler(formatter),
                    entry = {
                        level: levels.DEBUG,
                        message: 'debug message'
                    };

                spyOn(console, 'log');
                spyOn(formatter, 'process').and.callThrough();

                handler.show(entry);

                expect(formatter.process).toHaveBeenCalledWith(entry);
                expect(console.log).toHaveBeenCalledWith('Formatted message: debug message');
            });
        });

        describe('dump', function () {
            it('displays multiple entries', function () {
                var handler = new ConsoleHandler(formatter),
                    debugEntry = {},
                    warnEntry = {};

                spyOn(handler, 'show');

                handler.dump([debugEntry, warnEntry]);

                expect(handler.show).toHaveBeenCalledTimes(2);
                expect(handler.show.calls.argsFor(0)[0]).toBe(debugEntry);
                expect(handler.show.calls.argsFor(1)[0]).toBe(warnEntry);
            });
        });
    });
});
