# Magento_MediaGallerySynchronization module

This module provides synchronization between data and objects that contain media asset information.

## Installation details

For information about module installation, see [Enable or disable modules](https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/tutorials/manage-modules).

## Extensibility

Extension developers can interact with the Magento_MediaGallerySynchronization module. For more information about the extension mechanism, see [Plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_MediaGallerySynchronization module.

## Additional information

### Console commands

- `bin/magento media-gallery:sync` - synchronize media storage and media assets in the database.

#### Message Queue Consumer

- `media.gallery.synchronization` - run media files synchronization.

[Learn how to manage Message Queues](https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/message-queues/manage-message-queues).

For information about significant changes in patch releases, see [Release information](https://experienceleague.adobe.com/en/docs/commerce-operations/release/notes/overview).
