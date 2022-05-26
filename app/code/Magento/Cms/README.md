# CMS Module

The CMS module provides the create, edit, and manage functionality on pages for different content types.

### Wysiwyg

The Wysiwyg UI component is a customizable and configurable TinyMCE editor.

The default implementation has the following customizations:

* Magento Media Library support

### Layouts

The module interacts with the following layout handles:

`view/base/layout` directory:
The module interacts with the following layout handles:

`view/adminhtml/layout` directory:
 - `cms_block_edit.xml`
 - `cms_block_index.xml`
 - `cms_block_new.xml`
 - `cms_page_edit.xml`
 - `cms_page_index.xml`
 - `cms_page_new.xml`
 - `cms_wysiwyg_images_contents.xml`
 - `cms_wysiwyg_images_index.xml`

The module interacts with the following layout handles in the `view/frontend/layout` directory:
 - `cms_index_defaultindex.xml`
 - `cms_index_defaultnoroute.xml`
 - `cms_index_index.xml`
 - `cms_index_nocookies.xml`
 - `cms_noroute_index.xml`
 - `cms_page_view.xml`
 - `default.xml`
 - `print.xml`

### UI components
This module extends following ui components located in the `view/base/ui_component` directory:
This module extends following ui components located in the `view/adminhtml/ui_component` directory:
 - `cms_block_form.xml`
 - `cms_block_listing.xml`
 - `cms_page_form.xml`
 - `cms_page_listing.xml`
