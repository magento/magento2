#Magento_Elasticsearch8 module

Magento_Elasticsearch8 module allows using ElasticSearch engine 8.x version for the product searching capabilities.

The module implements Magento_Search library interfaces.

## Installation details

The Magento_Elasticsearch8 module is one of the base Magento 2 modules. Disabling or uninstalling this module is not recommended.

For information about a module installation in Magento 2, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Structure

`SearchAdapter/` - the directory that contains solutions for adapting ElasticSearch query searching.

For information about a typical file structure of a module in Magento 2, see [Module file structure](https://developer.adobe.com/commerce/php/development/build/component-file-structure/).

## Additional information

By default`indices.id_field_data`  is disallowed in Elasticsearch8 hence it needs to enabled it from `elasticsearch.yml`
by adding the following configuration
`indices:
id_field_data:
enabled: true`

More information about ElasticSearch are at articles:

- [Configuring Catalog Search](https://experienceleague.adobe.com/docs/commerce-admin/catalog/catalog/search/search-configuration.html).
- [Installation Guide/Elasticsearch](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/prerequisites/search-engine/overview.html).
- [Configure and maintain Elasticsearch](https://experienceleague.adobe.com/docs/commerce-operations/configuration-guide/search/overview-search.html).
- Magento Commerce Cloud - [set up Elasticsearch service](https://experienceleague.adobe.com/docs/commerce-cloud-service/user-guide/configure/service/elasticsearch.html).
