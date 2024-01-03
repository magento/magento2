# Magento_LoginAsCustomerApi module

This module provides API for ability to login into customer account for an admin user.

### Public APIs

- `\Magento\LoginAsCustomerApi\Api\Data\AuthenticationDataInterface`:
    - contains authentication data

-`\Magento\LoginAsCustomerApi\Api\Data\IsLoginAsCustomerEnabledForCustomerResultInterface`:
    - contains the result of the check whether the login as customer is enabled

- `\Magento\LoginAsCustomerApi\Api\AuthenticateCustomerBySecretInterface`:
    - authenticate a customer by secret

- `\Magento\LoginAsCustomerApi\Api\ConfigInterface`:
    - check if Login as Customer extension is enabled
    - check if store view manual choice is enabled
    - get authentication data expiration time (in seconds)

- `\Magento\LoginAsCustomerApi\Api\DeleteAuthenticationDataForUserInterface`:
    - delete authentication data by user id

- `\Magento\LoginAsCustomerApi\Api\GenerateAuthenticationSecretInterface`:
    - generate authentication secret

- `\Magento\LoginAsCustomerApi\Api\GetAuthenticationDataBySecretInterface`:
    - get authentication data by secret

- `\Magento\LoginAsCustomerApi\Api\GetLoggedAsCustomerAdminIdInterface`:
    - get id of admin logged as customer

- `\Magento\LoginAsCustomerApi\Api\GetLoggedAsCustomerCustomerIdInterface`:
    - get id of customer admin is logged as
  
- `\Magento\LoginAsCustomerApi\Api\IsLoginAsCustomerEnabledForCustomerInterface`:
    - check if login as customer functionality is enabled for customer

- `\Magento\LoginAsCustomerApi\Api\IsLoginAsCustomerSessionActiveInterface`:
    - check if Login as Customer session is still active

- `\Magento\LoginAsCustomerApi\Api\SaveAuthenticationDataInterface`:
    - save authentication data. Return secret key

- `\Magento\LoginAsCustomerApi\Api\SetLoggedAsCustomerAdminIdInterface`:
    - set id of admin logged as customer

- `\Magento\LoginAsCustomerApi\Api\SetLoggedAsCustomerCustomerIdInterface`:
    - set id of customer admin is logged as

For information about a public API in Magento 2, see [Public interfaces & APIs](https://developer.adobe.com/commerce/php/development/components/api-concepts/).

## Additional information

This module is a part of Login As Customer feature.

[Learn more about Login As Customer feature](https://experienceleague.adobe.com/docs/commerce-admin/customers/customer-accounts/manage/login-as-customer.html).
