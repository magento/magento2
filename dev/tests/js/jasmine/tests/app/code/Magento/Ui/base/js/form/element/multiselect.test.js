/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/

define([
    'Magento_Ui/js/form/element/multiselect'
], function (MultiselectElement) {
    'use strict';

    describe('Magento_Ui/js/form/element/multiselect', function () {
        var params, model;

        beforeEach(function () {
            params = {
                dataScope: 'multiselect'
            };
            model = new MultiselectElement(params);
        });

        describe('getInitialValue method', function () {
            it('check for default', function () {
                expect(model.getInitialValue()).toEqual(undefined);
            });
            it('check with default value', function () {
                model.indexedOptions = {
                    Select: {
                        value: 'value'
                    }
                };
                model.default = 'Select';
                expect(model.getInitialValue()).toEqual(['value']);
            });
            it('check with value', function () {
                model.indexedOptions = {
                    Select: {
                        value: 'value'
                    }
                };
                model.value('Select');
                expect(model.getInitialValue()).toEqual(['value']);
            });
        });
    });
});
