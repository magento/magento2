/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
        'ko',
        'uiComponent'
    ], function (ko, Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_CheckoutAgreements/checkout/payment/checkout-agreements-link'
            },
            isVisible: window.checkoutConfig.checkoutAgreementsEnabled,
            /**
             * Opens modal window with Terms&Conditions
             */
            showAgreements: function () {
                this.elems()[0].showAgreements();
            }
        });
    }
);
