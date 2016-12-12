/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/

define([
    'Magento_Ui/js/form/element/date',
    'mageUtils'
], function (DateElement, utils) {
    'use strict';

    describe('Magento_Ui/js/form/element/date', function () {
        var params, model;

        beforeEach(function () {
            params = {
                dataScope: 'abstract'
            };
            model = new DateElement(params);
        });

        it('Check convertToMomentFormat function', function () {
            var format,
                momentFormat;

            format = 'M/d/yy';
            momentFormat = 'MM/DD/YYYY';
            expect(model.convertToMomentFormat(format)).toBe(momentFormat);
        });

        it('Check prepareDateTimeFormats function', function () {
            spyOn(model, 'convertToMomentFormat');
            spyOn(utils, 'normalizeDate');
            model.prepareDateTimeFormats();
            expect(model.convertToMomentFormat).toHaveBeenCalled();
            expect(utils.normalizeDate).toHaveBeenCalled();
        });

    });
});
