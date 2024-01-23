/*
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*eslint-disable max-nested-callbacks*/
/*jscs:disable jsDoc*/
define([
    'jquery',
    'jquery/validate',
    'Magento_Payment/js/transparent'
], function ($) {
    'use strict';

    var containerEl,
        formEl,
        jQueryAjax;

    function init(config) {
        var defaultConfig = {
            orderSaveUrl: '/',
            gateway: 'payflowpro',
            editFormSelector: '#' + formEl.id
        };

        $(formEl).find(':radio[value="payflowpro"]').prop('checked', 'checked');
        $(formEl).transparent($.extend({}, defaultConfig, config || {}));
    }

    beforeEach(function () {
        if (!window.FORM_KEY) {
            window.FORM_KEY = '61d0c9da0aa473d214f61913967cc0ea';
        }
        $('<div id="admin_edit_order_form_container">' +
            '<form id="admin_edit_order_form" action="/">' +
                '<input type="radio" name="payment[method]" value="payflowpro"/>' +
                '<input type="radio" name="payment[method]" value="money_order"/>' +
            '</form>' +
            '</div>'
        ).appendTo(document.body);
        containerEl = document.getElementById('admin_edit_order_form_container');
        formEl = document.getElementById('admin_edit_order_form');
        jQueryAjax = $.ajax;
    });

    afterEach(function () {
        $(containerEl).remove();
        formEl = undefined;
        containerEl = undefined;
        $.ajax = jQueryAjax;
        jQueryAjax = undefined;
    });

    describe('Magento_Payment/js/transparent', function () {
        describe('beforeSubmitOrder handler', function () {
            it('is registered when selected payment method requires transparent', function () {
                init();
                expect(($._data(formEl, 'events') || {}).beforeSubmitOrder[0].type).toBe('beforeSubmitOrder');
                expect(($._data(formEl, 'events') || {}).beforeSubmitOrder[0].namespace).toBe('payflowpro');
            });
            it('is not registered when selected payment method does not require transparent', function () {
                init();
                $(formEl).find(':radio[value="money_order"]').prop('checked', 'checked');
                $(formEl).trigger('changePaymentMethod', ['money_order']);
                expect(($._data(formEl, 'events') || {}).beforeSubmitOrder).toBeUndefined();
            });
            it('returns false to prevent normal order creation', function () {
                var beforeSubmitOrderEvent;

                $.ajax = jasmine.createSpy();
                init({
                    orderSaveUrl: '/admin/paypal/transparent/requestSecureToken'
                });
                beforeSubmitOrderEvent = $.Event('beforeSubmitOrder');
                $(formEl).trigger(beforeSubmitOrderEvent);
                expect($.ajax).toHaveBeenCalledWith(jasmine.objectContaining({
                    url: '/admin/paypal/transparent/requestSecureToken',
                    type: 'post'
                }));
                expect(beforeSubmitOrderEvent.result).toBe(false);
                expect(beforeSubmitOrderEvent.isImmediatePropagationStopped()).toBe(true);
            });
        });
    });
});
