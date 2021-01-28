# Magento_LoginAsCustomerApi module

This module provides API for ability to login into customer account for an admin user.

## Installation

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_LoginAsCustomerApi module. For more information about the Magento extension mechanism, see [Magento plugins](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_LoginAsCustomerApi module.

### Public APIs

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
