# Magento_Config module

The Magento_Config module is designed to implement system configuration functionality.
It provides mechanisms to add, edit, store and retrieve the configuration data for each scope (there can be a default scope as well as scopes for each website and store).

The Magento_Config module adds the possibility for other modules to add items to be configured on the system configuration page by creating system.xml files in their etc/adminhtml directories. These system.xml files get merged to populate the forms in the config page.

## Installation details

The Magento_Config module is one of the base Magento 2 modules. You cannot disable or uninstall this module.

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Structure

`App/` - the directory that declares config types and config sources.

For information about a typical file structure of a module in Magento 2, see [Module file structure](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/build/module-file-structure.html#module-file-structure).

## Extensibility

Extension developers can interact with the Magento_Config module. For more information about the Magento extension mechanism, see [Magento plugins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Config module.

### Events

The module dispatches the following events:

- `adminhtml_system_config_advanced_disableoutput_render_before` event in the `\Magento\Config\Block\System\Config\Form\Fieldset\Modules\DisableOutput::render` method. Parameters:
    - `modules` - a modules list data object(`\Magento\Framework\DataObject` class).
- `admin_system_config_save` event in the `\Magento\Config\Controller\Adminhtml\System\Config\Save::execute` method. Parameters:
    - `configData` - data list (`array` type).
    - `request` - request data, instance of `\Magento\Framework\App\RequestInterface`.
- `admin_system_config_changed_section_` event in the `\Magento\Config\Model\Config::save` method. Parameters:
    - `website` - website code (`string` type).
    - `store` - store code (`string` type).
    - `changed_paths` - data list (`array` type).

### Layouts

The module introduces layout handles in the `view/adminhtml/layout` directory.

For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

## Additional information

You can get more information at the articles:
- [Manage system.xml reference](https://devdocs.magento.com/guides/v2.4/config-guide/prod/config-reference-systemxml.html)
- [2.4.x Release information](https://devdocs.magento.com/guides/v2.4/release-notes/bk-release-notes.html)
