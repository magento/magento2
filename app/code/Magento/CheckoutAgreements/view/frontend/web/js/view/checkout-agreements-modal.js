/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
        'ko',
        'uiComponent',
        'Magento_Ui/js/modal/modal'
    ], function (ko, Component, modal) {
        'use strict';

        /**
         * Provides an empty div to force load template into.
         * @todo MAGETWO-39170 Refactor this as soon as modal component is updated to support ko view models.
         * @returns {HTMLElement}
         */
        function createTemporayContainer() {
            var temporaryDiv = document.createElement('div');
            temporaryDiv.style.display = 'none';
            document.body.appendChild(temporaryDiv);

            return temporaryDiv;
        }

        return Component.extend({
            defaults: {
                template: 'Magento_CheckoutAgreements/checkout/modal/agreements-modal'
            },
            /**
             * Initialize view and render it's template to temporary div. This will be refactored as soon as
             * modal component is integrated with uiComponent.
             * @returns {*}
             */
            initialize: function () {
                var temporaryContainer = createTemporayContainer();

                this._super();
                ko.renderTemplate(this.template, this, {}, temporaryContainer);
                temporaryContainer.parentNode.removeChild(temporaryContainer);

                return this;
            },
            modal: modal({
                responsive: true,
                innerScroll: true,
                buttons: []
            }),
            /**
             * Show Terms&Conditions pop-up
             */
            showAgreements: function () {
                ko.renderTemplate(this.template, this, {}, this.modal.openModal(), 'replaceNode');
            }
        });
    }
);
