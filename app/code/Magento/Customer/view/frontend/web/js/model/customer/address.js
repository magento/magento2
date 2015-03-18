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
            id: addressData.id,
            email: addressData.email,
            country_id: addressData.country_id,
            region_id: addressData.region.region_id,
            region_code: addressData.region.region_code,
            region: addressData.region.region,
            customer_id: addressData.customer_id,
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
            vat_id: addressData.vat_id,
            getFullName: function() {
                return addressData.region.region + ', ' + addressData.street[0] + ', ' + addressData.city;
            }
        }
    }
});
