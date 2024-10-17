## Magento_InstantPurchase module

This module allows the Customer to place the order in seconds without going through full checkout. Once clicked, system places the order using default shipping and billing addresses and stored payment method. Order is placed and customer gets confirmation message in notification area.

## Installation

For information about a module installation in Magento 2, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Structure

`PaymentMethodsIntegration` - directory contains interfaces and basic implementation of integration vault payment method to the instant purchase.

For information about a typical file structure of a module in Magento 2, see [Module file structure](https://developer.adobe.com/commerce/php/development/build/component-file-structure/#module-file-structure).

## Extensibility

Extension developers can interact with the Magento_InstantPurchase module. For more information about the Magento extension mechanism, see [Magento plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_InstantPurchase module.

### Public APIs

- `\Magento\InstantPurchase\Model\BillingAddressChoose\BillingAddressChooserInterface`
    - choose billing address for a customer if available

- `\Magento\InstantPurchase\Model\PaymentMethodChoose\PaymentTokenChooserInterface`
    - choose one of the stored payment methods for a customer if available

- `\Magento\InstantPurchase\Model\ShippingAddressChoose\ShippingAddressChooserInterface`
    - choose shipping address for a customer if available

- `\Magento\InstantPurchase\Model\ShippingMethodChoose\DeferredShippingMethodChooserInterface`
    - choose shipping method for a quote address

- `\Magento\InstantPurchase\Model\ShippingMethodChoose\ShippingMethodChooserInterface`
    - choose shipping method for customer address if available

- `\Magento\InstantPurchase\Model\InstantPurchaseInterface`
    - detects instant purchase options for a customer in a store

- `\Magento\InstantPurchase\PaymentMethodIntegration\AvailabilityCheckerInterface`
    - checks if payment method may be used for instant purchase

- `\Magento\InstantPurchase\PaymentMethodIntegration\PaymentAdditionalInformationProviderInterface`
    - provides additional information part specific for payment method

- `\Magento\InstantPurchase\PaymentMethodIntegration\PaymentTokenFormatterInterface`
    - provides mechanism to create string presentation of token for payment method

For information about a public API in Magento 2, see [Public interfaces & APIs](https://developer.adobe.com/commerce/php/development/components/api-concepts/).

## Additional information

### Instant purchase customization

Almost all aspects of instant purchase may be customized. See comments to classes and interfaces marked with `@api` tag.

All payments created for instant purchase also have `'instant-purchase' => true` in addition information. Use this only if all other customization points not suitable,

### Payment method integration

Instant purchase support may be implemented for any payment method with [vault support](https://developer.adobe.com/commerce/php/development/payments-integrations/vault/).
Basic implementation provided in `Magento\InstantPurchase\PaymentMethodIntegration` should be enough in most cases. It is not enabled by default to avoid issues on production sites and authors of vault payment method should verify correct work for instant purchase manually.
To enable basic implementation just add single option to configuration of payemnt method in `config.xml`:

```xml
<instant_purchase>
    <supported>1</supported>
</instant_purchase>
```

Basic implementation is a good start point but it's recommended to provide own implementation to improve user experience. If instant purchase integration has customization then `supported` option is not required.

```xml
<instant_purchase>
    <available>Implementation_Of_Magento\InstantPurchase\PaymentMethodIntegration\AvailabilityCheckerInterface</available>
    <tokenFormat>Implementation_Of_Magento\InstantPurchase\PaymentMethodIntegration\PaymentTokenFormatterInterface</tokenFormat>
    <additionalInformation>Implementation_Of_Magento\InstantPurchase\PaymentMethodIntegration\PaymentAdditionalInformationProviderInterface</additionalInformation>
</instant_purchase>
```

- `Magento\InstantPurchase\PaymentMethodIntegration\AvailabilityCheckerInterface` - allows programmatically defines if instant purchase supported (e.g. support may not be available if some payment method option switched on/off). Basic implementation always returns `true`.
- `Magento\InstantPurchase\PaymentMethodIntegration\PaymentTokenFormatterInterface` - creates string that describes stored payment method. Basic implementation returns payment method name. It is highly recommended to implement own formatter.
- `Magento\InstantPurchase\PaymentMethodIntegration\PaymentAdditionalInformationProviderInterface` - allows to add some extra values to payment additional information array. Default implementation returns empty array.

### Prerequisites to display the Instant Purchase button

1. Instant purchase enabled for a store at `Store / Configurations / Sales / Sales / Instant Purchase`
2. Customer is logged in
3. Customer has default shipping and billing address defined
4. Customer has valid stored payment method with instant purchase support

[Learn more about Instant Purchase](https://experienceleague.adobe.com/docs/commerce-admin/stores-sales/point-of-purchase/checkout-instant-purchase.html).

### Backward incompatible changes

The `Magento_InstantPurchase` module does not introduce backward incompatible changes.

You can track [backward incompatible changes in patch releases](https://developer.adobe.com/commerce/php/development/backward-incompatible-changes/highlights/).

***

This module was initially developed by the [Creatuity Corp.](https://creatuity.com/) and [Magento Community Engineering Team](mailto:engcom@magento.com).
