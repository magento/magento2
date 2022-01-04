/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'Magento_ConfigurableProduct/js/configurable'
], function ($, Configurable) {
    'use strict';

    var widget,
        option = '<select name=\'super_attribute[93]\'' +
            'data-selector=\'super_attribute[93]\'' +
            'data-validate=\'{required:true}\'' +
            'id=\'attribute93\'' +
            'class=\'super-attribute-select\'>' +
            '<option value=\'\'></option>' +
            '</select>',
        selectElement = $(option);

    beforeEach(function () {
        widget = new Configurable();
        widget.options = {
            settings: [
                {
                    selectedIndex: 0,
                    options: [
                        {
                            label: 'Chose an Option...'
                        },
                        {
                            label: 'red',
                            config: {
                                id: '4',
                                label: 'red',
                                products: [
                                    '4'
                                ],
                                initialLabel: 'red',
                                allowedProducts: ['4']
                            }
                        }
                    ]
                }
            ],
            priceHolderSelector: 'testSelector',
            spConfig: {
                chooseText: 'Chose an Option...',
                optionPrices: {
                    4: {
                        testPrice1: {
                            amount: 40
                        },
                        testPrice2: {
                            amount: 30
                        }
                    }
                },
                attributes:
                    {
                        'size': {
                            options: [
                                {
                                    id: '2',
                                    value: '2'
                                },
                                {
                                    id: 3,
                                    value: 'red'

                                }
                            ]
                        }
                    },
                prices: {
                    finalPrice: {
                        amount: 12
                    }
                }
            },
            values: {}
        };
    });

    describe('Magento_ConfigurableProduct/js/configurable', function () {

        it('check if attribute value is possible to be set as configurable option', function () {
            expect($.mage.configurable).toBeDefined();
            widget._parseQueryParams('size=2');
            expect(widget.options.values.size).toBe('2');
        });

        it('check that attribute value is not set if provided option does not exists', function () {
            expect($.mage.configurable).toBeDefined();
            widget._parseQueryParams('size=10');
            widget._fillSelect(selectElement[0]);
            expect(widget.options.values.size).toBe(undefined);
        });

        it('check if widget will return correct price values in case option is selected or not.', function () {
            var result;

            spyOn($.fn, 'priceBox').and.callFake(function () {
                return {
                    prices: {
                        testPrice1: {
                            amount: 10
                        },
                        testPrice2: {
                            amount: 20
                        }
                    }
                };
            });
            result = widget._getPrices().prices;
            expect(result.testPrice1.amount).toBe(0);
            expect(result.testPrice2.amount).toBe(0);

            widget.options.settings[0].selectedIndex = 1;
            result = widget._getPrices().prices;
            expect(result.testPrice1.amount).toBe(30);
            expect(result.testPrice2.amount).toBe(10);
        });
    });
});
