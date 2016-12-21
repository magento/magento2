/**
 * Copyright © 2016 Magento. All rights reserved.
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

        it('Check onValueChange function', function () {
            spyOn(moment, 'tz').and.callThrough();
            model.onValueChange('2016-11-16 11:30 AM');
            expect(moment.tz).toHaveBeenCalled();
        });

    });
});
