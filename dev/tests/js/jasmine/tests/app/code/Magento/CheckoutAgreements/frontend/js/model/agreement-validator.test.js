/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['squire', 'jquery'], function (Squire, $) {
    'use strict';

    var injector = new Squire(),
        agreementsBlockSelector = '.payment-method._active div.checkout-agreements',
        agreementsBlock = $('<div class="payment-method _active"><div class="checkout-agreements"></div></div>'),
        obj,

        /**
         * @param {Boolean} isChecked
         * @return {Object}
         */
        getCheckboxMock = function (isChecked) {
            return $('<input type="checkbox" class="required-entry" name="some"/>').prop('checked', isChecked);
        };

    beforeEach(function (done) {
        window.checkoutConfig = {
            checkoutAgreements: {
                isEnabled: true
            }
        };

        $('body').append(agreementsBlock);

        injector.require(['Magento_CheckoutAgreements/js/model/agreement-validator'], function (Validator) {
            obj = Validator;
            done();
        });
    });

    afterEach(function () {
        $('.payment-method._active').remove();
    });

    describe('Magento_CheckoutAgreements/js/model/agreement-validator', function () {
        describe('"validate" method', function () {
            it('Check with non existing checkboxes', function () {
                expect(obj.validate()).toBe(true);
            });

            it('Check with unchecked checkboxes', function () {
                $(agreementsBlockSelector).html(getCheckboxMock(false));
                expect(obj.validate()).toBe(false);
            });

            it('Check with checked checkboxes', function () {
                $(agreementsBlockSelector).html(getCheckboxMock(true));
                expect(obj.validate()).toBe(true);
            });

            it('Check with several checkboxes', function () {
                $(agreementsBlockSelector).html(getCheckboxMock(true));
                $(agreementsBlockSelector).append(getCheckboxMock(false));
                expect(obj.validate()).toBe(false);

                $(agreementsBlockSelector).html(getCheckboxMock(false));
                $(agreementsBlockSelector).append(getCheckboxMock(false));
                expect(obj.validate()).toBe(false);

                $(agreementsBlockSelector).html(getCheckboxMock(true));
                $(agreementsBlockSelector).append(getCheckboxMock(true));
                expect(obj.validate()).toBe(true);
            });
        });
    });
});
