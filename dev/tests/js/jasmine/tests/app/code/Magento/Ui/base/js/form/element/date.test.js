/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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

    });
});
