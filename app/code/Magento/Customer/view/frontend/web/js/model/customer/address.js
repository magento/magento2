/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([], function() {
    return function (addressData) {
        return {
            customerAddressId: addressData.id,
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
            sameAsBilling: null,
            getAddressInline: function() {
                return addressData.inline;
            }
        }
    }
});
