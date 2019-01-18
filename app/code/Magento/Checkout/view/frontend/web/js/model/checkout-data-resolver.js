/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Checkout adapter for customer data storage
 */
define([
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/action/create-shipping-address',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/action/select-payment-method',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/action/create-billing-address',
    'underscore'
], function (
    addressList,
    quote,
    checkoutData,
    createShippingAddress,
    selectShippingAddress,
    selectShippingMethodAction,
    paymentService,
    selectPaymentMethodAction,
    addressConverter,
    selectBillingAddress,
    createBillingAddress,
    _
) {
    'use strict';

    return {

        /**
         * Resolve estimation address. Used local storage
         */
        resolveEstimationAddress: function () {
            var address;

            if (checkoutData.getShippingAddressFromData()) {
                address = addressConverter.formAddressDataToQuoteAddress(checkoutData.getShippingAddressFromData());
                selectShippingAddress(address);
            } else {
                this.resolveShippingAddress();
            }

            if (quote.isVirtual()) {
                if (checkoutData.getBillingAddressFromData()) {
                    address = addressConverter.formAddressDataToQuoteAddress(
                        checkoutData.getBillingAddressFromData()
                    );
                    selectBillingAddress(address);
                } else {
                    this.resolveBillingAddress();
                }
            }

        },

        /**
         * Resolve shipping address. Used local storage
         */
        resolveShippingAddress: function () {
            var newCustomerShippingAddress;

            if (!checkoutData.getShippingAddressFromData() &&
                window.checkoutConfig.shippingAddressFromData
            ) {
                checkoutData.setShippingAddressFromData(window.checkoutConfig.shippingAddressFromData);
            }

            newCustomerShippingAddress = checkoutData.getNewCustomerShippingAddress();

            if (newCustomerShippingAddress) {
                createShippingAddress(newCustomerShippingAddress);
            }
            this.applyShippingAddress();
        },

        /**
         * Apply resolved estimated address to quote
         *
         * @param {Object} isEstimatedAddress
         */
        applyShippingAddress: function (isEstimatedAddress) {
            var address,
                shippingAddress,
                isConvertAddress,
                addressData,
                isShippingAddressInitialized;

            if (addressList().length === 0) {
                address = addressConverter.formAddressDataToQuoteAddress(
                    checkoutData.getShippingAddressFromData()
                );
                selectShippingAddress(address);
            }
            shippingAddress = quote.shippingAddress();
            isConvertAddress = isEstimatedAddress || false;

            if (!shippingAddress) {
                isShippingAddressInitialized = addressList.some(function (addressFromList) {
                    if (checkoutData.getSelectedShippingAddress() == addressFromList.getKey()) { //eslint-disable-line
                        addressData = isConvertAddress ?
                            addressConverter.addressToEstimationAddress(addressFromList)
                            : addressFromList;
                        selectShippingAddress(addressData);

                        return true;
                    }

                    return false;
                });

                if (!isShippingAddressInitialized) {
                    isShippingAddressInitialized = addressList.some(function (addrs) {
                        if (addrs.isDefaultShipping()) {
                            addressData = isConvertAddress ?
                                addressConverter.addressToEstimationAddress(addrs)
                                : addrs;
                            selectShippingAddress(addressData);

                            return true;
                        }

                        return false;
                    });
                }

                if (!isShippingAddressInitialized && addressList().length === 1) {
                    addressData = isConvertAddress ?
                        addressConverter.addressToEstimationAddress(addressList()[0])
                        : addressList()[0];
                    selectShippingAddress(addressData);
                }
            }
        },

        /**
         * @param {Object} ratesData
         */
        resolveShippingRates: function (ratesData) {
            var selectedShippingRate = checkoutData.getSelectedShippingRate(),
                availableRate = false;

            if (ratesData.length === 1) {
                //set shipping rate if we have only one available shipping rate
                selectShippingMethodAction(ratesData[0]);

                return;
            }

            if (quote.shippingMethod()) {
                availableRate = _.find(ratesData, function (rate) {
                    return rate['carrier_code'] == quote.shippingMethod()['carrier_code'] && //eslint-disable-line
                        rate['method_code'] == quote.shippingMethod()['method_code']; //eslint-disable-line eqeqeq
                });
            }

            if (!availableRate && selectedShippingRate) {
                availableRate = _.find(ratesData, function (rate) {
                    return rate['carrier_code'] + '_' + rate['method_code'] === selectedShippingRate;
                });
            }

            if (!availableRate && window.checkoutConfig.selectedShippingMethod) {
                availableRate = window.checkoutConfig.selectedShippingMethod;
                selectShippingMethodAction(window.checkoutConfig.selectedShippingMethod);

                return;
            }

            //Unset selected shipping method if not available
            if (!availableRate) {
                selectShippingMethodAction(null);
            } else {
                selectShippingMethodAction(availableRate);
            }
        },

        /**
         * Resolve payment method. Used local storage
         */
        resolvePaymentMethod: function () {
            var availablePaymentMethods = paymentService.getAvailablePaymentMethods(),
                selectedPaymentMethod = checkoutData.getSelectedPaymentMethod();

            if (selectedPaymentMethod) {
                availablePaymentMethods.some(function (payment) {
                    if (payment.method == selectedPaymentMethod) { //eslint-disable-line eqeqeq
                        selectPaymentMethodAction(payment);
                    }
                });
            }
        },

        /**
         * Resolve billing address. Used local storage
         */
        resolveBillingAddress: function () {
            var selectedBillingAddress,
                newCustomerBillingAddressData;

            if (!checkoutData.getBillingAddressFromData() &&
                window.checkoutConfig.billingAddressFromData
            ) {
                checkoutData.setBillingAddressFromData(window.checkoutConfig.billingAddressFromData);
            }

            selectedBillingAddress = checkoutData.getSelectedBillingAddress();
            newCustomerBillingAddressData = checkoutData.getNewCustomerBillingAddress();

            if (selectedBillingAddress) {
                if (selectedBillingAddress == 'new-customer-address' && newCustomerBillingAddressData) { //eslint-disable-line
                    selectBillingAddress(createBillingAddress(newCustomerBillingAddressData));
                } else {
                    addressList.some(function (address) {
                        if (selectedBillingAddress == address.getKey()) { //eslint-disable-line eqeqeq
                            selectBillingAddress(address);
                        }
                    });
                }
            } else {
                this.applyBillingAddress();
            }
        },

        /**
         * Apply resolved billing address to quote
         */
        applyBillingAddress: function () {
            var shippingAddress,
                isBillingAddressInitialized;

            if (quote.billingAddress()) {
                selectBillingAddress(quote.billingAddress());

                return;
            }

            if (quote.isVirtual()) {
                isBillingAddressInitialized = addressList.some(function (addrs) {
                    if (addrs.isDefaultBilling()) {
                        selectBillingAddress(addrs);

                        return true;
                    }

                    return false;
                });
            }

            shippingAddress = quote.shippingAddress();

            if (!isBillingAddressInitialized &&
                shippingAddress &&
                shippingAddress.canUseForBilling() &&
                (shippingAddress.isDefaultShipping() || !quote.isVirtual())
            ) {
                //set billing address same as shipping by default if it is not empty
                selectBillingAddress(quote.shippingAddress());
            }
        }
    };
});
