/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'Magento_Vault/js/view/payment/method-renderer/vault'
], function ($, VaultComponent) {
    'use strict';

    return VaultComponent.extend({
        defaults: {
            template: 'Magento_Vault/payment/form'
        },

        /**
         * @returns {String}
         */
        getToken: function () {
            return this.publicHash;
        },

        /**
         * Get last 4 digits of card
         * @returns {String}
         */
        getMaskedCard: function () {
            return this.details['cc_last_4'];
        },

        /**
         * Get expiration date
         * @returns {String}
         */
        getExpirationDate: function () {
            return this.details['cc_exp_month'] + '/' + this.details['cc_exp_year'];
        },

        /**
         * Get card type
         * @returns {String}
         */
        getCardType: function () {
            return this.details['cc_type'];
        }
    });
});
