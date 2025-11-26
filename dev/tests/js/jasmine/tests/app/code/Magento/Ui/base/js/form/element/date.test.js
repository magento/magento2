/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'squire'
], function (Squire) {
    'use strict';

    describe('Magento_Ui/js/form/element/date', function () {
        var injector = new Squire(),
            mocks = {
                'Magento_Ui/js/lib/registry/registry': {
                    /** Method stub. */
                    get: function () {
                        return {
                            get: jasmine.createSpy(),
                            set: jasmine.createSpy()
                        };
                    },
                    create: jasmine.createSpy(),
                    set: jasmine.createSpy(),
                    async: jasmine.createSpy()
                },
                '/mage/utils/wrapper': jasmine.createSpy()
            },
            model, utils,
            dataScope = 'abstract';

        beforeEach(function (done) {
            injector.mock(mocks);
            injector.require([
                'Magento_Ui/js/form/element/date',
                'mageUtils',
                'knockoutjs/knockout-es5'
            ], function (Constr, mageUtils) {
                model = new Constr({
                    provider: 'provName',
                    name: '',
                    index: '',
                    dataScope: dataScope,
                    outputDateFormat: 'DD-MM-YYYY',
                    inputDateFormat: 'YYYY-MM-DD',
                    pickerDateTimeFormat: 'DD-MM-YYYY',
                    options: {
                        showsTime: false,
                        dateFormat: 'dd-MM-y'
                    }
                });
                utils = mageUtils;

                done();
            });
        });

        it('Check prepareDateTimeFormats function', function () {
            spyOn(utils, 'convertToMomentFormat');
            model.prepareDateTimeFormats();
            expect(utils.convertToMomentFormat).toHaveBeenCalled();
        });

        it('Check date will have correct value with different locales.', function () {
            model.value('2020-11-28');
            expect(model.getPreview()).toBe('28-11-2020');
        });

        it('Check date will have correct value with timeOnly config value.', function () {
            model.options.timeOnly = true;
            model.options.timeFormat = 'h:mm a';
            model.prepareDateTimeFormats();
            model.value('02:43:58');
            expect(model.getPreview()).toBe('2:43 am');

            model.options.timeFormat = 'HH:mm:ss';
            model.prepareDateTimeFormats();
            model.value('02:43:58');
            expect(model.getPreview()).toBe('02:43:58');

            model.options.timeFormat = 'HH:mm:ss';
            model.prepareDateTimeFormats();
            model.value('2:43 am');
            expect(model.getPreview()).toBe('02:43:00');
        });

        it('Prefers pickerDateTimeFormat for date-only display format', function () {
            // simulate UI XML providing pickerDateTimeFormat while showsTime is false
            model.options.showsTime = false;
            model.options.pickerDateTimeFormat = 'MM/dd/y';

            // Re-init to adopt option and set options.dateFormat accordingly
            model.initConfig();

            expect(model.options.dateFormat).toBe('MM/dd/y');
        });

        it('Displays value using pickerDateTimeFormat when date-only', function () {
            model.options.showsTime = false;
            // Ensure consistent IO formats for this test case
            model.outputDateFormat = 'MM/dd/y';
            model.inputDateFormat = 'MM/dd/y';
            model.pickerDateTimeFormat = 'MM/dd/y';

            // Make sure formats are prepared
            model.prepareDateTimeFormats();

            model.value('21-11-2025');
            expect(model.getPreview()).toBe('21-11-2025');
        });
    });
});
