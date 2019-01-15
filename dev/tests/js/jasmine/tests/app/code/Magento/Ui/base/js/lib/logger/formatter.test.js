/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* eslint-disable max-nested-callbacks */
/*jscs:disable jsDoc*/

define([
    'Magento_Ui/js/lib/logger/formatter',
    'moment'
], function (Formatter, moment) {
    'use strict';

    describe('Magento_Ui/js/lib/logger/formatter', function () {
        describe('process', function () {
            it('creates a string representation of the entry using default settings', function () {
                var formatter = new Formatter(),
                    entry,
                    date;

                entry = {
                    message: 'message text',
                    levelName: 'DEBUG',
                    timestamp: Date.now()
                };

                date = moment(entry.timestamp).format('YYYY-MM-DD hh:mm:ss');

                expect(formatter.process(entry)).toBe('[' + date + '] [DEBUG] message text');
            });

            it('interpolates message of the entry', function () {
                var formatter = new Formatter(),
                    entry,
                    date;

                entry = {
                    message: 'Foo ${ $.customData }',
                    levelName: 'DEBUG',
                    timestamp: Date.now(),
                    data: {
                        customData: 'Bar'
                    }
                };

                date = moment(entry.timestamp).format('YYYY-MM-DD hh:mm:ss');

                expect(formatter.process(entry)).toBe('[' + date + '] [DEBUG] Foo Bar');
            });

            it('may use custom date format', function () {
                var dateFormat = 'MMMM Do YYYY, h:mm:ss a',
                    formatter = new Formatter(dateFormat),
                    entry,
                    date;

                entry = {
                    message: 'message text',
                    levelName: 'DEBUG',
                    timestamp: Date.now()
                };

                date = moment(entry.timestamp).format(dateFormat);

                expect(formatter.process(entry)).toBe('[' + date + '] [DEBUG] message text');
            });

            it('may use custom template', function () {
                var dateFormat = 'MMMM Do YYYY, h:mm:ss a',
                    template = '[${ $.date }]: ${ $.message } (${$.entry.levelName}-CUSTOM)',
                    formatter = new Formatter(dateFormat, template),
                    entry,
                    date;

                entry = {
                    message: 'Foo ${ $.customData }',
                    levelName: 'DEBUG',
                    timestamp: Date.now(),
                    data: {
                        customData: 'Bar'
                    }
                };

                date = moment(entry.timestamp).format(dateFormat);

                expect(formatter.process(entry)).toBe('[' + date + ']: Foo Bar (DEBUG-CUSTOM)');
            });
        });
    });
});
