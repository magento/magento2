/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* eslint-disable max-nested-callbacks */
/*jscs:disable jsDoc*/

define([
    'Magento_Ui/js/lib/logger/entry',
    'Magento_Ui/js/lib/logger/levels-pool'
], function (Entry, levelsPoll) {
    'use strict';

    var levels = levelsPoll.getLevels();

    describe('Magento_Ui/js/lib/logger/entry', function () {
        describe('constructor', function () {
            it('has the "level" field', function () {
                var entry = new Entry('message', levels.INFO, {});

                expect(entry.level).toBe(levels.INFO);
            });

            it('contains name of the provided level', function () {
                var entry;

                spyOn(levelsPoll, 'getNameByCode').and.callFake(function () {
                    return 'level\'s name';
                });

                entry = new Entry('message', levels.INFO, {});

                expect(entry.levelName).toBe('level\'s name');
            });

            it('has the "message" field', function () {
                var entryMessage = 'entry message',
                    entry = new Entry(entryMessage, levels.INFO, {});

                expect(entry.message).toBe(entryMessage);
            });

            it('doesn\'t interpolate provided message', function () {
                var entryMessage = '${ $.customData }',
                    entry = new Entry(entryMessage, levels.INFO, {
                        customData: 'foo'
                    });

                expect(entry.message).toBe(entryMessage);
            });

            it('has the "data" field', function () {
                var entryData = {},
                    entry = new Entry('message', levels.INFO, entryData);

                expect(entry.data).toBe(entryData);
            });

            it('has the "timestamp" field', function () {
                var entry = new Entry('message', levels.INFO, {});

                expect(entry.timestamp).not.toBeLessThan(0);
                expect(entry.timestamp).toBeLessThan(Date.now() + 1);
            });
        });
    });
});
