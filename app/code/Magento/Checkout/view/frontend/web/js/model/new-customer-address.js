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
        return {
            email: addressData.email,
            countryId: addressData.country_id,
            regionId: addressData.region.region_id,
            regionCode: addressData.region.region_code,
            region: addressData.region.region,
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
            isDefaultShipping: addressData.default_shipping,
            isDefaultBilling: addressData.default_billing,
            sameAsBilling: addressData.same_as_billing,
            saveInAddressBook: addressData.save_in_address_book,
            getType: function() {
                return 'new-customer-address'
            },
            getKey: function() {
                return this.getType();
            }
        }
    }
});
