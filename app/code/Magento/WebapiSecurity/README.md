# Magento_WebapiSecurity module

The Magento_WebapiSecurity module enables access management of some Web API resources.

If checkbox enabled in backend through: `Stores -> Configuration -> Services -> Magento Web API -> Web Api Security` then the security of all the services outlined in `app/code/Magento/WebapiSecurity/etc/di.xml` would be loosened. You may modify this list to customize which services should follow this behavior.

By loosening the security, these services would allow access anonymously (by anyone).

## Installation details

Before installing this module, note that the Magento_WebapiSecurity is dependent on the following modules:

- `Magento_Webapi`

Refer to [how to enable or disable modules in Magento 2](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).
