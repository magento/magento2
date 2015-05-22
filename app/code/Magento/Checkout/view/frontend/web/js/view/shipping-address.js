/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        "jquery",
        'Magento_Ui/js/form/form',
        'ko',
        'Magento_Customer/js/model/customer',
        '../model/addresslist',
        '../model/address-converter',
        '../model/quote',
        'Magento_Checkout/js/model/step-navigator',
        'mage/translate'
    ],
    function($, Component, ko, customer, addressList, addressConverter, quote, navigator) {
        'use strict';
        var stepName = 'shippingAddress';
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-address',
                visible: true,
                formVisible: customer.getShippingAddressList().length === 0
            },
            stepNumber: navigator.getStepNumber(stepName),
            isVisible: navigator.isStepVisible(stepName),
            isCustomerLoggedIn: customer.isLoggedIn(),
            isFormPopUpVisible: ko.observable(false),
            isFormInline: !customer.isLoggedIn() || window.checkoutConfig.customerAddressCount == 0,

            stepClassAttributes: function() {
                return navigator.getStepClassAttributes(stepName);
            },

            /** Initialize observable properties */
            initObservable: function () {
                this._super()
                    .observe('visible');
                return this;
            },

            /** Check if component is active */
            isActive: function() {
                if (quote.isVirtual()) {
                    navigator.setStepEnabled(stepName, false);
                }
                return !quote.isVirtual();
            },

            /** Navigate to current step */
            navigateToCurrentStep: function() {
                if (!navigator.isStepVisible(stepName)()) {
                    navigator.goToStep(stepName);
                }
            },

            /** Show address form popup */
            showFormPopUp: function() {
                this.isFormPopUpVisible(true);
            },

            /** Hide address form popup */
            hideFormPopUp: function() {
                this.isFormPopUpVisible(false);
            },

            /** Save new shipping address */
            saveNewAddress: function() {
                this.validate();
                if (!this.source.get('params.invalid')) {
                    var addressData = this.source.get('shippingAddress');
                    var saveInAddressBook = true;
                    if (this.isCustomerLoggedIn) {
                        var addressBookCheckBox =  $("input[name = 'shipping[save_in_address_book]']:checked");
                        saveInAddressBook = !!addressBookCheckBox.val();
                    }
                    addressData.save_in_address_book = saveInAddressBook;

                    var newAddress = addressConverter.formAddressDataToQuoteAddress(addressData);
                    addressList.add(newAddress);
                    //selectShippingAddress(addressData, additionalData);
                    this.isFormPopUpVisible(false);
                }
            },
            validate: function() {
                this.source.set('params.invalid', false);
                this.source.trigger('shippingAddress.data.validate');
            }
        });
    }
);
