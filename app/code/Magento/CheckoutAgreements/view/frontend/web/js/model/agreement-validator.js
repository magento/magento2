/**
 * Copyright © 2016 Magento. All rights reserved.
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
                var noError = true;
                if (!agreementsConfig.isEnabled || $(agreementsInputPath).length == 0) {
                    return noError;
                }

                $('.payment-method:not(._active) div.checkout-agreements input')
                    .prop('checked', false)
                    .removeClass('mage-error')
                    .siblings('.mage-error[generated="true"]').remove();

                $(agreementsInputPath).each(function() {
                    var name = $(this).attr('name');

                    var result = $('#co-payment-form').validate({
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
                    }).element(agreementsInputPath + '[name="' + name + '"]');

                    if (!result) {
                        noError = false;
                    }
                });

                return noError;
            }
        }
    }
);
