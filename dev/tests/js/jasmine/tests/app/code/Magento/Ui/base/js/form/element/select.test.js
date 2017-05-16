/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/

define([
    'Magento_Ui/js/form/element/select'
], function (SelectElement) {
    'use strict';

    describe('Magento_Ui/js/form/element/select', function () {
        var params, model;

        beforeEach(function () {
            params = {
                dataScope: 'select'
            };
            model = new SelectElement(params);
        });

        describe('initialize method', function () {
            it('check for existing', function () {
                expect(model).toBeDefined();
            });
            it('check for chainable', function () {
                spyOn(model, 'initInput');
                spyOn(model, 'initFilter');
                expect(model.initialize(params)).toEqual(model);
                expect(model.initInput).not.toHaveBeenCalled();
                expect(model.initFilter).not.toHaveBeenCalled();
            });
            it('check for call initInput', function () {
                spyOn(model, 'initInput');
                spyOn(model, 'initFilter');
                model.customEntry = true;
                expect(model.initialize(params)).toEqual(model);
                expect(model.initInput).toHaveBeenCalled();
                expect(model.initFilter).not.toHaveBeenCalled();
            });
            it('check for call initFilter', function () {
                spyOn(model, 'initInput');
                spyOn(model, 'initFilter');
                model.filterBy = true;
                expect(model.initialize(params)).toEqual(model);
                expect(model.initInput).not.toHaveBeenCalled();
                expect(model.initFilter).toHaveBeenCalled();
            });
        });
        describe('initConfig method', function () {
            it('check for chainable', function () {
                expect(model.initConfig({})).toEqual(model);
            });
            it('check with empty value and caption', function () {
                var config = {
                    options: [{
                        label: 'Caption',
                        value: null
                    }, {
                        label: 'Some label',
                        value: 'Some value'
                    }],
                    caption: 'Main caption'
                },
                expected = {
                    options: [config.options[1]],
                    caption: config.caption
                };

                expect(model.initConfig(config)).toEqual(model);
                expect(config).toEqual(expected);
            });
            it('check with empty value', function () {
                var config = {
                        options: [{
                            label: 'Caption',
                            value: null
                        }, {
                            label: 'Some label',
                            value: 'Some value'
                        }]
                    },
                    expected = {
                        options: [config.options[1]],
                        caption: config.options[0].label
                    };

                expect(model.initConfig(config)).toEqual(model);
                expect(config).toEqual(expected);
            });
            it('check with multiple empty value', function () {
                var config = {
                        options: [{
                            label: 'Caption',
                            value: null
                        }, {
                            label: 'Some label',
                            value: 'Some value'
                        }, {
                            label: 'Another caption',
                            value: null
                        }]
                    },
                    expected = {
                        options: [config.options[1]],
                        caption: config.options[0].label
                    };

                expect(model.initConfig(config)).toEqual(model);
                expect(config).toEqual(expected);
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
