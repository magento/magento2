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
        'Magento_Ui/js/form/component',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils'
    ],
    function (Component, quote, priceUtils) {
        return Component.extend({
            defaults: {
                template: 'Magento_Tax/checkout/review/subtotal'
            },
            getColspan: 3,
            style: "",
            getTitle: function() {
                return "Subtotal"
            },
            totals: quote.getTotals(),
            getValue: function () {
                var price = 0;
                if (this.totals()) {
                    price = this.totals().subtotal;
                }
                return quote.getCurrencySymbol() + priceUtils.formatPrice(price);
            }
        });
    }
);
