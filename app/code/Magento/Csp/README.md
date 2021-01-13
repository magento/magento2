# Magento_Csp module

Magento_Csp implements Content Security Policies for Magento. Allows CSP configuration for Merchants,
provides a way for extension and theme developers to configure CSP headers for their extensions.

## Installation details

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_Contact module. For more information about the Magento extension mechanism, see [Magento plugins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Cms module.

## Additional information

### Configuration
For more information about Magento CSP, see [Content Security Policies](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/security/content-security-policies.html#configure-csps-for-your-custom-codeextensiontheme)

#### Select CSP mode
CSP can work in two modes:

 - `report-only` mode - In this mode, Magento reports policy violations but does not interfere. This mode is useful for debugging. By default, CSP violations are written to the browser console, but they can be configured to be reported to an endpoint as an HTTP request to collect logs. There are a number of services that will collect, store, and sort your store’s CSP violations reports for you.
 - `restrict` mode - In this mode, Magento acts on any policy violations.

You can set the CSP mode in a custom module by editing the module’s etc/config.xml file. 
To set the mode to restrict, change the value of the default/csp/mode/admin/report_only and/or the default/csp/mode/storefront/report_only element to 0. 
To enable report-only mode, set the values to 1.

#### Types of CSP policies
[List of CSP policy types](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/security/content-security-policies.html#configure-csps-for-your-custom-codeextensiontheme)

#### Add domains to whitelist
1. Create `csp_whitelist.xml` in `<module_dir>/etc`.
2. Add domains for a policy (like `script-src`, `style-src`, `font-src` and others).
For more information, see [Add a domain to whitelist](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/security/content-security-policies.html#add-a-domain-to-the-whitelist)

#### Whitelist an inline script or style
Stores that have unsafe-inline disabled for style-src and script-src (default for Magento 2.4) inline scripts and styles must be whitelisted.
You must use Magento\Framework\View\Helper\SecureHtmlRenderer, which is available as a $secureRenderer variable in the .phtml templates to achieve this.

For more information, see [Whitelist an inline script or style](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/security/content-security-policies.html#whitelist-an-inline-script-or-style)

#### Page specific Content-Security-Policies
Magento can send unique policies for a specific page. To do so, implement Magento\Csp\Api\CspAwareActionInterface in a controller responsible for the page and define the `modifyCsp` method

For more information, see [Page specific Content-Security-Policies](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/security/content-security-policies.html#report-uri-configuration)
