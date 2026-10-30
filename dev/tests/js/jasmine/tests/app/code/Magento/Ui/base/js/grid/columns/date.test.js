/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/*eslint max-nested-callbacks: 0*/
define([
    'underscore',
    'moment',
    'Magento_Ui/js/grid/columns/date'
], function (_, moment, Date) {
    'use strict';

    describe('Ui/js/grid/columns/date', function () {
        var date;

        beforeEach(function () {
            date = new Date({
                dataScope: 'abstract'
            });
        });

        describe('initConfig method', function () {
            it('check for chainable', function () {
                expect(date.initConfig()).toEqual(date);
            });
            it('check for extend', function () {
                date.initConfig();
                expect(date.dateFormat).toBeDefined();
            });
        });

        describe('getLabel method', function () {
            it('uses moment.updateLocale when storeLocale is defined', function () {
                var value,
                    label;

                date.storeLocale = 'en_US';
                date.calendarConfig = {
                    week: { dow: 1 }
                };
                date.index = 'created_at';

                date._super = function () {
                    return '2025-11-18 15:30:00';
                };

                value = {
                    created_at: '2025-11-18 15:30:00'
                };

                spyOn(moment, 'updateLocale').and.callThrough();

                label = date.getLabel(value, 'YYYY-MM-DD');

                expect(moment.updateLocale).toHaveBeenCalledWith(
                    'en_US',
                    jasmine.any(Object)
                );
                expect(label).toBe('2025-11-18');
            });
        });
    });
});
