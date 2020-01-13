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
        //remove this lines when jasmine version will be upgraded
        if (!Array.prototype.find) {
            Object.defineProperty(Array.prototype, 'find', {//eslint-disable-line
                enumerable: false,
                configurable: true,
                writable: true,

                /**
                 * Find method
                 */
                value: function (predicate) {
                    var list = Object(this),
                        length = list.length >>> 0,
                        thisArg = arguments[1],
                        value,
                        i;

                    if (this == null) {
                        throw new TypeError('Array.prototype.find called on null or undefined');
                    }

                    if (typeof predicate !== 'function') {
                        throw new TypeError('predicate must be a function');
                    }

                    for (i = 0; i < length; i++) {
                        if (i in list) {
                            value = list[i];

                            if (predicate.call(thisArg, value, i, list)) {//eslint-disable-line
                                return value;
                            }
                        }
                    }

                    return undefined;
                }
            });
        }
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

        it('check that attribute value is not set id provided option does not exists', function () {
            expect($.mage.configurable).toBeDefined();
            widget._parseQueryParams('size=10');
            widget._fillSelect(selectElement[0]);
            expect(widget.options.values.size).toBe(undefined);
        });
    });
});
