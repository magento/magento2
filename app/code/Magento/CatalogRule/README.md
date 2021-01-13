# Magento_CatalogRule module

Magento_CatalogRule module is responsible for one of the types of price rules in Magento 2.

Magento_CatalogRule module applied rules to products before they are added to the cart.

## Installation details

The Magento_CatalogRule module cannot be removed, because have dependencies with the Magento_CatalogRule module.

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Structure

`Pricing/` - the directory that contains solutions for calculation of the product's price.

For information about a typical file structure of a module in Magento 2, see [Module file structure](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/build/module-file-structure.html#module-file-structure).

## Extensibility

Extension developers can interact with the Magento_CatalogRule module. For more information about the Magento extension mechanism, see [Magento plugins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_CatalogRule module.

### Events

The module dispatches the following events:

- `catalogrule_dirty_notice` event in the `\Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog\Index::execute` method. Parameters:
    - `dirty_rules` - dirty rules(`\Magento\CatalogRule\Model\Flag` class).
    - `message` - rule notice message(`string` type).
- `adminhtml_controller_catalogrule_prepare_save` event in the `\Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog\Save::execute` method. Parameters:
    - `request` - object with the `\Magento\Framework\App\RequestInterface` interface.
- `clean_cache_by_tags` event in the `\Magento\CatalogRule\Model\Indexer\AbstractIndexer::executeFull` method. Parameters:
    - `object` is a `$this` object(`\Magento\CatalogRule\Model\Indexer\AbstractIndexer` class).

### Layouts

The module introduces layout handles in the `view/adminhtml/layout` directory.

For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

### UI components

You can extend a catalog rule form using the configuration files located in the `view/adminhtml/ui_component` directory.

For information about a UI component in Magento 2, see [Overview of UI components](https://devdocs.magento.com/guides/v2.4/ui_comp_guide/bk-ui_comps.html).

### Public APIs

`\Magento\CatalogRule\Api\CatalogRuleRepositoryInterface`:
- create new catalog rule
- get catalog rule by ID
- remove catalog rule by ID or an object(implemented `\Magento\CatalogRule\Api\Data\RuleInterface` interface)

## Additional information

You can get more information about [Catalog Price Rules.](https://docs.magento.com/user-guide/marketing/price-rules-catalog.html)

For information about significant changes in patch releases, see [2.4.x Release information.](https://devdocs.magento.com/guides/v2.4/release-notes/bk-release-notes.html)

### cron options

cron group configuration can be set in `etc/crontab.xml`.

- `catalogrule_apply_all` - daily update catalog price rule by cron.

[Learn how to configure and run cron in Magento.](https://devdocs.magento.com/guides/v2.4/config-guide/cli/config-cli-subcommands-cron.html)
