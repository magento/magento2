# Magento_ConfigurableProduct module

This module introduces a new product type called Configurable Product. It extends the functionality of the Magento_Catalog module by adding this new product type.

Configurable products allow customers to select a variant by choosing options.
For example, store owner sells t-shirts in two colors and three sizes.

## Structure

`ConfigurableProduct/` - the directory that declares ConfigurableProduct metadata used by the module.

For information about a typical file structure of a module, see [Module file structure](https://developer.adobe.com/commerce/php/development/build/component-file-structure/#module-file-structure).

## Extensibility

Extension developers can interact with the Magento_ConfigurableProduct module. For more information about the extension mechanism, see [Plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_ConfigurableProduct module.

## Additional information

### Configurable variables through the theme view.xml

Modify the value of the `gallery_switch_strategy` variable in the theme `view.xml` file to configure how gallery images should be updated when a user switches between product configurations.

Learn how to [configure variables](https://developer.adobe.com/commerce/frontend-core/guide/themes/configure/#configure-variables) in the `view.xml` file.

There are two available values for the `gallery_switch_strategy` variable:

| Value | Description |
|-------|-------------|
| `replace` | In replace mode, images of the parent configurable product will be replaced by the simple product images upon a configuration change. |
| `prepend` | In prepend mode, the simple product images will be added in front of the parent configurable product upon a configuration change. |

If the `gallery_switch_strategy` variable is not defined, the default value `replace` is used.

For example, adding these lines of code to the theme `view.xml` file sets the gallery behavior to `replace` mode.

```xml
<vars module="Magento_ConfigurableProduct">
    <var name="gallery_switch_strategy">replace</var>
</vars>
```
