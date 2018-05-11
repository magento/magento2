/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/

define([
    'squire'
], function (Squire) {
    'use strict';

    describe('Magento_Ui/js/form/element/date-time', function () {
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
            model, utils, moment,
            dataScope = 'abstract';

        beforeEach(function (done) {
            injector.mock(mocks);
            injector.require([
                'Magento_Ui/js/form/element/date',
                'mageUtils',
                'moment',
                'knockoutjs/knockout-es5'
            ], function (Constr, mageUtils, m) {
                model = new Constr({
                    provider: 'provName',
                    name: '',
                    index: '',
                    dataScope: dataScope,
                    options: {
                        showsTime: true
                    }
                });
                utils = mageUtils;
                moment = m;

                done();
            });
        });

        it('Check prepareDateTimeFormats function', function () {
            spyOn(utils, 'convertToMomentFormat').and.callThrough();
            model.prepareDateTimeFormats();
            expect(utils.convertToMomentFormat).toHaveBeenCalled();
        });

        it('Check onShiftedValueChange function', function () {
            spyOn(moment, 'tz').and.callThrough();
            model.onShiftedValueChange('2016-12-23 9:11 PM');
            expect(moment.tz).toHaveBeenCalled();
        });

    });
});
