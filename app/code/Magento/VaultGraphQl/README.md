# Magento_VaultGraphQl module

The Magento_VaultGraphQl module extends Magento_GraphQl and Magento_Vault modules.

The Magento_VaultGraphQl module provides type and resolver information for the GraphQl module to generate Vault (stored payment information) information endpoints.

The Magento_VaultGraphQl module also provides mutations for modifying a payment token.

## Installation details

The Magento_VaultGraphQl is dependent on the following modules:

- Magento_Vault
- Magento_GraphQl

For information about enabling or disabling a module, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Additional information

For more information about the Magento_VaultGraphQl [Queries](#queries) and [Mutations](#mutations) see below:

### Queries {#queries}

- [`customerPaymentTokens`](https://devdocs.magento.com/guides/v2.4/graphql/queries/customer-payment-tokens.html)

### Mutations {#mutations}

- [`deletePaymentToken`](https://devdocs.magento.com/guides/v2.4/graphql/mutations/delete-payment-token.html)
