/*
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*eslint-disable max-nested-callbacks*/
/*jscs:disable jsDoc*/
define([
    'jquery',
    'squire',
    'jquery/validate'
], function ($, Squire) {
    'use strict';

    var formEl,
        jQueryAjax,
        order,
        confirmSpy = jasmine.createSpy('confirm'),
        tmpl = '<form id="edit_form" action="/">' +
                '<section id="order-methods">' +
                    '<div id="order-billing_method"></div>' +
                    '<div id="order-shipping_method"></div>' +
                '</section>' +
                '<div id="order-billing_method_form">' +
                    '<input id="p_method_payment1" type="radio" name="payment[method]" value="payment1"/>' +
                    '<fieldset id="payment_form_payment1">' +
                        '<input type="number" name="payment[cc_number]"/>' +
                        '<input type="number" name="payment[cc_cid]"/>' +
                    '</fieldset>' +
                    '<input id="p_method_payment2" type="radio" name="payment[method]" value="payment2"/>' +
                    '<fieldset id="payment_form_payment2">' +
                        '<input type="number" name="payment[cc_number]"/>' +
                        '<input type="number" name="payment[cc_cid]"/>' +
                    '</fieldset>' +
                    '<input id="p_method_free" type="radio" name="payment[method]" value="free"/>' +
                '</div>' +
            '<button id="submit_order_top_button" type="button">Submit Order</button>' +
            '</form>';

    $.widget('magetest.testPaymentMethodA', {
        options: {
            code: null,
            orderSaveUrl: null,
            orderFormSelector: null
        },

        _create: function () {
            var $editForm = $(this.options.orderFormSelector);

            $editForm.off('changePaymentMethod.' + this.options.code)
                .on('changePaymentMethod.' + this.options.code, this._onChangePaymentMethod.bind(this));
        },

        _onChangePaymentMethod: function (event, method) {
            var $editForm = $(this.options.orderFormSelector);

            $editForm.off('beforeSubmitOrder.' + this.options.code);

            if (method === this.options.code) {
                $editForm.on('beforeSubmitOrder.' + this.options.code, this._submitOrder.bind(this));
            }
        },

        _submitOrder: function (event) {
            $.ajax({
                url: this.options.orderSaveUrl,
                type: 'POST',
                context: this,
                data: {
                    code: this.options.code
                },
                dataType: 'JSON'
            });
            event.stopImmediatePropagation();

            return false;
        }

    });

    $.widget('magetest.testPaymentMethodB', $.magetest.testPaymentMethodA, {
        isActive: false,
        _onChangePaymentMethod: function (event, method) {
            var $editForm = $(this.options.orderFormSelector),
                isActive = method === this.options.code;

            if (this.isActive !== isActive) {
                this.isActive = isActive;

                if (!isActive) {
                    $editForm.off('submitOrder.' + this.options.code);
                } else {
                    $editForm.off('submitOrder')
                        .on('submitOrder.' + this.options.code, this._submitOrder.bind(this));
                }
            }
        }
    });

    function init(config) {
        config = config || {};
        order = new window.AdminOrder({});
        $(formEl).validate({});
        $(formEl).find(':radio[value="payment1"]').testPaymentMethodA({
            code: 'payment1',
            orderSaveUrl: '/admin/sales/order/create/payment_method/payment1',
            orderFormSelector: '#' + formEl.id
        });
        $(formEl).find(':radio[value="payment2"]').testPaymentMethodB({
            code: 'payment2',
            orderSaveUrl: '/admin/sales/order/create/payment_method/payment2',
            orderFormSelector: '#' + formEl.id
        });
        $(formEl).off('realOrder').on('realOrder', function () {
            $.ajax({
                url: '/admin/sales/order/create',
                type: 'POST',
                context: this,
                data: $(this).serializeArray(),
                dataType: 'JSON'
            });
        });

        if (config.method) {
            $(formEl).find(':radio[value="' + config.method + '"]').prop('checked', true);
            order.switchPaymentMethod(config.method);
        }
    }

    describe('Magento_Sales/order/create/scripts', function () {
        var injector = new Squire(),
            mocks = {
                'jquery': $,
                'Magento_Catalog/catalog/product/composite/configure': jasmine.createSpy(),
                'Magento_Ui/js/modal/confirm': confirmSpy,
                'Magento_Ui/js/modal/alert': jasmine.createSpy(),
                'Magento_Ui/js/lib/view/utils/async': jasmine.createSpy()
            };

        beforeEach(function (done) {
            jQueryAjax = $.ajax;
            injector.mock(mocks);
            injector.require(['Magento_Sales/order/create/scripts'], function () {
                window.FORM_KEY = window.FORM_KEY || '61d0c9da0aa473d214f61913967cc0ea';
                $(tmpl).appendTo(document.body);
                formEl = document.getElementById('edit_form');
                $(formEl).off();
                done();
            });
        });

        afterEach(function () {
            try {
                injector.clean();
                injector.remove();
            } catch (e) {
            }
            $(formEl).off().remove();
            formEl = undefined;
            order = undefined;
            $.ajax = jQueryAjax;
            jQueryAjax = undefined;
        });

        describe('Testing syncAddressField method', function () {
            it('Synchronize region and region_id fields display when called with field named "country"', function () {
                let form, billingCountryId, billingRegionId, billingRegion, billingCountryIdOption1,
                    billingCountryIdOption2, shippingCountryId, shippingRegionId, shippingRegion, billingRegionOption1,
                    billingRegionOption2, shippingCountryIdOption1, shippingOption2, shippingRegionOption1,
                    shippingRegionOption2;

                form = document.createElement('form');

                //create billing country id field
                billingCountryId = document.createElement('select');
                billingCountryId.name = 'order[billing_address][country_id]';
                billingCountryIdOption1 = document.createElement('option');
                billingCountryIdOption1.value = 'USA';
                billingCountryIdOption1.innerText = 'United States of America';
                billingCountryIdOption2 = document.createElement('option');
                billingCountryIdOption2.value = 'RO';
                billingCountryIdOption2.innerText = 'Romania';
                billingCountryId.appendChild(billingCountryIdOption1);
                billingCountryId.appendChild(billingCountryIdOption2);
                form.appendChild(billingCountryId);

                //create billing region id field
                billingRegionId = document.createElement('select');
                billingRegionId.name = 'order[billing_address][region_id]';
                billingRegionId.id = 'order-billing_address_region_id';
                billingRegionOption1 = document.createElement('option');
                billingRegionOption1.value = 'NY';
                billingRegionOption1.innerText = 'New York';
                billingRegionOption2 = document.createElement('option');
                billingRegionOption2.value = 'TX';
                billingRegionOption2.innerText = 'Texas';
                billingRegionId.appendChild(billingRegionOption1);
                billingRegionId.appendChild(billingRegionOption2);
                form.appendChild(billingRegionId);

                //create hidden billing region field
                billingRegion = document.createElement('input');
                billingRegion.name = 'order[billing_address][region]';
                billingRegion.id = 'order-billing_address_region';
                billingRegion.style.display = 'none';
                form.appendChild(billingRegion);

                //create shipping country id field
                shippingCountryId = document.createElement('select');
                shippingCountryId.name = 'order[shipping_address][country_id]';
                shippingCountryIdOption1 = document.createElement('option');
                shippingCountryIdOption1.value = 'USA';
                shippingCountryIdOption1.innerText = 'United States of America';
                shippingOption2 = document.createElement('option');
                shippingOption2.value = 'RO';
                shippingOption2.innerText = 'Romania';
                shippingCountryId.appendChild(shippingCountryIdOption1);
                shippingCountryId.appendChild(shippingOption2);
                shippingCountryId.value = 'RO';
                form.appendChild(shippingCountryId);

                //create shipping region id field
                shippingRegionId = document.createElement('select');
                shippingRegionId.name = 'order[shipping_address][region_id]';
                shippingRegionId.id = 'order-shipping_address_region_id';
                shippingRegionOption1 = document.createElement('option');
                shippingRegionOption1.value = 'B';
                shippingRegionOption1.innerText = 'Bucuresti';
                shippingRegionOption2 = document.createElement('option');
                shippingRegionOption2.value = 'CT';
                shippingRegionOption2.innerText = 'Constanta';
                shippingRegionId.appendChild(shippingRegionOption1);
                shippingRegionId.appendChild(shippingRegionOption2);
                form.appendChild(shippingRegionId);

                //create shipping region field
                shippingRegion = document.createElement('input');
                shippingRegion.name = 'order[shipping_address][region]';
                shippingRegion.id = 'order-shipping_address_region';
                form.appendChild(shippingRegion);

                document.body.appendChild(form);
                order = new window.AdminOrder({});
                order.syncAddressField(form, 'order[billing_address][country_id]', billingCountryId);

                expect(shippingCountryId.value).toEqual('USA');
                expect(shippingRegion.style.display).toEqual('none');
            });
        });

        it('test that setStoreId calls loadArea with a callback', function () {
            init();
            spyOn(order, 'loadArea').and.callFake(function () {
                expect(arguments.length).toEqual(4);
                expect(arguments[3] instanceof Function).toBeTrue();
            });
            order.setStoreId('id');
            expect(order.loadArea).toHaveBeenCalled();
        });

        describe('Testing the process customer group change', function () {
            it('and confirm method is called', function () {
                init();
                spyOn(window, '$$').and.returnValue(['testing']);
                order.processCustomerGroupChange(
                    1,
                    'testMsg',
                    'customerGroupMsg',
                    'errorMsg',
                    1,
                    'change'
                );
                expect(confirmSpy).toHaveBeenCalledTimes(1);
            });
        });

        describe('submit()', function () {
            function testSubmit(currentPaymentMethod, paymentMethod, ajaxParams) {
                $.ajax = jasmine.createSpy('$.ajax');
                init({
                    method: currentPaymentMethod
                });
                $(formEl).find(':radio[value="' + paymentMethod + '"]').prop('checked', true);
                order.switchPaymentMethod(paymentMethod);
                order.submit();
                expect($.ajax).toHaveBeenCalledTimes(1);
                expect($.ajax).toHaveBeenCalledWith(jasmine.objectContaining(ajaxParams));
            }

            it('Check that payment custom handler is executed #1', function () {
                testSubmit(
                    null,
                    'payment1',
                    {
                        url: '/admin/sales/order/create/payment_method/payment1',
                        data: {
                            code: 'payment1'
                        }
                    }
                );
            });

            it('Check that payment custom handler is executed #2', function () {
                testSubmit(
                    'payment1',
                    'payment1',
                    {
                        url: '/admin/sales/order/create/payment_method/payment1',
                        data: {
                            code: 'payment1'
                        }
                    }
                );
            });

            it('Check that payment custom handler is executed #3', function () {
                testSubmit(
                    null,
                    'payment2',
                    {
                        url: '/admin/sales/order/create/payment_method/payment2',
                        data: {
                            code: 'payment2'
                        }
                    }
                );
            });

            it('Check that payment custom handler is executed #4', function () {
                testSubmit(
                    'payment2',
                    'payment2',
                    {
                        url: '/admin/sales/order/create/payment_method/payment2',
                        data: {
                            code: 'payment2'
                        }
                    }
                );
            });

            it('Check that native handler is executed for payment without custom handler #1', function () {
                testSubmit(
                    'payment1',
                    'free',
                    {
                        url: '/admin/sales/order/create',
                        data: [
                            {
                                name: 'payment[method]',
                                value: 'free'
                            }
                        ]
                    }
                );
            });

            it('Check that native handler is executed for payment without custom handler #2', function () {
                testSubmit(
                    'payment2',
                    'free',
                    {
                        url: '/admin/sales/order/create',
                        data: [
                            {
                                name: 'payment[method]',
                                value: 'free'
                            }
                        ]
                    }
                );
            });
        });

        describe('Check that payment custom handler is executed and button states', function () {
            let $submitButton;

            function testSubmit(currentPaymentMethod, paymentMethod, ajaxParams) {
                $.ajax = jasmine.createSpy('$.ajax');
                init({
                    method: currentPaymentMethod
                });
                $(formEl).find(':radio[value="' + paymentMethod + '"]').prop('checked', true);
                order.switchPaymentMethod(paymentMethod);

                spyOn($.prototype, 'trigger').and.callThrough();
                order.submit();

                $submitButton = $('#submit_order_top_button');
                expect($.ajax).toHaveBeenCalledTimes(1);
                expect($.ajax).toHaveBeenCalledWith(jasmine.objectContaining(ajaxParams));

                expect($.prototype.trigger).toHaveBeenCalledWith(
                    jasmine.objectContaining({ type: 'beforeSubmitOrder' }));

                if (paymentMethod !== 'payment1') {
                    $.prototype.trigger.and.callFake(function (event) {
                        if (event.type === 'beforeSubmitOrder') {
                            event.result = false;
                        }
                    });
                    expect($submitButton.prop('disabled')).toBe(true);
                } else {
                    expect($submitButton.prop('disabled')).toBe(false);

                }
            }

            it('Check that payment custom handler is executed and button states #1', function () {
                testSubmit(
                    null,
                    'payment1',
                    {
                        url: '/admin/sales/order/create/payment_method/payment1',
                        data: {
                            code: 'payment1'
                        }
                    }
                );
            });

            it('Check that payment custom handler is executed and button states #2', function () {
                testSubmit(
                    'payment1',
                    'payment1',
                    {
                        url: '/admin/sales/order/create/payment_method/payment1',
                        data: {
                            code: 'payment1'
                        }
                    }
                );
            });

            it('Validate re-enabling the button for canceled events', function () {
                order = new window.AdminOrder({});
                spyOn(order, 'submit').and.callFake(function () {
                    const $editForm = $('#edit_form');

                    if ($editForm.valid()) {
                        $submitButton.prop('disabled', true);
                        const beforeSubmitOrderEvent = $.Event('beforeSubmitOrder');

                        $editForm.trigger(beforeSubmitOrderEvent);

                        if (beforeSubmitOrderEvent.result !== false) {
                            $editForm.trigger('submitOrder');
                        } else {
                            $submitButton.prop('disabled', false);
                        }
                    }
                });
                spyOn($.prototype, 'trigger').and.callFake(function (event) {
                    if (event.type === 'beforeSubmitOrder') {
                        event.result = false;
                    }
                });
                $.prototype.trigger.and.callFake(function (event) {
                    if (event.type === 'beforeSubmitOrder') {
                        event.result = false;
                    }
                });
                order.submit();
                expect($submitButton.prop('disabled')).toBe(false);
            });

            it('Check button state for non-payment1 methods', function () {
                testSubmit(
                    'payment2',
                    'payment2',
                    {
                        url: '/admin/sales/order/create/payment_method/payment2',
                        data: {
                            code: 'payment2'
                        }
                    }
                );
            });
        });

    });
});
