/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'uiLayout'
], function (_, layout) {
    'use strict';

    describe('Magento_Ui/js/core/layout', function () {
        var layoutObj;

        beforeEach(function () {
            layoutObj = layout;
        });
        it('is executable', function () {
            expect(typeof layoutObj).toEqual('function');
        });
    });
});
