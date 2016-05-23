/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'ko',
        'Magento_Paypal/js/model/iframe'
    ],
    function (Component, ko, iframe) {
        return Component.extend({
            defaults: {
                template: 'Magento_Paypal/review/actions/iframe'
            },
            getCode: function() {
                return this.index;
            },
            getActionUrl: function() {
                return this.isInAction() ? window.checkoutConfig.payment.paypalIframe.actionUrl[this.getCode()] : '';
            },
            afterSave: function() {
                iframe.setIsInAction(true);
                return false;
            },
            isInAction: function() {
                return iframe.getIsInAction()();
            },
            placeOrder: function(context) {
                return context.placeOrder.bind(context, this.afterSave);
            }
        });
    }
);
