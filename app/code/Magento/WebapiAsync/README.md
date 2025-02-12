# Magento_WebapiAsync module

Magento_WebapiAsync module extends Webapi extension and provide functional to process asynchronous requests.

Magento_WebapiAsync module handles asynchronous requests, schedule, publish and consume bulk operations from a queue.

## Installation details

Before installing this module, note that the Magento_WebapiAsync is dependent on the following modules:

- Magento_AsynchronousOperations
- Magento_Customer
- Magento_User
- Magento_Webapi

For information about enabling or disabling a module, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Structure

`Code/` - the directory that contains Remote service reader configuration files.

For information about a typical file structure of a module, see [Module file structure](https://developer.adobe.com/commerce/php/development/build/component-file-structure/#module-file-structure).

## Extensibility

Extension developers can interact with the Magento_WebapiAsync module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_WebapiAsync module.
