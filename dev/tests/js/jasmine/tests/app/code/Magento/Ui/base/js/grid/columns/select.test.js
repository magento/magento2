/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*eslint max-nested-callbacks: 0*/

define([
    'Magento_Ui/js/grid/columns/select'
], function (Select) {
    'use strict';

    describe('Ui/js/grid/columns/select', function () {
        var opts = [{
                label : 'a', value : 1
            }, {
                label : 'b', value : 2
            }],
            optsAsObject = {
                1 : {
                    label : 'a', value : 1
                },
                2 : {
                    label : 'b', value : 2
                },
                4 : {
                    label : 'c', value : 3
                }
            },
            select;

        beforeEach(function () {
            select = new Select();
        });

        describe('getLabel method', function () {
            it('get label while options empty', function () {
                expect(select.getLabel(2)).toBe('');
            });

            it('get label for existed value', function () {
                select.options = opts;
                expect(select.getLabel(2)).toBe('b');
            });

            it('get label for existed value in case the options are initialized as an object', function () {
                select.options = optsAsObject;
                expect(select.getLabel(3)).toBe('c');
            });
        });
    });
});
