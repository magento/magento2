# Magento_Deploy module

The Magento_Deploy module provides a holding collection of services and command-line tools to help with Magento application deployment.

## Installation details

The Magento_Deploy module is installed automatically (using the native Magento install mechanism) without any additional actions.
It is one of the base Magento 2 modules. Disabling or uninstalling this module is not recommended.

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Structure

- `App/` - the directory that contains solutions for extending framework core functionality.
- `Collector/` - the directory that contains class, which resolves deployment files collector.
- `Config/` - the directory that contains class, which resolves static files bundling configuration.
- `Package/` - the directory that contains solutions for packages files.
- `Process/` - the directory that contains solutions queue of deploy packages in parallel forks.
- `Service/` - the directory that contains single logic solutions for each service class.
- `Source/` - the directory that contains solutions for getting files from different sources.
- `Strategy/` -the directory that contains solutions for deployment of static files.

For information about a typical file structure of a module in Magento 2, see [Module file structure](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/build/module-file-structure.html#module-file-structure).

## Extensibility

Extension developers can interact with the Magento_ConfigurableProductStaging module. For more information about the Magento extension mechanism, see [Magento plugins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Deploy module.

## Additional information

For information about significant changes in patch releases, see [2.4.x Release information](https://devdocs.magento.com/guides/v2.4/release-notes/bk-release-notes.html).

You can get more information about the deployment in Magento at the articles:
- [Deploy static view files](https://devdocs.magento.com/guides/v2.4/config-guide/cli/config-cli-subcommands-static-view.html)
- [Static files deployment strategies](https://devdocs.magento.com/guides/v2.4/config-guide/cli/config-cli-subcommands-static-deploy-strategies.html)
- [Set the Magento mode](https://devdocs.magento.com/guides/v2.4/config-guide/cli/config-cli-subcommands-mode.html)
