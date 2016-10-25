/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'ko',
        'jquery',
        'uiComponent',
        'Magento_CheckoutAgreements/js/model/agreements-modal'
    ],
    function (ko, $, Component, agreementsModal) {
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

            /**
             * build a unique id for the term checkbox
             *
             * @param parents
             * @param agreementId
             */
            getCheckboxId: function(parents, agreementId) {
              var paymentMethodName = (parents[2].item) ? parents[2].item.method : '';
              return 'agreement_' + paymentMethodName + '_' + agreementId;
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
