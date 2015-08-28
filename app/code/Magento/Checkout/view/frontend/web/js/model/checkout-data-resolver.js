/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global alert*/
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
    'Magento_Checkout/js/action/create-billing-address'
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
    createBillingAddress
) {
    'use strict';

    return {
        resolveShippingAddress: function () {
            var newCustomerShippingAddress = checkoutData.getNewCustomerShippingAddress();
            if (newCustomerShippingAddress) {
                createShippingAddress(newCustomerShippingAddress);
            }

            if (addressList().length == 0) {
                var address = addressConverter.formAddressDataToQuoteAddress(checkoutData.getShippingAddressFromData());
                selectShippingAddress(address);
            }

            var shippingAddress = quote.shippingAddress();
            if (!shippingAddress) {
                var isShippingAddressInitialized = addressList.some(function (address) {
                    if (checkoutData.getSelectedShippingAddress() == address.getKey()) {
                        selectShippingAddress(address);
                        return true;
                    }
                    return false;
                });

                if (!isShippingAddressInitialized) {
                    isShippingAddressInitialized = addressList.some(function (address) {
                        if (address.isDefaultShipping()) {
                            selectShippingAddress(address);
                            return true;
                        }
                        return false;
                    });
                }
                if (!isShippingAddressInitialized && addressList().length == 1) {
                    selectShippingAddress(addressList()[0]);
                }
            }
        },

        resolveShippingRates: function (ratesData) {
            var selectedShippingRate = checkoutData.getSelectedShippingRate();
            var rateIsAvailable = false;

            if (ratesData.length == 1) {
                //set shipping rate if we have only one available shipping rate
                selectShippingMethodAction(ratesData[0]);
                return;
            }

            if(quote.shippingMethod()) {
                rateIsAvailable = ratesData.some(function (rate) {
                    return rate.carrier_code == quote.shippingMethod().carrier_code
                        && rate.method_code == quote.shippingMethod().method_code;
                });
            }

            if (!rateIsAvailable && selectedShippingRate) {
                rateIsAvailable = ratesData.some(function (rate) {
                    if (rate.carrier_code + "_" + rate.method_code == selectedShippingRate) {
                        selectShippingMethodAction(rate);
                        return true;
                    }
                    return false;

                });
            }

            if (!rateIsAvailable && window.checkoutConfig.selectedShippingMethod) {
                rateIsAvailable = true;
                selectShippingMethodAction(window.checkoutConfig.selectedShippingMethod);
            }

            //Unset selected shipping shipping method if not available
            if (!rateIsAvailable) {
                selectShippingMethodAction(null);
            }
        },

        resolvePaymentMethod: function () {
            var availablePaymentMethods = paymentService.getAvailablePaymentMethods();
            var selectedPaymentMethod = checkoutData.getSelectedPaymentMethod();
            if (selectedPaymentMethod) {
                availablePaymentMethods.some(function (payment) {
                    if (payment.method == selectedPaymentMethod) {
                        selectPaymentMethodAction(payment);
                    }
                });
            }
        },

        resolveBillingAddress: function () {
            var selectedBillingAddress = checkoutData.getSelectedBillingAddress(),
                newCustomerBillingAddressData = checkoutData.getNewCustomerBillingAddress(),
                shippingAddress = quote.shippingAddress();

            if (selectedBillingAddress) {
                if (selectedBillingAddress == 'new-customer-address' && newCustomerBillingAddressData) {
                    selectBillingAddress(createBillingAddress(newCustomerBillingAddressData));
                } else {
                    addressList.some(function (address) {
                        if (selectedBillingAddress == address.getKey()) {
                            selectBillingAddress(address);
                        }
                    });
                }
            } else if (
                shippingAddress
                && shippingAddress.canUseForBilling()
                && (shippingAddress.isDefaultShipping() || !quote.isVirtual())
            ) {
                //set billing address same as shipping by default if it is not empty
                selectBillingAddress(quote.shippingAddress());
            }
        }
    }
});
