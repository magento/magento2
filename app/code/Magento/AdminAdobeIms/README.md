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

# Admin Login design

The admin login design changes when the AdminAdobeIms module is enabled and configured correctly via the CLI command.
We have added the customer layout handle `adobe_ims_login` to deal with all the design changes.
This handle is added via `\Magento\AdminAdobeIms\Plugin\AddAdobeImsLayoutHandlePlugin::afterAddDefaultHandle`.

The layout file `view/adminhtml/layout/adobe_ims_login.xml` adds:
* The bundled [Adobe Spectrum CSS](https://opensource.adobe.com/spectrum-css/).
* New classes to current Magento html items,
* Our new "Login with Adobe ID" button template,
* A custom error message wrapper,

We have included the minified css and the used svgs from Spectrum CSS with our module, but you can also use npm to install the latest versions.
To rebuild the minified css run the command `./node_modules/.bin/postcss -o dist/index.min.css index.css` after npm install from inside the web directory.
