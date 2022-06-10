/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
define([
    'jquery',
    'squire',
    'underscore',
    'Magento_Sales/js/grid/tree-massactions'
], function ($, Squire, _, TreeMassaction) {
    'use strict';

    var injector = new Squire(),
        mocks = {
            'Magento_Ui/js/grid/massactions': {
                defaultCallback: jasmine.createSpy().and.returnValue({}),
                applyAction: jasmine.createSpy().and.returnValue({})
            }
        },
        obj,
        utils;

    describe('Magento_Sales/js/grid/tree-massactions', function () {
        var model;

        beforeEach(function (done) {
            injector.mock(mocks);
            injector.require([
                'Magento_Ui/js/grid/massactions',
                'mageUtils'
            ], function (instance, mageUtils) {
                obj = _.extend({}, instance);
                utils = mageUtils;
                done();
            });
            model = new TreeMassaction({
                actions: [
                    {
                        type: 'availability',
                        actions: [{
                            type: 'enable'
                        }, {
                            type: 'disable'
                        }]
                    },
                    {
                        type: 'hold_order',
                        component: 'uiComponent',
                        label: 'hold',
                        url: 'http://local.magento/hold_order',
                        modules: {
                            selections: ['1','2','3']
                        },
                        actions: [{
                            callback: 'defaultCallback'
                        }]
                    }]
            });
        });

        afterEach(function () {
            try {
                injector.clean();
                injector.remove();
            } catch (e) {}
        });

        describe('check applyAction', function () {
            it('change visibility of submenu', function () {
                expect(model.actions()[0].visible()).toBeFalsy();
                expect(model.applyAction('availability')).toBe(model);
                expect(model.actions()[0].visible()).toBeTruthy();
            });
        });
        describe('check defaultCallback', function () {
            it('check model called with action and selected data', function () {
                expect(model.applyAction('hold_order')).toBe(model);
                expect(model.actions()[1].visible()).toBeTruthy();
                expect(model.actions()[1].modules.selections).toBeTruthy();
                expect(model.actions()[1].modules.selections.total).toBeFalsy();
            });

            it('check defaultCallback submitted the data', function () {
                var action = {
                    component: 'uiComponent',
                    label: 'Hold',
                    type: 'hold_order',
                    url: 'http://local.magento/hold_order/'
                },
                    data = {
                    excludeMode: true,
                    excluded: [],
                    params: {},
                    selected: ['7', '6', '5', '4', '3', '2', '1'],
                    total: 7
                },
                    result;

                obj.getAction = jasmine.createSpy().and.returnValue('hold_order');

                obj.applyAction(action);

                result = obj.defaultCallback(action, data);

                expect(typeof result).toBe('object');
                utils.submit = jasmine.createSpy().and.callThrough();
                utils.submit({
                    url: action.url,
                    data: data.selected
                });
                expect(utils.submit).toHaveBeenCalled();
            });
        });
    });
});
