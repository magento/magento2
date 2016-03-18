/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui",
], function ($) {
    'use strict';

    $.widget('mage.braintreeDataJs', {
        options: {
            kountId: false
        },
        _create: function () {
            var self = this;
            window.onBraintreeDataLoad = function () {
                var formId = self.options.formId;
                if (!$('#' + formId)) {
                    formId = 'onestepcheckout-form';
                }
                var env;
                if (self.options.kountId) {
                    env = BraintreeData.environments.production.withId(self.options.kountId);
                } else {
                    env = BraintreeData.environments.production;
                }

                BraintreeData.setup(self.options.merchantId, formId, env);
                if (typeof(payment) !== 'undefined' && typeof(payment.addAfterInitFunction) !== "undefined") {
                    payment.addBeforeValidateFunction('braintree', function () {
                        $('#device_data').disabled = false;
                    });
                }
            };
            $.getScript(self.options.braintreeDataJs);
        }
    });
    return $.mage.braintreeDataJs;
});
