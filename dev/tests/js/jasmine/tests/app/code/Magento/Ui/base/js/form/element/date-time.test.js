/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/

define([
    'Magento_Ui/js/form/element/date',
    'mageUtils',
    'moment'
], function (DateElement, utils, moment) {
    'use strict';

    describe('Magento_Ui/js/form/element/date', function () {
        var params, model;

        beforeEach(function () {
            params = {
                dataScope: 'abstract',
                options: {
                    showsTime: true
                }
            };
            model = new DateElement(params);
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
