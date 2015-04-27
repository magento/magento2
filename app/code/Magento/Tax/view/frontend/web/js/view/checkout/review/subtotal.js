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
        'Magento_Catalog/js/price-utils'
    ],
    function (Component, quote, priceUtils) {
        var displaySubtotalMode = window.checkoutConfig.reviewTotalsDisplayMode;
        return Component.extend({
            defaults: {
                displaySubtotalMode: displaySubtotalMode,
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
            },
            isBothPricesDisplayed: function() {
                return 'both' == this.displaySubtotalMode;
            },
            isIncludingTaxDisplayed: function() {
                return 'including' == this.displaySubtotalMode;
            },
            getValueInclTax: function() {
                var price = 0;
                if (this.totals()) {
                    price = this.totals().subtotal_incl_tax;
                }
                return quote.getCurrencySymbol() + priceUtils.formatPrice(price);
            }
        });
    }
);
