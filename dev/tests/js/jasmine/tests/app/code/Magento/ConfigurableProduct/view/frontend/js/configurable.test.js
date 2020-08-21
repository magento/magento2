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
            values: {
            }
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
    });
});
