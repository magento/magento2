/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
define([
    'Magento_Ui/js/grid/toolbar',
    'jquery'
], function (Toolbar, $) {
    'use strict';

    describe('Magento_Ui/js/grid/toolbar', function () {
        var toolbarObj,
            toolbarType,
            originToolbar;

        beforeEach(function () {
            toolbarObj = new Toolbar({
                index: 'listing_top',
                dataScope: '',
                columnsProvider: 'magento',
                provider: 'provider',
                name: 'magento'
            });
            originToolbar = toolbarObj;
            spyOn(toolbarObj, 'waitDOMElements').and.callFake(function () {
                return $.Deferred().promise();
            });
        });

        afterEach(function () {
            toolbarObj = originToolbar;
        });

        describe('initialize toolbar', function () {
            it('Check for defined ', function () {
                expect(toolbarObj.hasOwnProperty('initialize')).toBeDefined();
            });
            it('Check method type', function () {
                toolbarType = typeof toolbarObj.initialize;
                expect(toolbarType).toEqual('function');
            });
        });

        describe('Test show toolbar method', function () {
            it('Check toolbar show method return same instance', function () {
                expect(toolbarObj.show()).toEqual(toolbarObj);
            });
        });
    });
});
