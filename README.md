# [Magento 2 Login As Customer](https://magefan.com/login-as-customer-magento-2-extension) by Magefan

[![Total Downloads](https://poser.pugx.org/magefan/module-login-as-customer/downloads)](https://packagist.org/packages/magefan/module-login-as-customer)
[![Latest Stable Version](https://poser.pugx.org/magefan/module-login-as-customer/v/stable)](https://packagist.org/packages/magefan/module-login-as-customer)

Allows admin to login as a customer (enter to customer account).

## Requirements
  * Magento Community Edition 2.0.x-2.2.x or Magento Enterprise Edition 2.0.x-2.2.x

## Installation Method 1 - Installing via composer
  * Run command: `composer require magefan/module-login-as-customer`

## Installation Method 2 - Installing using archive
  * Download [ZIP Archive](https://github.com/magefan/module-login-as-customer/archive/master.zip)
  * Extract files
  * In your Magento 2 root directory create folder app/code/Magefan/LoginAsCustomer
  * Copy files and folders from archive to that folder
  
## Enable module:
```
php bin/magento module:enable Magefan_LoginAsCustomer
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```

## Support
If you have any issues, please [contact us](mailto:support@magefan.com)
then if you still need help, open a bug report in GitHub's
[issue tracker](https://github.com/magefan/module-login-as-customer/issues).

Please do not use Magento Marketplace Reviews or (especially) the Q&A for support.
There isn't a way for us to reply to reviews and the Q&A moderation is very slow.

## Need More Features?
Please contact us to get a quote
https://magefan.com/contact

## License
The code is licensed under [Open Software License ("OSL") v. 3.0](http://opensource.org/licenses/osl-3.0.php).

## Other Magefan Extensions That Can Be Installed Via Composer
  * [Magento 2 Auto Currency Switcher Extension](https://magefan.com/magento-2-currency-switcher-auto-currency-by-country)
  * [Magento 2 Blog Extension](https://magefan.com/magento2-blog-extension)
  * [Magento 2 Conflict Detector Extension](https://magefan.com/magento2-conflict-detector)
  * [Magento 2 Lazy Load Extension](https://github.com/magefan/module-lazyload)
  * [Magento 2 Rocket JavaScript Extension](https://github.com/magefan/module-rocketjavascript)
  * [Magento 2 CLI Extension](https://github.com/magefan/module-cli)
