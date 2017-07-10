/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
define([
    'Magento_Ui/js/grid/tree-massactions'
], function (TreeMassaction) {
    'use strict';

    describe('Magento_Ui/js/grid/tree-massactions', function () {
        var model;

        beforeEach(function () {
            model = new TreeMassaction({
                actions: [{
                    type: 'availability',
                    actions: [{
                        type: 'enable'
                    }, {
                        type: 'disable'
                    }]
                }]
            });
        });
        describe('check initObservable', function () {
            it('by default will set visible for all action where exist actions', function () {
                expect(model.actions()[0].visible).toBeDefined();
                expect(model.actions()[0].visible()).toBeFalsy();
            });
            it('check when actions is absent', function () {
                model.actions([{
                    type: 'delete'
                }]);
                model.initObservable();
                expect(model.actions()[0].visible).toBeUndefined();
            });
            it('check nested level actions', function () {
                model.actions()[0].actions[0].actions = [{
                    type: 'delete'
                }];
                model.initObservable();
                expect(model.actions()[0].actions[0].visible).toBeDefined();
                expect(model.actions()[0].actions[0].visible()).toBeFalsy();
            });
            it('check reference to parent object', function () {
                expect(model.actions()[0].parent).toBe(model.actions());
            });
        });
        describe('check recursiveObserveActions', function () {
            it('set visible for all action where exist actions', function () {
                var actions = [{
                    type: 'availability',
                    actions: [{
                        type: 'delete'
                    }]
                }];

                model.recursiveObserveActions(actions);
                expect(actions[0].visible).toBeDefined();
                expect(actions[0].visible()).toBeFalsy();
            });
            it('check when actions is absent', function () {
                var actions = [{
                    type: 'delete'
                }];

                model.recursiveObserveActions(actions);
                expect(actions[0].visible).toBeUndefined();
            });
            it('check nested level actions', function () {
                var actions = [{
                    type: 'availability',
                    actions: [{
                        type: 'delete',
                        actions: [{
                            type: 'safely'
                        }]
                    }]
                }];

                model.recursiveObserveActions(actions);
                expect(actions[0].actions[0].visible).toBeDefined();
                expect(actions[0].actions[0].visible()).toBeFalsy();
            });
            it('check reference to parent object', function () {
                var actions = [{
                    type: 'availability',
                    actions: [{
                        type: 'delete'
                    }]
                }];

                model.recursiveObserveActions(actions);
                expect(actions[0].parent).toBe(actions);
            });
        });
        it('check getAction', function () {
            expect(model.getAction('availability')).toBe(model.actions()[0]);
            expect(model.getAction('availability.enable')).toBe(model.actions()[0].actions[0]);
            expect(model.getAction('absent')).toBeFalsy();
        });
        describe('check hideSubmenus', function () {
            it('with class actions', function () {
                model.actions()[0].visible(true);
                expect(model.actions()[0].visible()).toBeTruthy();
                model.hideSubmenus();
                expect(model.actions()[0].visible()).toBeFalsy();
            });
            it('with another object', function () {
                var actions = model.actions();

                actions[0].visible(true);
                expect(actions[0].visible()).toBeTruthy();
                model.hideSubmenus(actions);
                expect(actions[0].visible()).toBeFalsy();
            });
        });
        describe('check applyAction', function () {
            it('change visibility of submenu', function () {
                expect(model.actions()[0].visible()).toBeFalsy();
                expect(model.applyAction('availability')).toBe(model);
                expect(model.actions()[0].visible()).toBeTruthy();
            });
        });
    });
});
