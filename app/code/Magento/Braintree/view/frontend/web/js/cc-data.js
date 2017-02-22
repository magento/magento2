/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
            var formId = self.options.formId;
            var defaultPaymentFormId='co-payment-form';
            if (!$('#' + formId)) {
                formId = defaultPaymentFormId;
            }
            window.onBraintreeDataLoad = function () {

                var env;
                if (self.options.kountId) {
                    env = BraintreeData.environments.production.withId(self.options.kountId);
                } else {
                    env = BraintreeData.environments.production;
                }

                BraintreeData.setup(self.options.merchantId, formId, env);
            };

            if (formId != defaultPaymentFormId) {
                $.getScript(self.options.braintreeDataJs);
            }
        }
    });
    return $.mage.braintreeDataJs;
});
