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
        'jquery',
        'Magento_Ui/js/form/component',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils'
    ],
    function ($, Component, quote, priceUtils) {
        return Component.extend({
            defaults: {
                template: 'Magento_Tax/checkout/review/shipping',
            },
            getColspan: 3,
            style: "",
            rates: quote.getRates(),
            selectedShippingMethod: quote.getShippingMethod(),
            getTitle: function() {
                var shippingMethodTitle = '';
                if (this.selectedShippingMethod()) {
                    var code = this.selectedShippingMethod();
                    $.each(this.rates(), function (key, entity) {
                        if (entity['carrier_code'] == code[0]
                            && entity['method_code'] == code[1]) {
                            shippingMethodTitle = "(" + entity['carrier_title'] + " - " + entity['method_title'] + ")";
                        }
                    });
                }
                return "Shipping & Handling" + shippingMethodTitle;
            },
            totals: quote.getTotals(),
            getValue: function() {
                var price = 0;
                if (this.totals()) {
                    price =  this.totals().shipping_amount;
                }
                return quote.getCurrencySymbol() + priceUtils.formatPrice(price);
            }
        });
    }
);
