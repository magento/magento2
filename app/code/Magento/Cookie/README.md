# Magento_Cookie module 

This module allows enabling and configuring HTTP cookie-related settings for the store.
Allows enabling cookie restriction mode.

## Installation details

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_Cookie module. For more information about the Magento extension mechanism, see [Magento plugins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Cookie module.

A lot of functionality in the module is on JavaScript, use [mixins](https://devdocs.magento.com/guides/v2.4/javascript-dev-guide/javascript/js_mixins.html) to extend it.

### Events

The module dispatches the following events:

#### Controller
- `controller_action_nocookies` event in the `\Magento\Cookie\Controller\Index\NoCookies::execute()` method. Parameters:
    - `action` is a `$this` object (`\Magento\Cookie\Controller\Index\NoCookies` class)
    - `redirect` is a `DataObject` (`\Magento\Framework\DataObject()`)

For information about an event in Magento 2, see [Events and observers](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/events-and-observers.html#events).

### Layouts
This module introduces the following layouts in the `view/frontend/layout` and `view/adminhtml/layout` directories:
- `default`

For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

## Additional information

### Configuration

Use the `Stores -> Configuration -> General -> Web -> Default Cookie Settings -> Cookie Restriction Mode` configuration to enable or disable Cookie Restriction Mode.
Use the `Stores -> Configuration -> General -> Web -> Default Cookie Settings -> Cookie Lifetime` configuration to set Cookie Lifetime.
Use the `Stores -> Configuration -> General -> Web -> Default Cookie Settings -> Cookie Path` configuration to set Cookie Path.
Use the `Stores -> Configuration -> General -> Web -> Default Cookie Settings -> Cookie Domain` configuration to set Cookie Domain.
Use the `Stores -> Configuration -> General -> Web -> Default Cookie Settings -> Use HTTP Only` configuration to enable or disable use Http only.
