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
            agreementsConfig = checkoutConfig ? checkoutConfig.checkoutAgreements : {},
            agreementsInputPath = '.payment-method._active div.checkout-agreements input';

        return {
            /**
             * Validate checkout agreements
             *
             * @returns {boolean}
             */
            validate: function() {
                if (!agreementsConfig.isEnabled || $(agreementsInputPath).length == 0) {
                    return true;
                }

                return $.validator.validateSingleElement(agreementsInputPath, {errorElement: 'div'});
            }
        }
    }
);
