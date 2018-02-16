/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'underscore'
], function (_) {
    'use strict';

    /**
     * @param {Object} addressData
     * Returns new address object
     */
    return function (addressData) {
        var identifier = Date.now(),
            regionId;

        if (addressData.region && addressData.region.region_id) {
            regionId = addressData.region.region_id;
        } else if (addressData.country_id && addressData.country_id == window.checkoutConfig.defaultCountryId) {
            regionId = window.checkoutConfig.defaultRegionId || undefined;
        }

        return {
            email: addressData.email,
            countryId: addressData['country_id'] || addressData.countryId || window.checkoutConfig.defaultCountryId,
            regionId: regionId || addressData.regionId,
            regionCode: (addressData.region) ? addressData.region.region_code : null,
            region: (addressData.region) ? addressData.region.region : null,
            customerId: addressData.customer_id || addressData.customerId,
            street: addressData.street ? _.compact(addressData.street) : addressData.street,
            company: addressData.company,
            telephone: addressData.telephone,
            fax: addressData.fax,
            postcode: addressData.postcode ? addressData.postcode : window.checkoutConfig.defaultPostcode || undefined,
            city: addressData.city,
            firstname: addressData.firstname,
            lastname: addressData.lastname,
            middlename: addressData.middlename,
            prefix: addressData.prefix,
            suffix: addressData.suffix,
            vatId: addressData.vat_id,
            saveInAddressBook: addressData.save_in_address_book,
            customAttributes: addressData.custom_attributes,
            isDefaultShipping: function () {
                return addressData.default_shipping;
            },
            isDefaultBilling: function () {
                return addressData.default_billing;
            },
            getType: function () {
                return 'new-customer-address';
            },
            getKey: function () {
                return this.getType();
            },
            getCacheKey: function () {
                return this.getType() + identifier;
            },
            isEditable: function () {
                return true;
            },
            canUseForBilling: function () {
                return true;
            }
        }
    }
});
