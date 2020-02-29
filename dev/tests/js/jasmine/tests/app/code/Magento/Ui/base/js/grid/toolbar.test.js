/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* eslint-disable max-nested-callbacks */
define([
    'Magento_Ui/js/grid/toolbar'
], function (Toolbar) {
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
                expect(toolbarObj.show().visible).toBeTruthy();
                expect(typeof toolbarObj.show().visible).toEqual('boolean');
                expect(toolbarObj.show().visible).toEqual(true);
                expect(toolbarObj.show()).toEqual(toolbarObj);
            });
        });
    });
});
