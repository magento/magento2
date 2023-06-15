# Magento_MediaGallerySynchronization module

The Magento_MediaGallerySynchronization module represents implementation of synchronization between data and objects contains
media asset information.

## Installation details

For information about module installation in Magento 2, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Extensibility

Extension developers can interact with the Magento_MediaGallerySynchronization module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_MediaGallerySynchronization module.

## Additional information

### Console commands

- `bin/magento media-gallery:sync` - synchronize media storage and media assets in the database

#### Message Queue Consumer

- `media.gallery.synchronization` - run media files synchronization

[Learn how to manage Message Queues](https://experienceleague.adobe.com/docs/commerce-operations/configuration-guide/message-queues/manage-message-queues.html).

For information about significant changes in patch releases, see [2.4.x Release information](https://experienceleague.adobe.com/docs/commerce-operations/release/notes/overview.html).
