# Magento_Variable module

The **Magento_Variable** module allows creating custom variables and then use them in email templates or in WYSIWYG editor for editing description of system entities.

## Installation details

Before installing this module, note that the **Magento_Variable** is dependent on the following modules:

- Magento_Config
- Magento_Store

Before disabling or uninstalling this module, please consider the following dependencies:

- Magento_Cms
- Magento_Email

Please find here [how to enable or disable modules in Magento 2](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

### Layouts

This module introduces the following layouts and layout handles in the directories:

- `view/adminhtml/layout`:
    - `adminhtml_system_variable_edit`
    - `adminhtml_system_variable_grid_block`
    - `adminhtml_system_variable_index`
    
For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

### UI components

You can extend a customer form and widgets using the configuration files located in the directories

- `view/adminhtml/ui_component`:
    - `variables_modal`
    
For information about a UI component in Magento 2, see [Overview of UI components](https://devdocs.magento.com/guides/v2.4/ui_comp_guide/bk-ui_comps.html).


