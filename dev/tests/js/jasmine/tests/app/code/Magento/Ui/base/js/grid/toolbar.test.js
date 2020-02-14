/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
define([
    'underscore',
    'uiRegistry',
    'ko',
    'Magento_Ui/js/grid/toolbar',
    'Magento_Ui/js/lib/view/utils/dom-observer',
    'Magento_Ui/js/lib/view/utils/async'
], function (_, registry, ko, Toolbar, observer, $) {
    'use strict';

    describe('Magento_Ui/js/grid/toolbar', function () {
        var toolbarObj = new Toolbar({
                index: 'listing_top',
                dataScope: '',
                columnsProvider: 'magento',
                provider: 'provider',
                name: 'magento'
            }),
            toolbarType;

        beforeEach(function () {

        });

        describe('Test initialize method for toolbar', function () {
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

        describe('Test hide toolbar method', function () {
            it('Check toolbar hide method return same instance', function () {
                expect(toolbarObj.hide()).toEqual(toolbarObj);
            });

        });
    });
});
