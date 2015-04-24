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
        'Magento_Tax/js/view/checkout/review/item/columns/price'
    ],
    function (Price) {
        "use strict";
        var displayPriceMode = window.checkoutConfig.reviewItemPriceDisplayMode || 'including';
        return Price.extend({
            defaults: {
                displayPriceMode: displayPriceMode,
                ownClass: 'subtotal',
                columnTitle: 'Subtotal',
                template: 'Magento_Tax/checkout/review/item/columns/subtotal'
            }
        });
    }
);
