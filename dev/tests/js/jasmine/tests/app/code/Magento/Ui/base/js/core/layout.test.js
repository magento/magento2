/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
