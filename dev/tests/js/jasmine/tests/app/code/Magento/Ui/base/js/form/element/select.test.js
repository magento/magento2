/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
define([
    'squire'
], function (Squire) {
    'use strict';

    describe('Magento_Ui/js/form/element/select', function () {
        var injector = new Squire(),
            mocks = {
                'Magento_Ui/js/lib/registry/registry': {
                    /** Method stub. */
                    get: function () {
                        return {
                            get: jasmine.createSpy(),
                            set: jasmine.createSpy()
                        };
                    },
                    options: jasmine.createSpy(),
                    create: jasmine.createSpy(),
                    set: jasmine.createSpy(),
                    async: jasmine.createSpy()
                },
                '/mage/utils/wrapper': jasmine.createSpy(),
                'Magento_Ui/js/core/renderer/layout': jasmine.createSpy()
            },
            dataScope = 'select',
            params = {
                provider: 'provName',
                name: '',
                index: '',
                dataScope: dataScope
            },
            model;

        beforeEach(function (done) {
            injector.mock(mocks);
            injector.require([
                'Magento_Ui/js/form/element/select',
                'knockoutjs/knockout-es5',
                'Magento_Ui/js/lib/knockout/extender/observable_array'
            ], function (Constr) {
                model = new Constr(params);

                done();
            });
        });

        describe('initialize method', function () {
            it('check for existing', function () {
                expect(model).toBeDefined();
            });
            it('check for chainable', function () {
                spyOn(model, 'initFilter');
                expect(model.initialize(params)).toEqual(model);
                expect(model.initFilter).not.toHaveBeenCalled();
            });
            it('check for call initInput', function () {
                spyOn(model, 'initFilter');
                model.customEntry = true;
                expect(model.initialize(params)).toEqual(model);
                expect(model.initFilter).not.toHaveBeenCalled();
            });
            it('check for call initFilter', function () {
                spyOn(model, 'initFilter');
                model.filterBy = true;
                expect(model.initialize(params)).toEqual(model);
                expect(model.initFilter).toHaveBeenCalled();
            });
        });
        describe('initConfig method', function () {
            it('check for chainable', function () {
                expect(model.initConfig({})).toEqual(model);
            });
        });
        describe('initObservable method', function () {
            it('check for chainable', function () {
                expect(model.initObservable({})).toEqual(model);
            });
            it('check for options', function () {
                spyOn(model, 'setOptions');
                expect(model.initObservable({})).toEqual(model);
                expect(model.setOptions).toHaveBeenCalled();
                expect(model.options()).toEqual([]);
            });
        });
        describe('initFilter method', function () {
            it('check for filter', function () {
                spyOn(model, 'filter');
                spyOn(model, 'setLinks');
                model.filterBy = {
                    field: true
                };
                expect(model.initFilter()).toEqual(model);
                expect(model.filter).toHaveBeenCalled();
                expect(model.setLinks).toHaveBeenCalled();
            });
        });
        describe('initInput method', function () {
            it('check for chainable', function () {
                expect(model.initInput()).toEqual(model);
            });
        });
        describe('getOption method', function () {
            it('check existed option', function () {
                model.indexedOptions = {
                    value: 'option'
                };
                expect(model.getOption('value')).toEqual('option');
            });

            it('check not existed option', function () {
                expect(model.getOption('value')).not.toBeDefined();
            });

            it('check empty value', function () {
                model.indexedOptions = {
                    value: 'option'
                };
                expect(model.getOption('')).not.toBeDefined();
            });
        });
        describe('normalizeData method', function () {
            it('check on non empty value', function () {
                spyOn(model, 'getOption').and.callThrough();
                model.indexedOptions = {
                    val: {
                        value: 'value'
                    }
                };
                expect(model.normalizeData('val')).toEqual('value');
                expect(model.getOption).toHaveBeenCalledWith('val');
            });
            it('check on not existed option value', function () {
                expect(model.normalizeData('value')).not.toBeDefined();
            });
            it('check on empty value', function () {
                model.options = [{
                        value: 'valFirst'
                    },
                    {
                        value: 'valLast'
                    }];
                model.caption('');
                expect(model.normalizeData('')).toEqual('valFirst');
            });
        });
        describe('getInitialValue method', function () {
            it('check on non empty value', function () {
                model.value('val');
                model.indexedOptions = {
                    val: {
                        value: 'value'
                    }
                };
                spyOn(model, 'normalizeData').and.callThrough();
                expect(model.getInitialValue()).toEqual('value');
                expect(model.normalizeData).toHaveBeenCalledWith('val');
            });
            it('check on empty value', function () {
                model.options = [{
                    label: 'Label',
                    value: 'Value'
                }];
                expect(model.getInitialValue()).toEqual('Value');
            });
        });
        describe('filter method', function () {
            it('check for filter', function () {
                spyOn(model, 'setOptions');
                model.filter('Value', 'Name');
                expect(model.setOptions).toHaveBeenCalled();
            });
        });
        describe('toggleInput method', function () {
            it('check for toggling', function () {
                expect(model.toggleInput()).toEqual(undefined);
            });
        });
        describe('setOptions method', function () {
            it('check for chainable', function () {
                expect(model.setOptions([])).toEqual(model);
            });
            it('check for default customEntry', function () {
                var data = [{
                    value: 'First'
                }, {
                    value: 'Second'
                }];

                spyOn(model, 'setVisible');
                spyOn(model, 'toggleInput');
                expect(model.setOptions(data)).toEqual(model);
                expect(model.setVisible).not.toHaveBeenCalled();
                expect(model.toggleInput).not.toHaveBeenCalled();
            });
            it('check for customEntry', function () {
                var data = [{
                    value: 'First'
                }, {
                    value: 'Second'
                }];

                model.customEntry = true;
                spyOn(model, 'setVisible');
                spyOn(model, 'toggleInput');
                expect(model.setOptions(data)).toEqual(model);
                expect(model.setVisible).toHaveBeenCalled();
                expect(model.toggleInput).toHaveBeenCalled();
            });
            it('Check call "parseOptions" method without predefined "captionValue" property', function () {
                var data = [{
                        value: null,
                        label: 'label'
                    }, {
                        value: 'value'
                    }];

                model.options = jasmine.createSpy();
                model.caption = jasmine.createSpy().and.returnValue(false);

                model.setOptions(data);
                expect(model.options).toHaveBeenCalledWith([{
                    value: 'value'
                }]);
                expect(model.caption.calls.allArgs()).toEqual([[], ['label']]);

            });
            it('Check call "parseOptions" method with predefined "captionValue" property', function () {
                var data = [{
                        value: 'value',
                        label: 'label'
                    }];

                model.options = jasmine.createSpy();
                model.caption = jasmine.createSpy().and.returnValue(false);
                model.captionValue = 'value';

                model.setOptions(data);
                expect(model.options).toHaveBeenCalledWith([]);
                expect(model.caption.calls.allArgs()).toEqual([[], ['label']]);

            });
        });
        describe('getPreview method', function () {
            it('check for default preview', function () {
                expect(model.getPreview()).toEqual('');
            });
            it('check with options', function () {
                var expected = {
                    value: 'some',
                    label: 'Label'
                };

                model.indexedOptions = {
                    some: expected
                };
                model.value(expected.value);
                expect(model.getPreview()).toEqual(expected.label);
                expect(model.preview()).toEqual(expected.label);
            });
        });
    });
});
