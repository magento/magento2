# Magento_MediaGalleryUi module

The Magento_MediaGalleryUi module is responsible for the media gallery user interface (UI) implementation.

## Installation

Before installing this module, note that the Magento_MediaGalleryUi is dependent on the Magento_Cms module.

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_MediaGalleryUi module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_MediaGalleryUi module.

### Layouts

This module introduces the following layouts in the `view/adminhtml/layout` directory:
- `media_gallery_index_index`
- `media_gallery_media_index`

For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

### UI components

The configuration files located in the directory `view/adminhtml/ui_component`.

You can extend media gallery listing updates using the following configuration files:

- `media_gallery_listing`
- `standalone_media_gallery_listing`

This module extends ui components:
- `cms_block_listing`
- `cms_page_listing`
- `product_listing`

For information about a UI component in Magento 2, see [Overview of UI components](http://devdocs.magento.com/guides/v2.4/ui_comp_guide/bk-ui_comps.html).

## Additional information

For information about significant changes in patch releases, see [2.4.x Release information](https://devdocs.magento.com/guides/v2.4/release-notes/bk-release-notes.html).

[Learn more about New Media Gallery](https://docs.magento.com/user-guide/cms/media-gallery.html).
