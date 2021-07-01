# Magento_CatalogSearch module


The Magento_CatalogSearch module is an extension of Magento_Catalog module that allows to use search engine for product searching capabilities.

The Magento_CatalogSearch module implements Magento_Search library interfaces.

## Installation details

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_CatalogSearch module. For more information about the Magento extension mechanism, see [Magento plugins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_CatalogSearch module.

### Events

The module dispatches the following events:

- `catalogsearch_searchable_attributes_load_after` event in the `\Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider::getSearchableAttributes` method. Parameters:
    - `engine` - catalog search engine  (implemented `\Magento\CatalogSearch\Model\ResourceModel\EngineInterface` interface).
    - `attributes` - list of attribute object(`\Magento\Eav\Model\Entity\Attribute[]` type).
- `catalogsearch_reset_search_result` event in the `\Magento\CatalogSearch\Model\ResourceModel\Fulltext::resetSearchResults` method.
- `catalogsearch_reset_search_result` event in the `\Magento\CatalogSearch\Model\ResourceModel\Fulltext::resetSearchResultsByStore` method. Parameters:
    - `store_id` - store id (`int` type).

### Layouts

The module introduces layout handles in the `view/frontend/layout` directory.

For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

### UI components

You can extend a product updates using the configuration files located in the `view/adminhtml/ui_component` directory.

## Additional information

From Magento 2.4 `Magento_CatalogSearch` module requires [Elasticsearch](https://www.elastic.co/) to be the catalog search engine. Refer to the [Configure and maintain Elasticsearch](https://devdocs.magento.com/guides/v2.4/config-guide/elasticsearch/es-overview.html) about installing Elasticsearch and initial configuration.

You can get more information at the articles:
- [2.4.x Release information.](https://devdocs.magento.com/guides/v2.4/release-notes/bk-release-notes.html)
- [Configuring Catalog Search](https://docs.magento.com/user-guide/catalog/search-configuration.html)
- [Catalog search user guide](https://docs.magento.com/user-guide/catalog/search.html)
