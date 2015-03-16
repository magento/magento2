/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'mage/url',
        './view/authentication',
        'Magento_Customer/js/model/customer',
        'mage/layout'
    ],
    function(url, authentication, customer, layout) {
        return function (options, hostElement) {
            authentication.setFormKey(options.formKey);
            url.setBaseUrl(options.baseUrl);
            customer.setIsLoggedIn(options.isLoggedIn);
            var root = layout.build(options.layout);
            return root.render(hostElement);
        };
    }
);
