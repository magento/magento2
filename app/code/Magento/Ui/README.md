# Magento_Ui module

The Magento_Ui module introduces a set of common UI components, which could be used and configured via layout XML files.

## Installation details

The Magento_Ui module can be installed automatically (using the native Magento Setup)) without any additional actions.

Before installing this module, note that the Magento_Ui is dependent on the following modules:

- Magento_Authorization
- Magento_Backend
- Magento_Directory
- Magento_Eav
- Magento_Store
- Magento_User

Before disabling or uninstalling this module, note the following dependencies:

- Magento_GoogleOptimizer
- Magento_MediaGalleryIntegration
- Magento_ReleaseNotification
- Magento_Shipping

For information about enabling or disabling a module, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Structure

- `Component/` - directory that contains several component implementations.
- `Config/` - directory that contains configuration files for Argument, Converter, and Reader.

For information about a typical file structure of a module, see [Module file structure](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/build/module-file-structure.html#module-file-structure).

### Layouts

This module introduces the following layouts and layout handles in the directories:

- `view/base/layout`:
    - `default`

For more information about a layout, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

### UI components

You can extend a customer form and widgets using the configuration files located in the directories 

- `view/base/ui_component`:
    - `etc`:
        - `definition.map`
        - `definition`
    - `templates`:
        - `container`:
            - `default`
        - `export`:
            - `button`
        - `form`:
            - `collapsible`
            - `default`
        - `listing`:
            - `default`

For information about a UI component, see [Overview of UI components](https://devdocs.magento.com/guides/v2.4/ui_comp_guide/bk-ui_comps.html).

### Public APIs

`\Magento\Ui\Api\BookmarkManagementInterface`:

    - Retrieve list of bookmarks by namespace.
    - Retrieve bookmark by a identifier and namespace.

`\Magento\Ui\Api\BookmarkRepositoryInterface`:

    - Save bookmark.
    - Retrieve bookmark.
    - Retrieve bookmarks matching the specified criteria.
    - Delete bookmark.
    - Delete bookmark by ID.

[Learn detailed description of the Magento_Sales API.](https://devdocs.magento.com/guides/v2.4/mrg/ce/Sales/services.html)

For information about a public API, see [Public interfaces & APIs](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/api-concepts.html).  
