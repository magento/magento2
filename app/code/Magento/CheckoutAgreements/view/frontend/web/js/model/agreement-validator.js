/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'mage/validation'
    ],
    function ($) {
        'use strict';
        var checkoutConfig = window.checkoutConfig,
            agreementsConfig = checkoutConfig ? checkoutConfig.checkoutAgreements : {};

        var agreementsInputPath = '.payment-method._active div.checkout-agreements input';

        return {
            /**
             * Validate checkout agreements
             *
             * @returns {boolean}
             */
            validate: function() {
                if (!agreementsConfig.isEnabled) {
                    return true;
                }

                if ($(agreementsInputPath).length == 0) {
                    return true;
                }

                return $('#co-payment-form').validate({
                    errorClass: 'mage-error',
                    errorElement: 'div',
                    meta: 'validate',
                    errorPlacement: function (error, element) {
                        var errorPlacement = element;
                        if (element.is(':checkbox') || element.is(':radio')) {
                            errorPlacement = element.siblings('label').last();
                        }
                        errorPlacement.after(error);
                    }
                }).element(agreementsInputPath);
            }
        }
    }
);
