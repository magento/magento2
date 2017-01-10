/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
/*jscs:disable requirePaddingNewLinesInObjects*/
/*jscs:disable jsDoc*/

define([
    'Magento_Ui/js/form/adapter'
], function (obj) {
    'use strict';

    describe('Magento_Ui/js/form/adapter', function () {
        describe('"on" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('on')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.on;

                expect(type).toEqual('function');
            });
        });
    });
});
