/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
            spConfig: {
                chooseText: 'Chose an Option...',
                attributes:
                {
                    'size': {
                        options: $('<div><p class="2"></p></div>')
                    }
                },
                prices: {
                    finalPrice: {
                        amount: 12
                    }
                }
            },
            values: {
            }
        };
    });

    describe('Magento_ConfigurableProduct/js/configurable', function () {

        it('check if attribute value is possible to be set as configurable option', function () {
            expect($.mage.configurable).toBeDefined();
            widget._parseQueryParams('http://magento.com/product?color=red&size=2');
            expect(widget.options.values.size).toBe('2');
        });

        it('check if attribute value is possible to be set as option with "please select option"', function () {
            expect($.mage.configurable).toBeDefined();
            widget._fillSelect(selectElement[0]);
            expect(selectElement[0].options[0].innerHTML).toBe(widget.options.spConfig.chooseText);
            widget._parseQueryParams('http://magento.com/product?color=red&size=2');
            expect(widget.options.values.size).toBe('2');
        });
    });
});
