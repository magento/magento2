/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
/* eslint-disable max-nested-callbacks */
define([
    'Magento_Ui/js/form/components/insert-listing'
], function (Constr) {
    'use strict';

    describe('Magento_Ui/js/form/components/insert-listing', function () {
        describe('"updateFromServerData" method', function () {
            var context,
                requestedData;

            beforeEach(function () {
                requestedData = null;

                context = {
                    indexField: 'entity_id',
                    params: {},
                    requestConfig: {
                        method: 'POST'
                    },

                    /**
                     * Captures the payload the component would send to the server.
                     *
                     * @param {Object} data
                     * @returns {Object}
                     */
                    requestData: function (data) {
                        requestedData = data;

                        return {
                            /** Stub */
                            done: function () {
                                return this;
                            },

                            /** Stub */
                            fail: function () {
                                return this;
                            }
                        };
                    },

                    /** Stub */
                    setExternalValue: function () {},

                    /** Stub */
                    loading: function () {},

                    /** Stub */
                    onError: function () {}
                };
            });

            it('keeps applied grid filters when rows are excluded from the selection', function () {
                var selections = {
                    excluded: ['1', '2'],
                    selected: [],
                    excludeMode: true,
                    params: {
                        filters: {
                            name: 'Bag'
                        },
                        namespace: 'product_listing'
                    }
                };

                Constr.prototype.updateFromServerData.call(context, selections, 'excluded');

                expect(requestedData.filters).toEqual({
                    name: 'Bag'
                });
                expect(requestedData['filters_modifier']['entity_id']).toEqual({
                    'condition_type': 'nin',
                    value: ['1', '2']
                });
            });

            it('resets applied grid filters when rows are explicitly selected', function () {
                var selections = {
                    excluded: [],
                    selected: ['1', '2'],
                    excludeMode: false,
                    params: {
                        filters: {
                            name: 'Bag'
                        },
                        namespace: 'product_listing'
                    }
                };

                Constr.prototype.updateFromServerData.call(context, selections, 'selected');

                expect(requestedData.filters).toEqual({});
                expect(requestedData['filters_modifier']['entity_id']).toEqual({
                    'condition_type': 'in',
                    value: ['1', '2']
                });
            });
        });
    });
});
