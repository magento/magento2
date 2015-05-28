/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Ui/js/model/errorlist'
    ],
    function (urlBuilder, quote, storage, shippingService, errorList) {
        "use strict";
        var serviceUrl;
        if (quote.getCheckoutMethod()() === 'guest') {
            serviceUrl = urlBuilder.createUrl('/guest-carts/:quoteId/estimate-shipping-methods', {quoteId: quote.getQuoteId()});
        } else {
            serviceUrl =  urlBuilder.createUrl('/carts/mine/estimate-shipping-methods', {});
        }
        return {
            getRates: function(address) {
                storage.post(
                    serviceUrl,
                    JSON.stringify({
                            address: {
                                country_id: address.countryId,
                                region_id: address.regionId,
                                region: address.region,
                                postcode: address.postcode

                            }
                        }
                    )
                ).done(
                    function(result) {
                        shippingService.setShippingRates(result);
                        //shippingRates = result.shipping_methods
                    }

                ).fail(
                    function(response) {
                        var error = JSON.parse(response.responseText);
                        errorList.add(error);
                        shippingService.setShippingRates([])
                    }

                );
            }

        };
    }
);