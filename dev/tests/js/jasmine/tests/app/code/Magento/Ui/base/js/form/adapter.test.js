/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

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
                var type = typeof(obj.on);

                expect(type).toEqual('function');
            });
        });
    });
});
