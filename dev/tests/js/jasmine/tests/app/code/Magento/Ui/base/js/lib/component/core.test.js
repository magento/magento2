/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/lib/component/core'
], function (core) {
    'use strict';

    describe('Magento_Ui/js/lib/component/core', function () {
        var coreObj,
            returnedValue;

        beforeEach(function () {
            coreObj = core;
        });
        it('has initialize', function () {
            spyOn(coreObj, 'initialize');
            coreObj.initialize();
            expect(coreObj.initialize).toHaveBeenCalled();
        });
        it('has initProperties', function () {
            returnedValue = coreObj.initProperties();
            expect(typeof returnedValue).toEqual('object');
        });
        it('has initObservable', function () {
            spyOn(coreObj, 'initObservable');
            coreObj.initObservable();
            expect(coreObj.initObservable).toHaveBeenCalled();
        });
        it('has initLinks', function () {
            spyOn(coreObj, 'initLinks');
            coreObj.initLinks();
            expect(coreObj.initLinks).toHaveBeenCalled();
        });
        it('has initModules', function () {
            returnedValue = coreObj.initModules();
            expect(typeof returnedValue).toEqual('object');
        });
        it('has initUnique', function () {
            returnedValue = coreObj.initUnique();
            expect(typeof returnedValue).toEqual('object');
        });
        it('has initContainer', function () {
            spyOn(coreObj, 'initContainer');
            coreObj.initContainer();
            expect(coreObj.initContainer).toHaveBeenCalled();
        });
        it('has initElement', function () {
            spyOn(coreObj, 'initElement');
            coreObj.initElement();
            expect(coreObj.initElement).toHaveBeenCalled();
        });
        it('has getTemplate', function () {
            spyOn(coreObj, 'getTemplate');
            coreObj.getTemplate();
            expect(coreObj.getTemplate).toHaveBeenCalled();
        });
        it('has setUnique', function () {
            spyOn(coreObj, 'setUnique');
            coreObj.setUnique();
            expect(coreObj.setUnique).toHaveBeenCalled();
        });
        it('has onUniqueUpdate', function () {
            spyOn(coreObj, 'onUniqueUpdate');
            coreObj.onUniqueUpdate();
            expect(coreObj.onUniqueUpdate).toHaveBeenCalled();
        });
    });
});
