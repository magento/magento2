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
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/shipping-service'
    ],
    function ($, Component, quote, priceUtils, shippingService) {
        return Component.extend({
            defaults: {
                template: 'Magento_Tax/checkout/review/shipping',
            },
            getColspan: 3,
            style: "",
            selectedShippingMethod: quote.getShippingMethod(),
            getTitle: function() {
                return "Shipping & Handling" + shippingService.getTitleByCode(this.selectedShippingMethod());
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
