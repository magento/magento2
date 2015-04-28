/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    ['Magento_Ui/js/form/form', 'Magento_Checkout/js/view/review', 'underscore'],
    function (Component, review, _) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magento_CheckoutAgreements/checkout/review/agreements'
            },
            initialize: function() {
                this._super();
                if (window.checkoutConfig.checkoutAgreementsEnabled) {
                    review.prototype.beforePlaceOrder.checkoutAgreements = this;
                }
            },
            validate: function() {
                this.source.set('params.invalid', false);
                this.source.trigger('checkoutAgreements.data.validate');
                return this.source.get('params.invalid');
            },
            getSubmitParams: function() {
                return {
                    agreements: _.keys(this.source.get('checkoutAgreements'))
                };
            }
        });
    }
);
