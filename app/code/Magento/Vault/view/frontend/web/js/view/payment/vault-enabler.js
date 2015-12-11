/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiElement'
    ],
    function (
        Component
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                isActivePaymentTokenEnabler: true
            },

            setPaymentCode: function (paymentCode) {
                this.paymentCode = paymentCode;
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'isActivePaymentTokenEnabler'
                    ]);
                return this;
            },

            visitAdditionalData: function(data) {
                if (!this.isVaultEnabled()) {
                    return;
                }

                data.additional_data.is_active_payment_token_enabler = this.isActivePaymentTokenEnabler();
            },

            isVaultEnabled: function() {
                return window.checkoutConfig.vault.is_enabled == '1'
                    && window.checkoutConfig.vault.vault_provider_code == this.paymentCode;
            }
        });
    }
);