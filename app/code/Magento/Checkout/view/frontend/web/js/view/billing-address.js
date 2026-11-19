/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'ko',
    'underscore',
    'Magento_Ui/js/form/form',
    'Magento_Customer/js/model/customer',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/create-billing-address',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/action/set-billing-address',
    'Magento_Ui/js/model/messageList',
    'mage/translate',
    'Magento_Checkout/js/model/billing-address-postcode-validator',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/model/rejected-billing-addresses'
],
function (
    ko,
    _,
    Component,
    customer,
    addressList,
    quote,
    createBillingAddress,
    selectBillingAddress,
    checkoutData,
    checkoutDataResolver,
    customerData,
    setBillingAddressAction,
    globalMessageList,
    $t,
    billingAddressPostcodeValidator,
    addressConverter,
    rejectedBillingAddresses
) {
    'use strict';

    var lastSelectedBillingAddress = null,
        addressUpdated = false,
        addressEdited = false,
        countryData = customerData.get('directory-data'),
        addressOptions = addressList().filter(function (address) {
            return address.getType() === 'customer-address';
        });

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/billing-address',
            actionsTemplate: 'Magento_Checkout/billing-address/actions',
            formTemplate: 'Magento_Checkout/billing-address/form',
            detailsTemplate: 'Magento_Checkout/billing-address/details',
            links: {
                isAddressFormVisible: '${$.billingAddressListProvider}:isNewAddressSelected'
            }
        },
        currentBillingAddress: quote.billingAddress,
        customerHasAddresses: addressOptions.length > 0,

        /**
         * Init component
         */
        initialize: function () {
            this._super();
            quote.paymentMethod.subscribe(function () {
                checkoutDataResolver.resolveBillingAddress();
            }, this);
            billingAddressPostcodeValidator.initFields(this.get('name') + '.form-fields');
        },

        /**
         * @return {exports.initObservable}
         */
        initObservable: function () {
            this._super()
                .observe({
                    selectedAddress: null,
                    isAddressDetailsVisible: quote.billingAddress() != null,
                    isAddressFormVisible: !customer.isLoggedIn() || !addressOptions.length,
                    isAddressSameAsShipping: false,
                    saveInAddressBook: 1
                });

            quote.billingAddress.subscribe(function (newAddress) {
                if (quote.isVirtual()) {
                    this.isAddressSameAsShipping(false);
                } else {
                    this.isAddressSameAsShipping(
                        newAddress != null &&
                        newAddress.getCacheKey() == quote.shippingAddress().getCacheKey() //eslint-disable-line eqeqeq
                    );
                }

                if (newAddress != null && newAddress.saveInAddressBook !== undefined) {
                    this.saveInAddressBook(newAddress.saveInAddressBook);
                } else {
                    this.saveInAddressBook(1);
                }
                this.isAddressDetailsVisible(true);
            }, this);

            return this;
        },

        canUseShippingAddress: ko.computed(function () {
            return !quote.isVirtual() && quote.shippingAddress() && quote.shippingAddress().canUseForBilling();
        }),

        /**
         * @param {Object} address
         * @return {*}
         */
        addressOptionsText: function (address) {
            return address.getAddressInline();
        },

        /**
         * @return {Boolean}
         */
        useShippingAddress: function () {
            if (this.isAddressSameAsShipping()) {
                selectBillingAddress(quote.shippingAddress());
                this.updateAddresses(true);
                this.isAddressDetailsVisible(true);
            } else {
                lastSelectedBillingAddress = quote.billingAddress();
                quote.billingAddress(null);
                this.isAddressDetailsVisible(false);
            }
            checkoutData.setSelectedBillingAddress(null);

            return true;
        },

        /**
         * Update address action
         */
        updateAddress: function () {
            var addressData, newBillingAddress, needsToUpdateAddress, request;

            needsToUpdateAddress = true;
            addressUpdated = true;

            if (this.selectedAddress() && !this.isAddressFormVisible()) {
                selectBillingAddress(this.selectedAddress());
                checkoutData.setSelectedBillingAddress(this.selectedAddress().getKey());
            } else {
                this.source.set('params.invalid', false);
                this.source.trigger(this.dataScopePrefix + '.data.validate');

                if (this.source.get(this.dataScopePrefix + '.custom_attributes')) {
                    this.source.trigger(this.dataScopePrefix + '.custom_attributes.data.validate');
                }

                if (this.source.get('params.invalid')) {
                    needsToUpdateAddress = false;
                } else {
                    addressData = this.source.get(this.dataScopePrefix);

                    if (customer.isLoggedIn() && !this.customerHasAddresses) { //eslint-disable-line max-depth
                        this.saveInAddressBook(1);
                    }
                    addressData['save_in_address_book'] = this.saveInAddressBook() ? 1 : 0;
                    newBillingAddress = createBillingAddress(addressData);
                    // New address must be selected as a billing address
                    selectBillingAddress(newBillingAddress);
                    checkoutData.setSelectedBillingAddress(newBillingAddress.getKey());
                    checkoutData.setNewCustomerBillingAddress(addressData);
                }
            }
            request = this.updateAddresses(needsToUpdateAddress);

            if (request && quote.billingAddress()) {
                request.fail(this.onBillingAddressRejected.bind(this, quote.billingAddress()));
            }
        },

        /**
         * Reopen the billing address form when the server rejects the address
         *
         * A rejected address would otherwise stay selected and be sent again with the shipping information, which then
         * fails with the billing error while the billing form is out of reach on the shipping step. A rejected saved
         * address is also no longer picked as the default billing address in this checkout.
         * Only a rejection (400) is handled: a request that failed for another reason (no connection, an expired
         * session or cart, a server error) keeps the address.
         *
         * @param {Object} address - the rejected address
         * @param {Object} response
         */
        onBillingAddressRejected: function (address, response) {
            if (!response || response.status !== 400) {
                return;
            }
            rejectedBillingAddresses.add(address);

            if (address.customerAddressId) {
                globalMessageList.addErrorMessage({
                    message: $t(
                        'Correct this address in your address book, or choose or enter another billing address.'
                    )
                });
            }

            if (quote.billingAddress() !== address) {
                return;
            }

            if (lastSelectedBillingAddress === address) {
                lastSelectedBillingAddress = null;
            }
            quote.billingAddress(null);
            // Keep the address accepted before an edit selected, so Cancel and a page reload return to it.
            checkoutData.setSelectedBillingAddress(
                lastSelectedBillingAddress ? lastSelectedBillingAddress.getKey() : null
            );
            this.isAddressDetailsVisible(false);
        },

        /**
         * Edit address action
         */
        editAddress: function () {
            addressUpdated = false;
            addressEdited = true;
            lastSelectedBillingAddress = quote.billingAddress();
            quote.billingAddress(null);
            this.isAddressDetailsVisible(false);
        },

        /**
         * Cancel address edit action
         */
        cancelAddressEdit: function () {
            addressUpdated = true;
            this.restoreBillingAddress();

            if (quote.billingAddress()) {
                // restore 'Same As Shipping' checkbox state
                this.isAddressSameAsShipping(
                    quote.billingAddress() != null &&
                        quote.billingAddress().getCacheKey() == quote.shippingAddress().getCacheKey() && //eslint-disable-line
                        !quote.isVirtual()
                );
                this.isAddressDetailsVisible(true);
            }
        },

        /**
         * Manage cancel button visibility
         */
        canUseCancelBillingAddress: ko.computed(function () {
            return quote.billingAddress() || lastSelectedBillingAddress;
        }),

        /**
         * Check if Billing Address Changes should be canceled
         */
        needCancelBillingAddressChanges: function () {
            if (addressEdited && !addressUpdated) {
                this.cancelAddressEdit();
            }
        },

        /**
         * Restore billing address
         */
        restoreBillingAddress: function () {
            var lastBillingAddress;

            if (lastSelectedBillingAddress != null) {
                selectBillingAddress(lastSelectedBillingAddress);
                lastBillingAddress = addressConverter.quoteAddressToFormAddressData(lastSelectedBillingAddress);

                checkoutData.setNewCustomerBillingAddress(lastBillingAddress);
            }
        },

        /**
         * @param {Number} countryId
         * @return {*}
         */
        getCountryName: function (countryId) {
            return countryData()[countryId] != undefined ? countryData()[countryId].name : ''; //eslint-disable-line
        },

        /**
         * Trigger action to update shipping and billing addresses
         *
         * @param {Boolean} force
         * @return {Object|undefined} the billing address request, when one is sent
         */
        updateAddresses: function (force) {
            var request;

            force = !(typeof force === 'undefined' || force !== true);

            if (force
                || window.checkoutConfig.reloadOnBillingAddress
                || !window.checkoutConfig.displayBillingOnPaymentMethod) {
                request = setBillingAddressAction(globalMessageList);
            }

            return request;
        },

        /**
         * Get code
         * @param {Object} parent
         * @returns {String}
         */
        getCode: function (parent) {
            return _.isFunction(parent.getCode) ? parent.getCode() : 'shared';
        },

        /**
         * Get customer attribute label
         *
         * @param {*} attribute
         * @returns {*}
         */
        getCustomAttributeLabel: function (attribute) {
            var label;

            if (typeof attribute === 'string') {
                return attribute;
            }

            if (attribute.label) {
                return attribute.label;
            }

            if (_.isArray(attribute.value)) {
                label = _.map(attribute.value, function (value) {
                    return this.getCustomAttributeOptionLabel(attribute['attribute_code'], value) || value;
                }, this).join(', ');
            } else if (typeof attribute.value === 'object') {
                label = _.map(Object.values(attribute.value)).join(', ');
            } else {
                label = this.getCustomAttributeOptionLabel(attribute['attribute_code'], attribute.value);
            }

            return label || attribute.value;
        },

        /**
         * Get option label for given attribute code and option ID
         *
         * @param {String} attributeCode
         * @param {String} value
         * @returns {String|null}
         */
        getCustomAttributeOptionLabel: function (attributeCode, value) {
            var option,
                label,
                options = this.source.get('customAttributes') || {};

            if (options[attributeCode]) {
                option = _.findWhere(options[attributeCode], {
                    value: value
                });

                if (option) {
                    label = option.label;
                }
            } else if (value.file !== null) {
                label = value.file;
            }

            return label;
        }
    });
});
