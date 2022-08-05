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
        options = {
            'spConfig': {
                'attributes': {
                    '93': {
                        'id': '93',
                        'code': 'color',
                        'label': 'Color',
                        'options': [
                            {'id': '14', 'label': 'brown', 'products': ['2']},
                            {'id': '15', 'label': 'beige', 'products': ['3']}
                        ],
                        'position': '0'
                    }
                },
                'template': '$<%- data.price %>',
                'currencyFormat': '$%s',
                'optionPrices': {
                    '2': {
                        'baseOldPrice': {'amount': 12},
                        'oldPrice': {'amount': 12},
                        'basePrice': {'amount': 12},
                        'finalPrice': {'amount': 12},
                        'tierPrices': [{'qty': 4, 'price': 8.4, 'percentage': 30, 'excl_tax_price': 8.4}],
                        'msrpPrice': {'amount': 0}
                    },
                    '3': {
                        'baseOldPrice': {'amount': 10},
                        'oldPrice': {'amount': 10},
                        'basePrice': {'amount': 10},
                        'finalPrice': {'amount': 10},
                        'tierPrices': [],
                        'msrpPrice': {'amount': 0}
                    }
                },
                'priceFormat': {
                    'pattern': '$%s',
                    'precision': 2,
                    'requiredPrecision': 2,
                    'decimalSymbol': '.',
                    'groupSymbol': ',',
                    'groupLength': 3,
                    'integerRequired': false
                },
                'prices': {
                    'baseOldPrice': {'amount': 10},
                    'oldPrice': {'amount': 10},
                    'basePrice': {'amount': 10},
                    'finalPrice': {'amount': 10}
                },
                'productId': '4',
                'chooseText': 'Choose an Option...',
                'images': [],
                'index': { '2': {'93': '14'}, '3': {'93': '15'}},
                'salable': []
            }
        },
        blockHtml = '<form id="cart"/>'
            + '<select name=\'super_attribute[93]\'' +
            ' data-selector=\'super_attribute[93]\'' +
            ' data-validate=\'{required:true}\'' +
            ' id=\'attribute93\'' +
            ' class=\'super-attribute-select\'>' +
            '<option value=\'\'>Choose an Option...</option>' +
            '<option value=\'14\'>brown +$2.00</option>' +
            '<option value=\'15\'>beige</option>' +
            '</select>' +
            '<input id="qty"/>' +
            '</form>',
        selectElement,
        qtyElement,
        formElement;

    describe('Magento_ConfigurableProduct/js/configurable', function () {
        beforeEach(function () {
            $(blockHtml).appendTo('body');
            selectElement = $('#attribute93');
            qtyElement = $('#qty');
            formElement = $('#cart');
            widget = new Configurable($.extend(true, {}, options), formElement);
            $.fn.trigFunc = $.fn.trigger;
        });
        afterEach(function () {
            formElement.remove();
            $.fn.trigger = $.fn.trigFunc;
            delete $.fn.trigFunc;
        });
        it('check if attribute value is possible to be set as configurable option', function () {
            expect($.mage.configurable).toBeDefined();
            widget._parseQueryParams('93=14');
            expect(widget.options.values['93']).toBe('14');
        });

        it('check that attribute value is not set if provided option does not exists', function () {
            expect($.mage.configurable).toBeDefined();
            widget._parseQueryParams('93=10');
            expect(widget.options.values['93']).toBe(undefined);
        });

        it('check if widget will return correct price values in case option is selected or not.', function () {
            var result;

            spyOn($.fn, 'priceBox').and.callFake(function () {
                return {
                    prices: {
                        'baseOldPrice': {'amount': 10},
                        'oldPrice': {'amount': 10},
                        'basePrice': {'amount': 10},
                        'finalPrice': {'amount': 10},
                        'msrpPrice': {'amount': 0}
                    }
                };
            });
            result = widget._getPrices().prices;
            expect(result.baseOldPrice.amount).toBe(0);
            expect(result.oldPrice.amount).toBe(0);
            expect(result.basePrice.amount).toBe(0);
            expect(result.finalPrice.amount).toBe(0);

            selectElement.val(14);
            result = widget._getPrices().prices;
            expect(result.baseOldPrice.amount).toBe(2);
            expect(result.oldPrice.amount).toBe(2);
            expect(result.basePrice.amount).toBe(2);
            expect(result.finalPrice.amount).toBe(2);
        });

        it('check that price is reloaded on qty change', function () {
            spyOn($.fn, 'trigger');
            qtyElement.trigFunc('input');
            expect($.fn.trigger).toHaveBeenCalledWith('updatePrice', {});
        });
    });
});
