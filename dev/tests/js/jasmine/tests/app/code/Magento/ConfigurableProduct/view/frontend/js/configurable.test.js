/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_ConfigurableProduct/js/configurable'
], function ($, Configurable) {
    'use strict';

    var widget;

    beforeEach(function () {
        widget = new Configurable();
        widget.options = {
            spConfig: {
                attributes:
                {
                    'size': {
                        options: $('<div><p class="2"></p></div>')
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
    });
});
