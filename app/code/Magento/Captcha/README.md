# Magento_Captcha module

The **Magento_Captcha** module allows applying Turing test in the process of user authentication or similar tasks.

The **Magento_Captcha** module extends **Magento_Customer** and **Magento_Checkout** modules to validating forms by Turing test there.

## Installation details

Before disabling or uninstalling this module, please consider the following dependencies:

- Magento_Checkout
- Magento_PaypalCaptcha
- Magento_SalesRule
- Magento_SendFriend
- Magento_Wishlist

Please find here [how to enable or disable modules in Magento 2](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_Captcha module. For more information about the Magento 2 extension mechanism, see [Magento plugins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento 2 dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Captcha module.

### Layouts

This module introduces the following layouts and layout handles in the directories:
- `view/adminhtml/layout`:
    - `adminhtml_auth_forgotpassword`
    - `adminhtml_auth_login`
- `view/frantend/layout`:
    - `checkout_index_index`
    - `contact_index_index`
    - `customer_account_create`
    - `customer_account_edit`
    - `customer_account_forgotpassword`
    - `customer_account_login`
    - `default`

For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

## Additional information

For more information about a captcha in Magento 2, see the  [Captcha documentation](https://docs.magento.com/user-guide/stores/security-captcha.html).

### Cron options

Cron group configuration can be set in `etc/crontab.xml`.
-   `captcha_delete_old_attempts` – each period of time remove unnecessary logged attempts.
-   `captcha_delete_expired_images` – each period of time remove expired captcha Images.

[Learn how to configure and run cron in Magento.](https://devdocs.magento.com/guides/v2.4/config-guide/cli/config-cli-subcommands-cron.html)
