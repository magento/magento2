# Magento_Elasticsearch8 module

This module allows using the Elasticsearch engine 8.x version for the product searching capabilities.

The module implements Magento_Search library interfaces.

## Structure

`SearchAdapter/` - the directory that contains solutions for adapting Elasticsearch query searching.

For information about a typical file structure of a module, see [Module file structure](https://developer.adobe.com/commerce/php/development/build/component-file-structure/#module-file-structure).

## Additional information

By default, `indices.id_field_data` is disallowed in Elasticsearch8.
To enable it, add the following configuration to `elasticsearch.yml`:

```yaml
indices:
  id_field_data:
    enabled: true
```

You can get more information about Elasticsearch at the following articles:

- [Configuring Catalog Search](https://experienceleague.adobe.com/en/docs/commerce-admin/catalog/catalog/search/search-configuration).
- [Installation Guide/Elasticsearch](https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/prerequisites/search-engine/overview).
- [Configure and maintain Elasticsearch](https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/search/overview-search).
- [Set up Elasticsearch service](https://experienceleague.adobe.com/en/docs/commerce-on-cloud/user-guide/configure/service/elasticsearch).
