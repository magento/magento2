# Magento_Admin_Adobe_Ims module

The Magento_Admin_Adobe_Ims module contains integration with Adobe IMS for backend authentication.

For information about module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

CLI command usage:
bin/magento admin:adobe-ims info

Example of getting data if Admin Adobe Ims module is enabled:
Client ID: 1234567890a
Organization ID: 1234567890@org
Client Secret configured

If Admin Adobe Ims module is disabled, cli command will show message "Module is disabled"

CABPI-196 Login Display
---

1. Fix MFTF tests,
2. Build and include a minified css file https://github.com/adobe/spectrum-css#optimizing-spectrum-css,
3. Document changes,
