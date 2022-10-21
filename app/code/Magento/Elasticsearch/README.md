#Magento_Elasticsearch module

Magento_Elasticsearch module allows using the Elasticsearch engine for the product searching capabilities. This module 
provides logic used by other modules implementing newer versions of Elasticsearch, this module by itself only adds 
support for Elasticsearch v5.

The module implements Magento_Search library interfaces.

## Installation details

The Magento_Elasticsearch module is one of the base Magento 2 modules. You cannot disable or uninstall this module.

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Structure

`Elasticsearch5/` - the directory that contains solutions for providing ElasticSearch 5.x version.

`SearchAdapter/` - the directory that contains solutions for adapting ElasticSearch query searching.

For information about a typical file structure of a module in Magento 2, see [Module file structure](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/build/module-file-structure.html#module-file-structure).

## Additional information

For information about significant changes in patch releases, see [2.4.x Release information](https://devdocs.magento.com/guides/v2.4/release-notes/bk-release-notes.html).

More information about ElasticSearch are at articles:

- [Configuring Catalog Search](https://docs.magento.com/user-guide/catalog/search-configuration.html).
- [Installation Guide/Elasticsearch](https://devdocs.magento.com/guides/v2.4/install-gde/prereq/elasticsearch.html).
- [Configure and maintain Elasticsearch](https://devdocs.magento.com/guides/v2.4/config-guide/elasticsearch/es-overview.html).
- Magento Commerce Cloud - [set up Elasticsearch service](https://devdocs.magento.com/cloud/project/services-elastic.html).
