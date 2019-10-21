/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'uiRegistry',
    'Magento_Customer/js/form/components/form',
    'jquery'
], function (_, registry, Constr, $) {
    'use strict';

    describe('Magento_Customer/js/form/components/form', function () {

        var obj,
            originaljQueryAjax;

        beforeEach(function () {
            originaljQueryAjax = $.ajax;
            obj = new Constr({
                provider: 'provName',
                name: '',
                index: ''
            });
        });

        afterEach(function () {
            $.ajax = originaljQueryAjax;
        });

        registry.set('provName', {
            /** Stub */
            on: function () {},

            /** Stub */
            get: function () {},

            /** Stub */
            set: function () {}
        });

        describe('"deleteAddress" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('deleteAddress')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.deleteAddress;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called without arguments', function () {
                expect(obj.deleteAddress()).toBeUndefined();
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof obj.deleteAddress();

                expect(type).toEqual('undefined');
            });
            it('Should call not call ajax if arguments are empty', function () {
                $.ajax = jasmine.createSpy();

                spyOn(obj, 'deleteAddress');

                expect(obj.deleteAddress()).toBeUndefined();

                expect($.ajax).not.toHaveBeenCalled();
            });
        });
    });
});
