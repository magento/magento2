/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'Magento_ConfigurableProduct/js/components/associated-product-insert-listing'
], function ($, Component) {
    'use strict';

    describe('Magento_ConfigurableProduct/js/components/associated-product-insert-listing', function () {
        var component,
            request;

        beforeEach(function () {
            request = $.Deferred();
            component = {
                params: {
                    namespace: 'configurable_associated_product_listing'
                },
                externalFiltersModifier: {
                    'entity_id': {
                        'condition_type': 'nin',
                        value: ['7', '1']
                    },
                    size: {
                        'condition_type': 'notnull'
                    }
                },
                indexField: 'entity_id',
                requestConfig: {
                    method: 'GET'
                },
                requestData: jasmine.createSpy('requestData').and.returnValue(request),
                setExternalValue: jasmine.createSpy('setExternalValue'),
                loading: jasmine.createSpy('loading'),
                onError: jasmine.createSpy('onError')
            };
        });

        /**
         * @param {Object} selections
         * @param {String} itemsType
         * @returns {Object} the params sent to the server
         */
        function load(selections, itemsType) {
            Component.prototype.updateFromServerData.call(component, selections, itemsType);

            return component.requestData.calls.mostRecent().args[0];
        }

        it('keeps the grid filters modifier when loading the rows after Select All (#41546)', function () {
            var params = load({
                excludeMode: true,
                excluded: [],
                params: {
                    filters: {
                        name: 'Test Product'
                    }
                }
            }, 'excluded');

            expect(params['filters_modifier']).toEqual(component.externalFiltersModifier);
            expect(params.filters).toEqual({
                name: 'Test Product'
            });
            expect(params.paging).toEqual({
                notLimits: 1
            });
        });

        it('adds rows unticked after Select All to the hidden ones and keeps the grid filters', function () {
            var params = load({
                excludeMode: true,
                excluded: ['12'],
                params: {
                    filters: {
                        name: 'Test Product'
                    }
                }
            }, 'excluded');

            expect(params['filters_modifier']).toEqual({
                'entity_id': {
                    'condition_type': 'nin',
                    value: ['7', '1', '12']
                },
                size: {
                    'condition_type': 'notnull'
                }
            });
            expect(params.filters).toEqual({
                name: 'Test Product'
            });
        });

        it('loads only the ticked rows', function () {
            var params = load({
                excludeMode: false,
                selected: ['12', '13'],
                params: {
                    filters: {
                        name: 'Test Product'
                    }
                }
            }, 'selected');

            expect(params['filters_modifier']).toEqual({
                'entity_id': {
                    'condition_type': 'in',
                    value: ['12', '13']
                },
                size: {
                    'condition_type': 'notnull'
                }
            });
            expect(params.filters).toEqual({});
        });

        it('does not change the grid filters modifier', function () {
            load({
                excludeMode: true,
                excluded: ['12'],
                params: {}
            }, 'excluded');

            expect(component.externalFiltersModifier['entity_id'].value).toEqual(['7', '1']);
        });

        it('sets the loaded rows as the value', function () {
            load({
                excludeMode: false,
                selected: ['12'],
                params: {}
            }, 'selected');
            request.resolve({
                items: [{
                    'entity_id': '12'
                }]
            });

            expect(component.setExternalValue).toHaveBeenCalledWith([{
                'entity_id': '12'
            }]);
            expect(component.loading).toHaveBeenCalledWith(false);
        });
    });
});
