# Magento_MediaGallerySynchronization module

The Magento_MediaGallerySynchronization module represents implementation of synchronization between data and objects contains
media asset information.

## Installation details

For information about module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_MediaGallerySynchronization module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_MediaGallerySynchronization module.

## Additional information

### Console commands

- `bin/magento media-gallery:sync` - synchronize media storage and media assets in the database

#### Message Queue Consumer

- `media.gallery.synchronization` - run media files synchronization

[Learn how to manage Message Queues](https://devdocs.magento.com/guides/v2.4/config-guide/mq/manage-message-queues.html).

For information about significant changes in patch releases, see [2.4.x Release information](https://devdocs.magento.com/guides/v2.4/release-notes/bk-release-notes.html).
