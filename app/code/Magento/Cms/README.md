# Magento_Cms module

Magento_Cms module provides the creating, edit, and manage functionality on pages for different content types.

## Installation details

The Magento_Cms module is one of the base Magento 2 modules. Disable or uninstall this module is not recommended.

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_Cms module. For more information about the Magento extension mechanism, see [Magento plugins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Cms module.

### Events

The module dispatches the following events:

- `adminhtml_cmspage_on_delete` event in the `\Magento\Cms\Controller\Adminhtml\Page\Delete::execute` method. Parameters:
    - `title` page title (`string` type).
    - `status` - `success` string value.

- `adminhtml_cmspage_on_delete` event in the `\Magento\Cms\Controller\Adminhtml\Page\Delete::execute` method. Parameters:
    - `title` page title (`string` type).
    - `status` - `fail` string value.

- `cms_page_prepare_save` event in the `\Magento\Cms\Controller\Adminhtml\Page\Save::execute` method. Parameters:
    - `page` - page object(`\Magento\Cms\Model\Page` class).
    - `request` - request data instance of `\Magento\Framework\App\RequestInterface`.

- `cms_controller_router_match_before` event in the `\Magento\Cms\Controller\Router::match` method. Parameters:
    - `router` is a `$this` object(`\Magento\Cms\Controller\Router` class).
    - `condition` - condition data object(`\Magento\Framework\DataObject` class).

- `cms_page_render` event in the `\Magento\Cms\Helper\Page::prepareResultPage` method. Parameters:
    - `page` - page object(`\Magento\Cms\Model\Page` class).
    - `controller_action` - controller action object instance of `\Magento\Framework\App\ActionInterface`.
    - `request` - request data instance of `\Magento\Framework\App\RequestInterface`.

- `cms_wysiwyg_images_static_urls_allowed` event in the `` method. Parameters:
    - `result` - check result(`object` type).
    - `store_id` - store ID(`int` type).

### Layouts

The module introduces layout handles in the `view/adminhtml/layout` and `view/frontend/layout` directory.

For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

## UI components

You can extend cms blocks and pages UI components located in the `view/adminhtml/ui_component` directory.

For information about a UI component in Magento 2, see [Overview of UI components](https://devdocs.magento.com/guides/v2.4/ui_comp_guide/bk-ui_comps.html).

### Wysiwyg

The Wysiwyg UI component is a customizable and configurable TinyMCE4 editor.

The default implementation has the following customizations:

* Magento Media Library support

### Public APIs

`\Magento\Cms\Api\BlockRepositoryInterface`:

- save or update block.
- get block by ID.
- get blocks list by search criteria.
- remove the block by the object.
- remove the block by ID.

`\Magento\Cms\Api\GetBlockByIdentifierInterface`:

- load block data by given block identifier.

`\Magento\Cms\Api\GetPageByIdentifierInterface`:

- load page data by given page identifier.

`\Magento\Cms\Api\GetUtilityPageIdentifiersInterface`:

- get list page identifiers.

`\Magento\Cms\Api\PageRepositoryInterface`:
- save or update the page.
- get page by ID.
- get pages list by search criteria.
- remove the page by the object.
- remove page by ID.

For information about a public API in Magento 2, see [Public interfaces & APIs](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/api-concepts.html).

## Additional information

For information about significant changes in patch releases, see [2.4.x Release information](http://devdocs.magento.com/guides/v2.4/release-notes/bk-release-notes.html).

More information on this topic can be found in the articles:
- [Blocks Content Element](https://docs.magento.com/user-guide/cms/blocks.html)
- [Page Content Element](https://docs.magento.com/user-guide/cms/pages.html)

### Widgets
- CMS Page Link - link to a CMS page.
- CMS Static Block - contents of a static block.
