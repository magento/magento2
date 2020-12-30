# Magento_Theme module

The **Magento_Theme** module contains common infrastructure that provides an ability to apply and use themes in Magento application.

## Installation details

Before installing this module, note that the **Magento_Theme** is dependent on the following modules:

- Magento_Cms
- Magento_Config
- Magento_Customer
- Magento_Directory
- Magento_Eav
- Magento_MediaStorage
- Magento_Store
- Magento_Widget

Before disabling or uninstalling this module, please consider the following dependencies:

- Magento_Backend
- Magento_Cms
- Magento_Eav
- Magento_MediaStorage
- Magento_Robots
- Magento_Swatches
- Magento_Vault

Please find here [how to enable or disable modules in Magento 2](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Structure

`CustomerData/` - directory contains messages section.

For information about a typical file structure of a module in Magento 2, see [Module file structure](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/build/module-file-structure.html#module-file-structure).

## Extensibility

Extension developers can interact with the **Magento_Theme** module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the **Magento_Theme** module.

### Events

The module dispatches the following events:

- `page_block_html_topmenu_gethtml_before` event in the `\Magento\Theme\Block\Html\Topmenu::getHtml()` method. Parameters:
    - `menu` is a menu object (`\Magento\Framework\Data\Tree\Node` class).
    - `block` is a Topmenu object (`\Magento\Theme\Block\Html\Topmenu` class).
    - `request` is a request object (`\Magento\Framework\App\RequestInterface` class).
- `page_block_html_topmenu_gethtml_after` event in the `\Magento\Theme\Block\Html\Topmenu::getHtml()` method. Parameters:
    - `menu` is a menu object (`\Magento\Framework\Data\Tree\Node` class).
    - `transportObject` is a Transport object (`\Magento\Framework\DataObject` class).
- `admin_system_config_changed_section_design` event in the `\Magento\Theme\Model\Design\Config\Plugin::afterSave()` method. Parameters:
    - `website` is a website object (`\Magento\Store\Api\Data\WebsiteInterface` class).
    - `store` is a store object (`\Magento\Store\Api\Data\StoreInterface` class).
- `admin_system_config_changed_section_design` event in the `\Magento\Theme\Model\Design\Config\Plugin::afterDelete()` method. Parameters:
    - `website` is a website object (`\Magento\Store\Api\Data\WebsiteInterface` class).
    - `store` is a store object (`\Magento\Store\Api\Data\StoreInterface` class).
- `admin_system_config_changed_section_design` event in the `\Magento\Theme\Model\Config::assignToStore()` method. Parameters:
    - `store` is a store object (`\Magento\Store\Api\Data\StoreInterface` class).
    - `scope` is a store scope.
    - `theme` is a theme object (`\Magento\Framework\View\Design\ThemeInterface` class).
- `assigned_theme_changed` event in the `\Magento\Theme\Observer\CheckThemeIsAssignedObserver::execute()` method. Parameters:
    - `theme` is a theme object (`\Magento\Framework\View\Design\ThemeInterface` class).

For information about the event system in Magento 2, see [Events and observers](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/events-and-observers.html#events).

### Layouts

This module introduces the following layouts and layout handles in the directories:

- `view/adminhtml/layout`:
    - `adminhtml_system_design_theme_block`
    - `adminhtml_system_design_theme_edit`
    - `adminhtml_system_design_theme_grid`
    - `adminhtml_system_design_theme_index`
    - `adminhtml_system_design_wysiwyg_files_contents`
    - `adminhtml_system_design_wysiwyg_files_index`
    - `theme_design_config_edit`
    - `theme_design_config_index`
- `view/frantend/layout`:
    - `default`
    - `default_head_blocks`
    - `page_calendar`
    - `print`

For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

### UI components

You can extend a customer form and widgets using the configuration files located in the directories 

- `view/adminhtml/ui_component`:
    - `design_config_form`
    - `design_config_listing`
    - `design_theme_listing`

For information about a UI component in Magento 2, see [Overview of UI components](https://devdocs.magento.com/guides/v2.4/ui_comp_guide/bk-ui_comps.html).

### Public APIs

`\Magento\Theme\Api\DesignConfigRepositoryInterface`:

   - Get design settings by scope.
   - Save design settings.
   - Delete design settings.

[Learn detailed description of the Magento_Sales API.](https://devdocs.magento.com/guides/v2.4/mrg/ce/Sales/services.html)

For information about a public API in Magento 2, see [Public interfaces & APIs](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/api-concepts.html).
