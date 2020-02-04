# Magento_Backend module

The Magento_Backend module contains common infrastructure and assets for other modules to be defined and used in their
administration user interface (UI).

The Magento_Backend module does not contain anything specific to other modules. Among many things it handles the logic of authenticating and authorizing users.

## Installation details

Before disabling or uninstalling this module, note that the following modules depends on this module:

- Magento_Analytics
- Magento_Authorization
- Magento_NewRelicReporting
- Magento_ProductVideo
- Magento_ReleaseNotification
- Magento_Search
- Magento_Security
- Magento_Signifyd
- Magento_Swatches
- Magento_Ui
- Magento_User
- Magento_Webapi

For information about module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.3/install-gde/install/cli/install-cli-subcommands-enable.html).

## Structure

Beyond the [usual module file structure](https://devdocs.magento.com/guides/v2.3/architecture/archi_perspectives/components/modules/mod_intro.html) the module contains a directory `Service/V1`.

`Service/V1` - contains logic to provide a list of modules installed in Magento.

For information about typical file structure of a module in Magento 2, see [Module file structure](https://devdocs.magento.com/guides/v2.3/extension-dev-guide/build/module-file-structure.html#module-file-structure).

## Extensibility

Extension developers can interact with the Magento_Backend module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.3/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.3/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Backend module.

### Events

The module dispatches the following events:

 - `adminhtml_block_html_before` event in the `\Magento\Backend\Block\Template::_toHtml()` method. Parameters:
   - `block` is the backend block template (this) (`\Magento\Backend\Block\Template` class).
 - `adminhtml_store_edit_form_prepare_form` event in the `\Magento\Backend\Block\System\Store\Edit\AbstractForm::_prepareForm()` method. Parameters:
   - `block` is the AbstractForm block (this) (`\Magento\Backend\Block\System\Store\Edit\AbstractForm` class).
 - `backend_block_widget_grid_prepare_grid_before` event in the `\Magento\Backend\Block\Widget\Grid::_prepareGrid()` method. Parameters:
    - `grid` is the widget grid block (this) (`\Magento\Backend\Block\Widget\Grid` class)
    - `collection` is the grid collection (`\Magento\Framework\Data\Collection` class).
 - `adminhtml_cache_flush_system` event in the `\Magento\Backend\Console\Command\CacheCleanCommand::performAction()` method.
 - `adminhtml_cache_flush_all` event in the `\Magento\Backend\Console\Command\CacheFlushCommand::performAction()` method.
 - `clean_catalog_images_cache_after` event in the `\Magento\Backend\Controller\Adminhtml\Cache\CleanImages::execute()` method.
 - `clean_media_cache_after` event in the `\Magento\Backend\Controller\Adminhtml\Cache\CleanMedia::execute()` method.
 - `clean_static_files_cache_after` event in the `\Magento\Backend\Controller\Adminhtml\Cache\CleanStaticFiles::execute()` method.
 - `adminhtml_cache_flush_all` event in the `\Magento\Backend\Controller\Adminhtml\Cache\FlushAll::execute()` method.
 - `adminhtml_cache_flush_system` event in the `\Magento\Backend\Controller\Adminhtml\Cache\FlushSystem::execute()` method.
 - `theme_save_after` event in the `\Magento\Backend\Controller\Adminhtml\System\Design\Save::execute()` method.
 - `backend_auth_user_login_success` event in the `\Magento\Backend\Model\Auth::login()` method. Parameters:
    - `user` is the credential storage object (`null | \Magento\Backend\Model\Auth\Credential\StorageInterface`)
 - `backend_auth_user_login_failed` event in the `\Magento\Backend\Model\Auth::login()` method. Parameters:
    - `user_name` is username extracted from the credential storage object (`null | \Magento\Backend\Model\Auth\Credential\StorageInterface`)
    - `exception` any exception generated (`\Magento\Framework\Exception\LocalizedException | \Magento\Framework\Exception\Plugin\AuthenticationException`)

For information about an event in Magento 2, see [Events and observers](https://devdocs.magento.com/guides/v2.3/extension-dev-guide/events-and-observers.html#events).

### Layouts

This module introduces the following layouts and layout handles in the `view/adminhtml/layout` directory:

- `admin_login`
- `adminhtml_auth_login`
- `adminhtml_cache_block`
- `adminhtml_cache_index`
- `adminhtml_dashboard_customersmost`
- `adminhtml_dashboard_customersnewest`
- `adminhtml_dashboard_index`
- `adminhtml_dashboard_productsviewed`
- `adminhtml_denied`
- `adminhtml_noroute`
- `adminhtml_system_account_index`
- `adminhtml_system_design_edit`
- `adminhtml_system_design_grid`
- `adminhtml_system_design_grid_block`
- `adminhtml_system_design_index`
- `adminhtml_system_store_deletestore`
- `adminhtml_system_store_editstore`
- `adminhtml_system_store_grid_block`
- `adminhtml_system_store_index`
- `default`
- `editor`
- `empty`
- `formkey`
- `overlay_popup`
- `popup`


For more information about layouts in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.3/frontend-dev-guide/layouts/layout-overview.html).

### UI components

You can extend Magento_Backend module using the following configuration files:

- `view/adminhtml/ui_component/design_config_form.xml`
- `view/adminhtml/ui_component/design_config_listing.xml`

For information about UI components in Magento 2, see [Overview of UI components](https://devdocs.magento.com/guides/v2.3/ui_comp_guide/bk-ui_comps.html).

## Additional information

For information about significant changes in patch releases, see [2.3.x Release information](https://devdocs.magento.com/guides/v2.3/release-notes/bk-release-notes.html).
