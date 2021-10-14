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

    var isBillingAddressResolvedFromBackend = false;

    return {

        /**
         * Resolve estimation address. Used local storage
         */
        resolveEstimationAddress: function () {
            var address;

            if (quote.isVirtual()) {
                if (checkoutData.getBillingAddressFromData()) {
                    address = addressConverter.formAddressDataToQuoteAddress(
                        checkoutData.getBillingAddressFromData()
                    );
                    selectBillingAddress(address);
                } else {
                    this.resolveBillingAddress();
                }
            } else if (checkoutData.getShippingAddressFromData()) {
                address = addressConverter.formAddressDataToQuoteAddress(checkoutData.getShippingAddressFromData());
                selectShippingAddress(address);
            } else {
                this.resolveShippingAddress();
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
                isConvertAddress;

            if (addressList().length === 0) {
                address = addressConverter.formAddressDataToQuoteAddress(
                    checkoutData.getShippingAddressFromData()
                );
                selectShippingAddress(address);
            }
            shippingAddress = quote.shippingAddress();
            isConvertAddress = isEstimatedAddress || false;

            if (!shippingAddress) {
                shippingAddress = this.getShippingAddressFromCustomerAddressList();

                if (shippingAddress) {
                    selectShippingAddress(
                        isConvertAddress ?
                            addressConverter.addressToEstimationAddress(shippingAddress)
                            : shippingAddress
                    );
                }
            }
        },

        /**
         * @param {Object} ratesData
         */
        resolveShippingRates: function (ratesData) {
            var selectedShippingRate = checkoutData.getSelectedShippingRate(),
                availableRate = false;

            if (ratesData.length === 1 && !quote.shippingMethod()) {
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
                availableRate = _.find(ratesData, function (rate) {
                    var selectedShippingMethod = window.checkoutConfig.selectedShippingMethod;

                    return rate['carrier_code'] == selectedShippingMethod['carrier_code'] && //eslint-disable-line
                        rate['method_code'] == selectedShippingMethod['method_code']; //eslint-disable-line eqeqeq
                });
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

            selectedBillingAddress = checkoutData.getSelectedBillingAddress();
            newCustomerBillingAddressData = checkoutData.getNewCustomerBillingAddress();

            if (selectedBillingAddress) {
                if (selectedBillingAddress === 'new-customer-billing-address' && newCustomerBillingAddressData) {
                    selectBillingAddress(createBillingAddress(newCustomerBillingAddressData));
                } else {
                    addressList.some(function (address) {
                        if (selectedBillingAddress === address.getKey()) {
                            selectBillingAddress(address);
                        }
                    });
                }
            } else {
                this.applyBillingAddress();
            }

            if (!isBillingAddressResolvedFromBackend &&
                !checkoutData.getBillingAddressFromData() &&
                !_.isEmpty(window.checkoutConfig.billingAddressFromData) &&
                !quote.billingAddress()
            ) {
                if (window.checkoutConfig.isBillingAddressFromDataValid === true) {
                    selectBillingAddress(createBillingAddress(window.checkoutConfig.billingAddressFromData));
                } else {
                    checkoutData.setBillingAddressFromData(window.checkoutConfig.billingAddressFromData);
                }
                isBillingAddressResolvedFromBackend = true;
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

            if (quote.isVirtual() || !quote.billingAddress()) {
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
        },

        /**
         * Get shipping address from address list
         *
         * @return {Object|null}
         */
        getShippingAddressFromCustomerAddressList: function () {
            var shippingAddress = _.find(
                    addressList(),
                    function (address) {
                        return checkoutData.getSelectedShippingAddress() == address.getKey() //eslint-disable-line
                    }
                );

            if (!shippingAddress) {
                shippingAddress = _.find(
                    addressList(),
                    function (address) {
                        return address.isDefaultShipping();
                    }
                );
            }

            if (!shippingAddress && addressList().length === 1) {
                shippingAddress = addressList()[0];
            }

            return shippingAddress;
        }
    };
});
