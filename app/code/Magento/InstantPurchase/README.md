# Magento_InstantPurchase module

This module allows the customer to place the order in seconds without going through full checkout. Once clicked, system places the order using default shipping and billing addresses and stored payment method. Order is placed and customer gets confirmation message in the notification area.

## Installation

For information about a module installation, see [Enable or disable modules](https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/tutorials/manage-modules).

## Structure

`PaymentMethodsIntegration/` - directory that contains interfaces and basic implementation of integration vault payment method to the instant purchase.

For information about a typical file structure of a module, see [Module file structure](https://developer.adobe.com/commerce/php/development/build/component-file-structure/#module-file-structure).

## Extensibility

Extension developers can interact with this module. For more information about the extension mechanism, see [Plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of this module.

### Public APIs

- `\Magento\InstantPurchase\Model\BillingAddressChoose\BillingAddressChooserInterface`
    - chooses billing address for a customer if available

- `\Magento\InstantPurchase\Model\PaymentMethodChoose\PaymentTokenChooserInterface`
    - chooses one of the stored payment methods for a customer if available

- `\Magento\InstantPurchase\Model\ShippingAddressChoose\ShippingAddressChooserInterface`
    - chooses shipping address for a customer if available

- `\Magento\InstantPurchase\Model\ShippingMethodChoose\DeferredShippingMethodChooserInterface`
    - chooses shipping method for a quote address

- `\Magento\InstantPurchase\Model\ShippingMethodChoose\ShippingMethodChooserInterface`
    - chooses shipping method for customer address if available

- `\Magento\InstantPurchase\Model\InstantPurchaseInterface`
    - detects instant purchase options for a customer in a store

- `\Magento\InstantPurchase\PaymentMethodIntegration\AvailabilityCheckerInterface`
    - checks whether payment method may be used for instant purchase

- `\Magento\InstantPurchase\PaymentMethodIntegration\PaymentAdditionalInformationProviderInterface`
    - provides additional information part specific to payment method

- `\Magento\InstantPurchase\PaymentMethodIntegration\PaymentTokenFormatterInterface`
    - provides mechanism to create string presentation of token for payment method

For information about a public API, see [Public interfaces & APIs](https://developer.adobe.com/commerce/php/development/components/api-concepts/).

## Additional information

### Instant purchase customization

Almost all aspects of instant purchase may be customized. See comments to classes and interfaces marked with `@api` tag.

All payments created for instant purchase also have `'instant-purchase' => true` in addition information. Use this only if all other customization points are not suitable,

### Payment method integration

Instant purchase support may be implemented for any payment method with [vault support](https://developer.adobe.com/commerce/php/development/payments-integrations/vault/).
Basic implementation provided in `Magento\InstantPurchase\PaymentMethodIntegration` should be enough in most cases. It is not enabled by default to avoid issues on production sites and authors of vault payment methods should verify correct work for instant purchase manually.
To enable basic implementation, just add a single option to the configuration of the payment method in `config.xml`:

```xml
<instant_purchase>
    <supported>1</supported>
</instant_purchase>
```

Basic implementation is a good starting point, but it's recommended to provide your own implementation to improve user experience. If instant purchase integration has customization, then the `supported` option is not required.

```xml
<instant_purchase>
    <available>Implementation_Of_Magento\InstantPurchase\PaymentMethodIntegration\AvailabilityCheckerInterface</available>
    <tokenFormat>Implementation_Of_Magento\InstantPurchase\PaymentMethodIntegration\PaymentTokenFormatterInterface</tokenFormat>
    <additionalInformation>Implementation_Of_Magento\InstantPurchase\PaymentMethodIntegration\PaymentAdditionalInformationProviderInterface</additionalInformation>
</instant_purchase>
```

- `Magento\InstantPurchase\PaymentMethodIntegration\AvailabilityCheckerInterface` - allows programmatically defining whether instant purchase is supported (e.g. support may not be available if some payment method option is switched on/off). The basic implementation always returns `true`.
- `Magento\InstantPurchase\PaymentMethodIntegration\PaymentTokenFormatterInterface` - creates a string that describes the stored payment method. The basic implementation returns the payment method name. It is highly recommended to implement your own formatter.
- `Magento\InstantPurchase\PaymentMethodIntegration\PaymentAdditionalInformationProviderInterface` - allows adding some extra values to the payment additional information array. The default implementation returns an empty array.

### Prerequisites to display the Instant Purchase button

1. Instant purchase is enabled for a store at `Store / Configurations / Sales / Sales / Instant Purchase`
2. Customer is logged in
3. Customer has default shipping and billing address defined
4. Customer has valid stored payment method with instant purchase support

[Learn more about Instant Purchase](https://experienceleague.adobe.com/en/docs/commerce-admin/stores-sales/point-of-purchase/checkout-instant-purchase).

### Backward incompatible changes

This module does not introduce backward incompatible changes.

You can track [backward incompatible changes in patch releases](https://developer.adobe.com/commerce/php/development/backward-incompatible-changes/).

***

This module was initially developed by the [Creatuity Corp.](https://creatuity.com/) and [Magento Community Engineering Team](https://commercemarketplace.adobe.com/partner/engcom/).
