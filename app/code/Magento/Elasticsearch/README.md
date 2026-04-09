# Magento_Elasticsearch module

This module allows using the Elasticsearch engine for the product searching capabilities.
It provides logic used by other modules implementing newer versions of Elasticsearch.
This module by itself only adds support for Elasticsearch v7 and v8.

The module implements Magento_Search library interfaces.

## Installation details

This module is one of the base modules. You cannot disable or uninstall this module.

For information about a module installation, see [Enable or disable modules](https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/tutorials/manage-modules).

## Structure

`ElasticAdapter/` - the directory that contains the core files for providing support to Elasticsearch 7.x and 8.x
version.

`SearchAdapter/` - the directory that contains solutions for adapting Elasticsearch query searching.

For information about a typical file structure of a module, see [Module file structure](https://developer.adobe.com/commerce/php/development/build/component-file-structure/#module-file-structure).

## Additional information

For information about significant changes in patch releases, see [Release information](https://experienceleague.adobe.com/en/docs/commerce-operations/release/notes/overview).

You can get more information about Elasticsearch at the following articles:

- [Configuring Catalog Search](https://experienceleague.adobe.com/en/docs/commerce-admin/catalog/catalog/search/search-configuration).
- [Installation Guide/Elasticsearch](https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/prerequisites/search-engine/overview).
- [Configure and maintain Elasticsearch](https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/search/overview-search).
- [Set up Elasticsearch service](https://experienceleague.adobe.com/en/docs/commerce-on-cloud/user-guide/configure/service/elasticsearch).
