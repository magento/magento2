# Magento_AdvancedSearch module

This module introduces advanced search functionality and provides interfaces that allow third-party search engines to implement this functionality.

## Installation details

Before disabling or uninstalling this module, note that the following modules depends on this module:

- Magento_Elasticsearch
- Magento_Elasticsearch8

For information about module installation, see [Enable or disable modules](https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/tutorials/manage-modules).

## Extensibility

Extension developers can interact with this module. For more information about the extension mechanism, see [Plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of this module.

### Events

This module observes the following event:

- `catalogsearch_query_save_after` in the `Magento\AdvancedSearch\Model\Recommendations\SaveSearchQueryRelationsObserver` file.

For information about an event, see [Events and observers](https://developer.adobe.com/commerce/php/development/components/events-and-observers/#events).

### Layouts

The module interacts with the following layout handles in the `view/adminhtml/layout` directory:

- `catalog_search_block`
- `catalog_search_edit`
- `catalog_search_relatedgrid`

The module interacts with the following layout handles in the `view/frontend/layout` directory:

- `catalogsearch_result_index`

For more information about layouts, see the [Layout documentation](https://developer.adobe.com/commerce/frontend-core/guide/layouts/).
