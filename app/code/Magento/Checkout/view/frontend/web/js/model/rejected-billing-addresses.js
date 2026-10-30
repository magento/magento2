/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

/**
 * Saved customer addresses the server rejected as the billing address in this checkout.
 *
 * Checkout picks the default billing address again whenever no billing address is selected, for example when the
 * shipping step is saved. A default billing address the server rejects (an invalid telephone number, say) would then
 * be sent again on every try, while it cannot be corrected in checkout. An address is remembered with its contents,
 * so once it is corrected in the address book it is used again.
 *
 * @api
 */
define([
    'underscore',
    'Magento_Checkout/js/checkout-data'
], function (_, checkoutData) {
    'use strict';

    var fields = [
        'prefix', 'firstname', 'middlename', 'lastname', 'suffix', 'company', 'street', 'city', 'region', 'regionId',
        'postcode', 'countryId', 'telephone', 'fax', 'vatId', 'customAttributes'
    ];

    /**
     * @param {Object} address
     * @return {String}
     */
    function contents(address) {
        return JSON.stringify(_.pick(address, fields));
    }

    return {
        /**
         * Remember a rejected saved customer address; addresses entered in checkout are ignored.
         *
         * @param {Object} address
         */
        add: function (address) {
            var rejected;

            if (!address || !address.customerAddressId) {
                return;
            }
            rejected = _.extend({}, checkoutData.getRejectedBillingAddresses());
            rejected[address.getKey()] = contents(address);
            checkoutData.setRejectedBillingAddresses(rejected);
        },

        /**
         * Whether the saved customer address was rejected as the billing address and has not changed since.
         *
         * @param {Object} address
         * @return {Boolean}
         */
        has: function (address) {
            var rejected = checkoutData.getRejectedBillingAddresses() || {};

            return !!address && !!address.customerAddressId &&
                rejected[address.getKey()] === contents(address);
        }
    };
});
