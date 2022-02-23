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

1. Add new layout handle to page if our module is enabled,
2. Update header logo. Using similar to enterprise change for logo? Maybe but just for the login page,

```xml
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    <body>
        <referenceBlock name="logo">
            <arguments>
                <argument name="edition" xsi:type="string">Enterprise</argument>
            </arguments>
        </referenceBlock>
    </body>
</page>
```

3. Include css in customer layout handle, - Done
4. Change/remove blocks when needed based on the handle, - Done
5. Include only specturm elements we need:
   1. @spectrum-css/inlinealert@4.0.2
   2. @spectrum-css/button@6.0.2
   3. @spectrum-css/typography@4.0.10
   4. @spectrum-css/card@4.0.11
   5. @spectrum-css/dialog@6.0.1
6. Build and include a minified css file https://github.com/adobe/spectrum-css#optimizing-spectrum-css
7. Check out https://spectrum.adobe.com/page/cards/ & https://opensource.adobe.com/spectrum-css/dialog.html
8. Remove "page-wrapper" class on root,
9. Add html with correct format
   1. Background full width and change image based on viewer size,
   2. Text left, - Done
   3. Notification right, - Done
   4. copyright under, - Done
   5. Error Message design,
