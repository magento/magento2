/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'uiClass',
    'Magento_CardinalCommerce/js/cardinal-factory',
    'Magento_Checkout/js/model/quote',
    'mage/translate'
], function ($, Class, cardinalFactory, quote, $t) {
    'use strict';

    return {
        /**
         * Starts Cardinal Consumer Authentication
         *
         * @param {Object} cardData
         * @return {jQuery.Deferred}
         */
        startAuthentication: function (cardData) {
            var deferred = $.Deferred();

            if (this.cardinalClient) {
                this._startAuthentication(deferred, cardData);
            } else {
                cardinalFactory(this.getEnvironment())
                    .done(function (client) {
                        this.cardinalClient = client;
                        this._startAuthentication(deferred, cardData);
                    }.bind(this));
            }

            return deferred.promise();
        },

        /**
         * Cardinal Consumer Authentication
         *
         * @param {jQuery.Deferred} deferred
         * @param {Object} cardData
         */
        _startAuthentication: function (deferred, cardData) {
            //this.cardinalClient.configure({ logging: { level: 'verbose' } });
            this.cardinalClient.on('payments.validated', function (data, jwt) {
                if (data.ErrorNumber !== 0) {
                    deferred.reject(data.ErrorDescription);
                } else if ($.inArray(data.ActionCode, ['FAILURE', 'ERROR']) !== -1) {
                    deferred.reject($t('Authentication Failed. Please try again with another form of payment.'));
                } else {
                    deferred.resolve(jwt);
                }
                this.cardinalClient.off('payments.validated');
            }.bind(this));

            this.cardinalClient.on('payments.setupComplete', function () {
                this.cardinalClient.start('cca', this.getRequestOrderObject(cardData));
                this.cardinalClient.off('payments.setupComplete');
            }.bind(this));

            this.cardinalClient.setup('init', {
                jwt: this.getRequestJWT()
            });
        },

        /**
         * Returns request order object.
         *
         * The request order object is structured object that is used to pass data
         * to Cardinal that describes an order you'd like to process.
         *
         * If you pass a request object in both the JWT and the browser,
         * Cardinal will merge the objects together where the browser overwrites
         * the JWT object as it is considered the most recently captured data.
         *
         * @param {Object} cardData
         * @returns {Object}
         */
        getRequestOrderObject: function (cardData) {
            var totalAmount = quote.totals()['base_grand_total'],
                currencyCode = quote.totals()['base_currency_code'],
                billingAddress = quote.billingAddress(),
                requestObject;

            requestObject = {
                OrderDetails: {
                    Amount: totalAmount * 100,
                    CurrencyCode: currencyCode
                },
                Consumer: {
                    Account: {
                        AccountNumber: cardData.accountNumber,
                        ExpirationMonth: cardData.expMonth,
                        ExpirationYear: cardData.expYear,
                        CardCode: cardData.cardCode
                    },
                    BillingAddress: {
                        FirstName: billingAddress.firstname,
                        LastName: billingAddress.lastname,
                        Address1: billingAddress.street[0],
                        Address2: billingAddress.street[1],
                        City: billingAddress.city,
                        State: billingAddress.region,
                        PostalCode: billingAddress.postcode,
                        CountryCode: billingAddress.countryId,
                        Phone1: billingAddress.telephone
                    }
                }
            };

            return requestObject;
        },

        /**
         * Returns request JWT
         * @returns {String}
         */
        getRequestJWT: function () {
            return window.checkoutConfig.cardinal.requestJWT;
        },

        /**
         * Returns type of environment
         * @returns {String}
         */
        getEnvironment: function () {
            return window.checkoutConfig.cardinal.environment;
        }
    };
});
