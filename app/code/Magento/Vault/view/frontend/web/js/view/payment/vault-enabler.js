/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent'
    ],
    function (
        Component
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                isActivePaymentTokenEnabler: false
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'isActivePaymentTokenEnabler'
                    ]);
                return this;
            },

            visitAdditionalData: function(data, paymentProviderCode) {
                if (!this.isVaultEnabled(paymentProviderCode)) {
                    return;
                }

                data.additional_data.is_active_payment_token_enabler = this.isActivePaymentTokenEnabler();
            },

            isVaultEnabled: function(paymentProviderCode) {
                return window.checkoutConfig.vault.is_enabled == '1'
                    && window.checkoutConfig.vault.vault_provider_code == paymentProviderCode;
            }
        });
    }
);