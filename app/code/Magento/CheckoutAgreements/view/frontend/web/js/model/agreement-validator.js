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
        var agreementsConfig = window.checkoutConfig.checkoutAgreements;
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

                var form = $('.payment-method._active form[data-role=checkout-agreements]');
                form.validation();
                return form.validation('isValid');
            }
        }
    }
);
