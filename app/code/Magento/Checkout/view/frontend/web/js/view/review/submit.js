/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/place-order'
    ],
    function (Component, quote, orderAction) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/review/submit',
                displayArea: 'submit'
            },
            getLabel: function() {
                var view = this.getViewByCode(quote.getPaymentMethod()());
                if (view && view.getLabel) {
                    return view.getLabel();
                } else {
                    return 'Place Order';
                }
            },
            getClick: function() {
                var view = this.getViewByCode(quote.getPaymentMethod()());
                if (view && view.getClick) {
                    return view.getClick();
                } else {
                    return orderAction;
                }
            },
            getViewByCode: function(code) {
                return _.find(this.elems(), function(elem) {
                    return elem.index == code;
                });
            }
        });
    }
);
