/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([], function() {
    return function (addressData) {
        return {
            id: null,
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
            customerAddressId: addressData.id,
            sameAsBilling: null,
            getFullAddress: function() {
                var address = '';
                address += (addressData.firstname && addressData.lastname)
                    ? addressData.firstname + ' ' + addressData.lastname + ', ' : '';
                for (item in addressData.street) {
                    address += addressData.street[item] ? addressData.street[item] + ', ' : '';
                }
                address += addressData.city ? addressData.city + ', ' : '';
                if (addressData.region.region && addressData.postcode) {
                     address += addressData.region.region + ' ' + addressData.postcode + ', ';
                } else {
                    address += addressData.region.region ? addressData.region.region + ', ' : '';
                    address += addressData.postcode ? addressData.postcode + ', ' : '';
                }
                address += addressData.country_id ? addressData.country_id : '';
                return address;
            }
        }
    }
});
