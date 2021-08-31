# Magento_LoginAsCustomerApi module

This module provides API for ability to login into customer account for an admin user.

### Public APIs

- `\Magento\LoginAsCustomerApi\Api\Data\AuthenticationDataInterface`:
    - contains authentication data
  
- `\Magento\LoginAsCustomerApi\Api\DataAuthenticationDataInterface`:
    - authentication data

- `\Magento\LoginAsCustomerApi\Api\AuthenticateCustomerBySecretInterface`:
    - authenticate a customer by secret

- `\Magento\LoginAsCustomerApi\Api\ConfigInterface`:
    - check if Login as Customer extension is enabled
    - check if store view manual choice is enabled
    - get authentication data expiration time (in seconds)

- `\Magento\LoginAsCustomerApi\Api\DeleteAuthenticationDataForUserInterface`:
    - delete authentication data by user id

- `\Magento\LoginAsCustomerApi\Api\GetAuthenticationDataBySecretInterface`:
    - get authentication data by secret

- `\Magento\LoginAsCustomerApi\Api\IsLoginAsCustomerSessionActiveInterface`:
    - check if Login as Customer session is still active

- `\Magento\LoginAsCustomerApi\Api\SaveAuthenticationDataInterface`:
    - save authentication data. Return secret key

For information about a public API in Magento 2, see [Public interfaces & APIs](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/api-concepts.html).

## Additional information

This module is a part of Login As Customer feature.

[Learn more about Login As Customer feature](https://docs.magento.com/user-guide/customers/login-as-customer.html).
