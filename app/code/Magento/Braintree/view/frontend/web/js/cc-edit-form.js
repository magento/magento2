/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define([
    "jquery",
    "braintree",
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    "jquery/ui"
], function ($, braintree, $t, alert) {
    'use strict';

    $.widget('mage.braintreeEditForm', {
        options: {
            backUrl: '',
            formId: '#form-validate',
            creditCardTypeId: '#credit_card_type',
            deviceDataId: '#device_data',
            creditCardNumber: '#credit_card_number',
            creditCardExpirationYr: '#credit_card_expiration_yr',
            creditCardExpiration: '#credit_card_expiration',
            creditCardCvv: '#credit_card_cvv',
            creditCardCardHolderName: '#credit_card_cardholder_name',
            creditCardOptionsMakeDefault: '#credit_card_options_make_default',
            billingAddressFirstName: '#billing_address_first_name',
            billingAddressLastName: '#billing_address_last_name',
            billingAddressCompany: '#billing_address_company',
            billingAddressStreetAddress: '#billing_address_street_address',
            billingAddressExtendedAddress: '#billing_address_extended_address',
            billingAddressLocality: '#billing_address_locality',
            billingAddressRegionId: '#billing_address_region_id',
            billingAddressRegion: '#billing_address_region',
            billingAddressPostalCode: '#billing_address_postal_code',
            billingAddressCountry: '#billing_address_country',
            clientToken: "",
            braintreeClient: null,
            countrySpecificCardTypes: {},
            cardTypes: {},
            selectMergedOptions: null,
            applicableCardTypes: {},
            isFraudDetectionEnabled :false
        },
        _create: function () {
            if (!this.preventDoubleInit()) {
                var self = this;
                this.ccTypes = {
                    VI: [new RegExp('^4[0-9]{12}([0-9]{3})?$'), new RegExp('^[0-9]{3}$')],
                    MC: [new RegExp('^5[1-5][0-9]{14}$'), new RegExp('^[0-9]{3}$')],
                    AE: [new RegExp('^3[47][0-9]{13}$'), new RegExp('^[0-9]{4}$')],
                    DI: [new RegExp('^6011[0-9]{12}$'), new RegExp('^[0-9]{3}$')],
                    JCB: [new RegExp('^(3[0-9]{15}|(2131|1800)[0-9]{11})$'), new RegExp('^[0-9]{3,4}$')],
                    OT: [false, new RegExp('^([0-9]{3}|[0-9]{4})?$')]
                };

                self.options.braintreeClient = new braintree.api.Client({clientToken: this.options.clientToken});
                $(self.options.formId).on('submit', function (e) {
                    e.preventDefault();
                    $(self.options.formId).trigger('afterFormSubmit');
                });

                $(self.options.formId).on('afterFormSubmit', function (e) {
                    self.callBraintree();
                });
                self.options.selectMergedOptions = $(self.options.creditCardTypeId).find('option');
                $(self.options.billingAddressCountry).on('change', function (e) {
                    self.populateCountrySpecificCCType();
                });

                $(self.options.creditCardNumber).on('input', function () {
                    var ccNumber = $(this).val(),
                        ccTypeField = $(self.options.creditCardTypeId).find('options');
                    ccNumber = ccNumber.replace(/\D/g,''); //remove all but the digits
                    for(var ccType in self.ccTypes) {
                        if(self.ccTypes.hasOwnProperty(ccType)) {
                            var ccRegex = self.ccTypes[ccType][0];
                            if(ccRegex && ccNumber.match(ccRegex)) {
                                if ($(self.options.creditCardTypeId).find('option').length>0) {
                                        $(self.options.creditCardTypeId).val(ccType);
                                }
                            }
                        }
                    }
                });

                $(self.options.billingAddressCountry).trigger('change');
            }

        },
        preventDoubleInit: function() {
            //TODO remove this quick fix and fix the core problem, this file gets included twice, and it should not
            return $._data($(this.options.formId).get(0),'events').afterFormSubmit;
        },
        populateCountrySpecificCCType: function () {
            //TODO refactor with _underscore.js
            var self = this;
            var country = $(self.options.billingAddressCountry).val();
            var ccType = $(self.options.creditCardTypeId).val();
            if (self.options.countrySpecificCardTypes.hasOwnProperty(country)) {
                var cTypeList = $(self.options.creditCardTypeId);
                cTypeList.html('');
                self.options.selectMergedOptions.each(function(i) {
                    if (i == 0) {
                        cTypeList.append(
                            '<option value="' + $(this).attr('value') + '">' + $(this).text() + '</option>');
                    }
                });
                for (var co in self.options.countrySpecificCardTypes[country]) {
                    var currentType=self.options.countrySpecificCardTypes[country][co];
                    var currentText = '';
                    self.options.selectMergedOptions.each(function(i) {
                        if ( $(this).attr('value') == currentType )
                            currentText = $(this).text();
                    });
                    cTypeList.append('<option value="'+currentType+'">'+currentText+'</option>');
                }
            } else {
                //all options
                var cTypeList = $(self.options.creditCardTypeId);
                cTypeList.html('');
                self.options.selectMergedOptions.each(function(i) {
                    if (self.options.applicableCardTypes.indexOf($(this).attr('value')) > -1 || i == 0) {
                        cTypeList.append(
                            '<option value="' + $(this).attr('value') + '">' + $(this).text() + '</option>');
                    }
                });
            }
            $(self.options.creditCardTypeId).val(ccType);
        },
        callBraintree: function () {
            var self = this;
            if ($(self.options.formId).validate().errorList.length == 0) {
                var ccNumber = $(self.options.creditCardNumber).val(),
                    ccType = $(self.options.creditCardTypeId).val(),
                    ccExprYr = $(self.options.creditCardExpirationYr).val(),
                    ccExprMo = ("0" + $(self.options.creditCardExpiration).val()).slice(-2),
                    cvv = self.options.hasVerification ? $(self.options.creditCardCvv).val() : null,
                    deviceData = (self.options.isFraudDetectionEnabled && $(self.options.deviceDataId).length>0) ?
                        $(self.options.deviceDataId).val() : null,
                    ccHolderName = $(self.options.creditCardCardHolderName).val(),
                    isDefault = $(self.options.creditCardOptionsMakeDefault).prop('checked'),
                    billFirstName = $(self.options.billingAddressFirstName).val(),
                    billLastName = $(self.options.billingAddressLastName).val(),
                    billCompany = $(self.options.billingAddressCompany).val(),
                    billStreetAddress = $(self.options.billingAddressStreetAddress).val(),
                    billExtAddress = $(self.options.billingAddressExtendedAddress).val(),
                    billCity = $(self.options.billingAddressLocality).val(),
                    billState = $(self.options.billingAddressRegionId).is(':visible') ?
                        $(self.options.billingAddressRegionId).find('option:selected').text() : $(self.options.billingAddressRegion).val(),
                    billPostal = $(self.options.billingAddressPostalCode).val(),
                    billCountry = $(self.options.billingAddressCountry).val();
                $('body').trigger('processStart');
                var billingAddress = {
                        firstName: billFirstName,
                        lastName: billLastName,
                        company: billCompany,
                        streetAddress: billStreetAddress,
                        extendedAddress: billExtAddress,
                        locality: billCity,
                        region: billState,
                        postalCode: billPostal,
                        countryCodeAlpha2: billCountry
                    },
                    submitObj = {
                        number: ccNumber,
                        cardholderName: ccHolderName,
                        expirationDate: ccExprMo + '/' + ccExprYr,
                        billingAddress: billingAddress
                    };

                if (self.options.hasVerification) {
                    submitObj.cvv = cvv;
                }

                self.options.braintreeClient.tokenizeCard(submitObj, function (err, nonce) {
                    // Send nonce to our server
                    if (!err) {
                        $.ajax({
                            type: "POST",
                            url: self.options.ajaxSaveUrl,
                            data: {
                                nonce: nonce,
                                billingAddress: billingAddress,
                                options: {
                                    default: isDefault,
                                    token: self.options.cardToken,
                                    update: self.options.isEditMode,
                                    ccType: ccType,
                                    device_data: deviceData
                                }
                            },
                            success: function (response) {
                                $('body').trigger('processStop');
                                if (response instanceof Object) {
                                    if (response.success) {
                                        $('body').trigger('processStart');
                                        window.location = self.options.backUrl;
                                    }
                                }
                            },
                            error: function (response) {
                                alert({
                                    content: $t('There was error during saving card data')
                                });
                            }
                        });
                    } else {
                        //handle error
                        $('body').trigger('processStop');
                        alert({
                            content: $t('There was error during saving card data')
                        });
                    }
                });
            }
        }
    });
    return $.mage.braintreeEditForm;
});
