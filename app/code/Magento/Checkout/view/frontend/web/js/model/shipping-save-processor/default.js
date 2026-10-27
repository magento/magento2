/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/resource-url-manager',
    'mage/storage',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/model/payment/method-converter',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/model/shipping-save-processor/payload-extender',
    'jquery',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/rejected-billing-addresses',
    'Magento_Ui/js/model/messageList',
    'mage/translate'
], function (
    ko,
    quote,
    resourceUrlManager,
    storage,
    paymentService,
    methodConverter,
    errorProcessor,
    fullScreenLoader,
    selectBillingAddressAction,
    payloadExtender,
    $,
    checkoutData,
    rejectedBillingAddresses,
    messageList,
    $t
) {
    'use strict';

    /**
     * @return {Object}
     */
    function buildPayload() {
        var payload = {
            addressInformation: {
                'shipping_address': quote.shippingAddress(),
                'billing_address': quote.billingAddress(),
                'shipping_method_code': quote.shippingMethod()['method_code'],
                'shipping_carrier_code': quote.shippingMethod()['carrier_code']
            }
        };

        payloadExtender(payload);

        return payload;
    }

    /**
     * @param {Object} payload
     * @return {jQuery.Deferred}
     */
    function post(payload) {
        return storage.post(
            resourceUrlManager.getUrlForSetShippingInformation(quote),
            JSON.stringify(payload)
        );
    }

    /**
     * @param {Object} response
     */
    function onSaved(response) {
        quote.setTotals(response.totals);
        paymentService.setPaymentMethods(methodConverter(response['payment_methods']));
        fullScreenLoader.stopLoader();
    }

    /**
     * @param {Object} response
     */
    function onFailed(response) {
        errorProcessor.process(response);
        fullScreenLoader.stopLoader();
    }

    /**
     * Whether a rejected save is retried with the shipping address as the billing address
     *
     * Checkout picks the customer's default billing address when no billing address was chosen. If the server
     * rejects that saved address (an invalid telephone number, say), every save of the shipping step fails while
     * the address cannot be corrected in checkout.
     *
     * @param {Object} billingAddress - the billing address sent
     * @param {Object} response
     * @return {Boolean}
     */
    function canRetryWithShippingAsBilling(billingAddress, response) {
        var shippingAddress = quote.shippingAddress();

        return !!response && response.status === 400 &&
            !!billingAddress && !!billingAddress.customerAddressId &&
            typeof billingAddress.isDefaultBilling === 'function' && billingAddress.isDefaultBilling() &&
            !checkoutData.getSelectedBillingAddress() &&
            !!shippingAddress && shippingAddress.canUseForBilling() &&
            billingAddress.getCacheKey() !== shippingAddress.getCacheKey();
    }

    /**
     * Save again with the shipping address as the billing address
     *
     * If that is accepted, the default billing address was what the server rejected: it is not picked again in this
     * checkout and the customer is told. Otherwise the first error is shown and the billing address is restored.
     *
     * @param {Object} billingAddress - the rejected billing address
     * @param {Array} firstFailure - the arguments of the first failure
     * @param {jQuery.Deferred} result
     */
    function retryWithShippingAsBilling(billingAddress, firstFailure, result) {
        var payload;

        try {
            selectBillingAddressAction(quote.shippingAddress());
            payload = buildPayload();
        } catch (e) {
            selectBillingAddressAction(billingAddress);
            onFailed(firstFailure[0]);
            result.reject.apply(result, firstFailure);

            throw e;
        }

        post(payload).done(function (response) {
            rejectedBillingAddresses.add(billingAddress);
            messageList.addErrorMessage({
                message: $t(
                    'Your default billing address was not accepted, so your shipping address is used for ' +
                    'billing. Correct the default billing address in your address book, or choose another ' +
                    'billing address.'
                )
            });
            onSaved(response);
            result.resolve.apply(result, arguments);
        }).fail(function () {
            selectBillingAddressAction(billingAddress);
            onFailed(firstFailure[0]);
            result.reject.apply(result, firstFailure);
        });
    }

    return {
        /**
         * @return {jQuery.Deferred}
         */
        saveShippingInformation: function () {
            var result = $.Deferred(),
                billingAddress,
                payload;

            if (!quote.billingAddress() && quote.shippingAddress().canUseForBilling()) {
                selectBillingAddressAction(quote.shippingAddress());
            }
            billingAddress = quote.billingAddress();
            payload = buildPayload();

            fullScreenLoader.startLoader();

            post(payload).done(function (response) {
                onSaved(response);
                result.resolve.apply(result, arguments);
            }).fail(function (response) {
                if (!canRetryWithShippingAsBilling(billingAddress, response)) {
                    onFailed(response);
                    result.reject.apply(result, arguments);

                    return;
                }
                retryWithShippingAsBilling(billingAddress, Array.prototype.slice.call(arguments), result);
            });

            return result.promise();
        }
    };
});
