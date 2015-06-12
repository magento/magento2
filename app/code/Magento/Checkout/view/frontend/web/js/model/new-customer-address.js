/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([], function() {
    /**
     * @param addressData
     * Returns new address object
     */
    return function (addressData) {
        var identifier = Date.now();
        return {
            email: addressData.email,
            countryId: addressData.country_id,
            regionId: (addressData.region) ? addressData.region.region_id : null,
            regionCode: (addressData.region) ? addressData.region.region_code : null,
            region: (addressData.region) ? addressData.region.region : null,
            customerId: addressData.customer_id,
            street: addressData.street,
            company: addressData.company,
            telephone: addressData.telephone,
            fax: addressData.fax,
            postcode: addressData.postcode,
            city: addressData.city,
            firstname: addressData.firstname,
            lastname: addressData.lastname,
            middlename: addressData.middlename,
            prefix: addressData.prefix,
            suffix: addressData.suffix,
            vatId: addressData.vat_id,
            sameAsBilling: addressData.same_as_billing,
            saveInAddressBook: addressData.save_in_address_book,
            isDefaultShipping: function() {
                return addressData.default_shipping;
            },
            isDefaultBilling: function() {
                return addressData.default_billing;
            },
            getType: function() {
                return 'new-customer-address'
            },
            getKey: function() {
                return this.getType();
            },
            getCacheKey: function() {
                return this.getType() + identifier;
            },
            isEditable: function() {
                return true;
            },
            canUseForBilling: function() {
                return true;
            }
        }
    }
});
