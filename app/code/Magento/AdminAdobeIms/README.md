# Magento_Admin_Adobe_Ims module

The Magento_Admin_Adobe_Ims module contains integration with Adobe IMS for backend authentication.

For information about module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

# AdminAdobeIMS Callback

For the AdobeIMS Login we provide a redirect_uri on the request. After a successful Login in AdobeIMS, we get redirected to provided redirect_uri.

In the ImsCallback Controller we get the access_token and then the user profile.
We then check if the assigned organization is valid and if the user does exist in the magento database, before we complete the user login in Magento.

If there went something wrong during the authorization, the user gets redirected to the admin login page and an error message is shown.

# Organization ID Validation

During the authorization we check if the configured `Organization ID` provided on the enable CLI command is assigned to the user.

In the profile response from Adobe IMS must be a `roles` array. There we have all assigned organizations to the user.

We compare if the configured organization ID is existend in this array and also the structure of the organization ID is valid.

# CLI command usage:

`bin/magento admin:adobe-ims info`

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
