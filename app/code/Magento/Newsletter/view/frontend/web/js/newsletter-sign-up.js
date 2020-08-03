/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'uiElement',
    'mage/url',
    'subscriptionStatusResolver',
    'mage/validation'
], function ($, Component, urlBuilder, subscriptionStatusResolver) {
    'use strict';

    return Component.extend({

        defaults: {
            signUpElement: '',
            submitButton: '',
            element: null
        },

        /** @inheritdoc */
        initialize: function (config, element) {
            this._super();
            this.element = element;
            $(element).on('change', $.proxy(this.updateSignUpStatus, this));
            this.updateSignUpStatus();
        },

        /**
         * Send status request and update subscription element according to result.
         */
        updateSignUpStatus: function () {
            var element = $(this.element),
                email = element.val(),
                self = this,
                newsletterSubscription;

            if ($(self.signUpElement).is(':checked')) {
                return;
            }

            if (!email || !$.validator.methods['validate-email'].call(this, email, element)) {
                return;
            }

            newsletterSubscription = $.Deferred();

            $(self.submitButton).prop('disabled', true);

            subscriptionStatusResolver(email, newsletterSubscription);

            $.when(newsletterSubscription).done(function (isSubscribed) {
                if (isSubscribed) {
                    $(self.signUpElement).prop('checked', true);
                }
            }).always(function () {
                $(self.submitButton).prop('disabled', false);
            });
        }
    });
});
