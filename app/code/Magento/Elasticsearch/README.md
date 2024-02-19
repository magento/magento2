# Magento_Elasticsearch module

Magento_Elasticsearch module allows using the Elasticsearch engine for the product searching capabilities. This module
provides logic used by other modules implementing newer versions of Elasticsearch, this module by itself only adds
support for Elasticsearch v7 and v8.

The module implements Magento_Search library interfaces.

## Installation details

The Magento_Elasticsearch module is one of the base Magento 2 modules. You cannot disable or uninstall this module.

For information about a module installation in Magento 2, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Structure

`ElasticAdapter/` - the directory that contains the core files for providing support to ElasticSearch 7.x and 8.x
version.

`SearchAdapter/` - the directory that contains solutions for adapting ElasticSearch query searching.

For information about a typical file structure of a module in Magento 2, see [Module file structure](https://developer.adobe.com/commerce/php/development/build/component-file-structure/#module-file-structure).

## Additional information

For information about significant changes in patch releases, see [2.4.x Release information](https://experienceleague.adobe.com/docs/commerce-operations/release/notes/overview.html).

More information about ElasticSearch are at articles:

- [Configuring Catalog Search](https://experienceleague.adobe.com/docs/commerce-admin/catalog/catalog/search/search-configuration.html).
- [Installation Guide/Elasticsearch](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/prerequisites/search-engine/overview.html).
- [Configure and maintain Elasticsearch](https://experienceleague.adobe.com/docs/commerce-operations/configuration-guide/search/overview-search.html).
- Magento Commerce Cloud - [set up Elasticsearch service](https://experienceleague.adobe.com/docs/commerce-cloud-service/user-guide/configure/service/elasticsearch.html).
