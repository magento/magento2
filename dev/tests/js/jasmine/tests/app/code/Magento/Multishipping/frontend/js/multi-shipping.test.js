/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'squire',
    'jquery',
    'ko',
    'multiShipping'
], function (Squire, $, ko, MultiShipping) {
    'use strict';

    describe('Magento_Multishipping/js/multi-shipping', function () {
        var injector = new Squire(),
            Obj;

        describe('Check Cart data preparation process', function () {
            var customerData = ko.observable({}),
                mocks = {
                    'Magento_Customer/js/customer-data': {
                        get: jasmine.createSpy('get', function () {
                            return customerData;
                        }).and.callThrough(),
                        reload: jasmine.createSpy()
                    }
                },
                summaryCount = {};

            beforeEach(function (done) {
                injector.mock(mocks);
                injector.require(['multiShipping'], function (Instance) {
                    Obj = Instance;
                    done();
                });
            });

            afterEach(function () {
                try {
                    injector.clean();
                    injector.remove();
                } catch (e) {}

                customerData({});
            });

            it('Prepare Cart data with the same items qty', function () {
                summaryCount['summary_count'] = 0;
                customerData(summaryCount);
                new Obj({});

                expect(mocks['Magento_Customer/js/customer-data'].get).toHaveBeenCalledWith('cart');
                expect(mocks['Magento_Customer/js/customer-data'].reload).not.toHaveBeenCalled();
            });

            it('Prepare Cart data with different items qty', function () {
                summaryCount['summary_count'] = 1;
                customerData(summaryCount);
                new Obj({});

                expect(mocks['Magento_Customer/js/customer-data'].get).toHaveBeenCalledWith('cart');
                expect(mocks['Magento_Customer/js/customer-data'].reload).toHaveBeenCalledWith(['cart'], false);
            });
        });

        describe('Check Multishipping events', function () {
            var addNewAddressBtn,
                addressflag,
                canContinueBtn,
                canContinueFlag;

            beforeEach(function () {
                addNewAddressBtn = $('<button type="button" data-role="add-new-address"/>');
                addressflag = $('<input type="hidden" value="0" id="add_new_address_flag"/>');
                canContinueBtn = $('<button type="submit" data-role="can-continue" data-flag="1"/>');
                canContinueFlag = $('<input type="hidden" value="0" id="can_continue_flag"/>');
                $(document.body).append(addNewAddressBtn)
                    .append(addressflag)
                    .append(canContinueBtn)
                    .append(canContinueFlag);
            });

            afterEach(function () {
                addNewAddressBtn.remove();
                addressflag.remove();
                canContinueBtn.remove();
                canContinueFlag.remove();
            });

            it('Check add new address event', function () {
                Obj = new MultiShipping({});
                Obj.element = jasmine.createSpyObj('element', ['submit']);
                addNewAddressBtn.click();

                expect(Obj.element.submit).toHaveBeenCalled();
                expect(addressflag.val()).toBe('1');
            });

            it('Check can continue event', function () {
                Obj = new MultiShipping({});
                Obj.element = jasmine.createSpyObj('element', ['submit']);
                canContinueBtn.click();

                expect(Obj.element.submit).not.toHaveBeenCalled();
                expect(canContinueFlag.val()).toBe('1');
            });
        });
    });
});
