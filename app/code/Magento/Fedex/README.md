# Magento_Fedex module

This module implements the integration with the FedEx shipping carrier.

## Installation details

For information about a module installation, see [Enable or disable modules](https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/tutorials/manage-modules).

## Extensibility

Extension developers can interact with the Magento_Fedex module. For more information about the extension mechanism, see [Plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_Fedex module.

A lot of functionality in the module is on JavaScript, use [mixins](https://developer.adobe.com/commerce/frontend-core/javascript/mixins/) to extend it.

### Layouts

This module introduces the following layouts in the `view/frontend/layout` directory:

- `checkout_cart_index`
- `checkout_index_index`

## Additional information

You can get more information about delivery method at the following articles:

- [FedEx Configuration Settings](https://experienceleague.adobe.com/en/docs/commerce-admin/stores-sales/delivery/shipping-carriers/fedex)
- [Delivery Methods Configuration](https://experienceleague.adobe.com/en/docs/commerce-admin/config/sales/delivery-methods)
- [Add custom shipping carrier](https://developer.adobe.com/commerce/php/tutorials/frontend/custom-checkout/add-shipping-carrier/)
