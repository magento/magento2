## Overview

Instant Purchase feature allows the Customer to place the order in seconds without going through full checkout. Once clicked, system places the order using default shipping and billing addresses and stored payment method. Order is placed and customer gets confirmation message in notification area.

Prerequisites to display the Instant Purchase button:
1. Instant purchase enabled for a store at `Store / Configurations / Sales / Sales / Instant Purchase`
2. Customer is logged in
3. Customer has default shipping and billing address defined
4. Customer has valid stored payment method with instant purchase support

## Structure

In addition to [a typical file structure for a Magento 2 module](https://devdocs.magento.com/guides/v2.2/extension-dev-guide/build/module-file-structure.html) `PaymentMethodsIntegration` directory contains interfaces and basic implementation of integration vault payment method to the instant purchase.

## Extensibility

### Instant purchase customization

Almost all aspects of instant purchase may be customized. See comments to classes and interfaces marked with `@api` tag.

All payments created for instant purchase also have `'instant-purchase' => true` in addition information. Use this only if all other customization points not suitable,

### Payment method integration

Instant purchase support may be implemented for any payment method with [vault support](https://devdocs.magento.com/guides/v2.1/payments-integrations/vault/vault-intro.html).
Basic implementation provided in `Magento\InstantPurchase\PaymentMethodIntegration` should be enough in most cases. It is not enabled by default to avoid issues on production sites and authors of vault payment method should verify correct work for instant purchase manually.
To enable basic implementation just add single option to configuration of payemnt method in `config.xml`:

```
<instant_purchase>
    <supported>1</supported>
</instant_purchase>
```

Basic implementation is a good start point but it's recommended to provide own implementation to improve user experience. If instant purchase integration has customization then `supported` option is not required.

```
<instant_purchase>
    <available>Implementation_Of_Magento\InstantPurchase\PaymentMethodIntegration\AvailabilityCheckerInterface</available>
    <tokenFormat>Implementation_Of_Magento\InstantPurchase\PaymentMethodIntegration\PaymentTokenFormatterInterface</tokenFormat>
    <additionalInformation>Implementation_Of_Magento\InstantPurchase\PaymentMethodIntegration\PaymentAdditionalInformationProviderInterface</additionalInformation>
</instant_purchase>
```

- `Magento\InstantPurchase\PaymentMethodIntegration\AvailabilityCheckerInterface` - allows programmatically defines if instant purchase supported (e.g. support may not be available if some payment method option switched on/off). Basic implementation always returns `true`.
- `Magento\InstantPurchase\PaymentMethodIntegration\PaymentTokenFormatterInterface` - creates string that describes stored payment method. Basic implementation returns payment method name. It is highly recommended to implement own formatter.
- `Magento\InstantPurchase\PaymentMethodIntegration\PaymentAdditionalInformationProviderInterface` - allows to add some extra values to payment additional information array. Default implementation returns empty array.

## Additional information

### Backward incompatible changes

The `Magento_InstantPurchase` module does not introduce backward incompatible changes.

You can track [backward incompatible changes in patch releases](https://devdocs.magento.com/guides/v2.2/release-notes/changes/ce_changes.html).

***

This module was initially developed by the [Creatuity Corp.](https://creatuity.com/) and [Magento Community Engineering Team](mailto:engcom@magento.com).
