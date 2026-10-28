# Magento_MediaGalleryUi module

This module provides the media gallery user interface (UI) implementation.

## Installation

Before installing this module, note that the Magento_MediaGalleryUi is dependent on the Magento_Cms module.

For information about a module installation, see [Enable or disable modules](https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/tutorials/manage-modules).

## Extensibility

Extension developers can interact with the Magento_MediaGalleryUi module. For more information about the extension mechanism, see [Plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_MediaGalleryUi module.

### Layouts

This module introduces the following layouts in the `view/adminhtml/layout` directory:

- `media_gallery_index_index`
- `media_gallery_media_index`

For more information about a layout, see the [Layout documentation](https://developer.adobe.com/commerce/frontend-core/guide/layouts/).

### UI components

The configuration files located in the directory `view/adminhtml/ui_component`.

You can extend media gallery listing updates using the following configuration files:

- `media_gallery_listing`
- `standalone_media_gallery_listing`

This module extends ui components:

- `cms_block_listing`
- `cms_page_listing`
- `product_listing`

For information about a UI component, see [Overview of UI components](https://developer.adobe.com/commerce/frontend-core/ui-components/).

## Additional information

For information about significant changes in patch releases, see [Release information](https://experienceleague.adobe.com/en/docs/commerce-operations/release/notes/overview).

[Learn more about New Media Gallery](https://experienceleague.adobe.com/en/docs/commerce-admin/content-design/wysiwyg/gallery/media-gallery).
