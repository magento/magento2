/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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

        it('Check prepareDateTimeFormats function', function () {
            spyOn(utils, 'convertToMomentFormat');
            model.prepareDateTimeFormats();
            expect(utils.convertToMomentFormat).toHaveBeenCalled();
        });

    });
});
