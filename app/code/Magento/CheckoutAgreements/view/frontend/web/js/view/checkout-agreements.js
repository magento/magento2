/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'ko',
        'jquery',
        'uiComponent',
        'Magento_CheckoutAgreements/js/model/agreements-modal',
        'Magento_Checkout/js/model/quote'
    ],
    function (ko, $, Component, agreementsModal, quote) {
        'use strict';
        var checkoutConfig = window.checkoutConfig,
            agreementManualMode = 1,
            agreementsConfig = checkoutConfig ? checkoutConfig.checkoutAgreements : {};

        return Component.extend({
            defaults: {
                template: 'Magento_CheckoutAgreements/checkout/checkout-agreements'
            },
            isVisible: agreementsConfig.isEnabled,
            agreements: agreementsConfig.agreements,
            modalTitle: ko.observable(null),
            modalContent: ko.observable(null),
            modalWindow: null,

            selectedPaymentMethod: function() {
              return quote.paymentMethod() ? quote.paymentMethod().method : "";
            },

            /**
             * Checks if agreement required
             *
             * @param element
             */
            isAgreementRequired: function(element) {
                return element.mode == agreementManualMode;
            },

            /**
             * Show agreement content in modal
             *
             * @param element
             */
            showContent: function (element) {
                this.modalTitle(element.checkboxText);
                this.modalContent(element.content);
                agreementsModal.showModal();
            },

            /**
             * Init modal window for rendered element
             *
             * @param element
             */
            initModal: function(element) {
                agreementsModal.createModal(element);
            }
        });
    }
);
