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
        'Magento_Customer/js/model/customer'
    ],
    function( url, authentication, customer) {
        return function (options, root) {
            authentication.setFormKey(options.formKey);
            url.setBaseUrl(options.baseUrl);
            customer.setIsLoggedIn(options.isLoggedIn);
            return first.render(root);
        };
    }
);
